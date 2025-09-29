<?php

namespace Jiny\Admin\Services\Sms;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Exception;

class EnhancedSmsService
{
    protected $driver;
    protected $retryAttempts = 3;
    protected $retryDelay = 5; // seconds
    
    public function __construct()
    {
        $this->initializeDriver();
    }
    
    /**
     * Initialize SMS driver
     */
    protected function initializeDriver()
    {
        $defaultDriver = config('sms.default', 'twilio');
        $driverConfig = config("sms.drivers.{$defaultDriver}", []);
        
        switch ($defaultDriver) {
            case 'twilio':
                $this->driver = new TwilioDriver($driverConfig);
                break;
            case 'vonage':
                $this->driver = new VonageDriver($driverConfig);
                break;
            case 'aws_sns':
                $this->driver = new AwsSnsDriver($driverConfig);
                break;
            case 'aligo':
                $this->driver = new AligoDriver($driverConfig);
                break;
            default:
                throw new Exception("Unknown SMS driver: {$defaultDriver}");
        }
    }
    
    /**
     * Send SMS immediately
     */
    public function send($to, $message, $options = [])
    {
        $startTime = microtime(true);
        
        // Rate limiting check
        if (!$this->checkRateLimit($to)) {
            return [
                'success' => false,
                'error' => 'Rate limit exceeded for this number'
            ];
        }
        
        // Retry logic
        $lastError = null;
        for ($attempt = 1; $attempt <= $this->retryAttempts; $attempt++) {
            try {
                $result = $this->driver->send($to, $message, $options['from'] ?? null);
                
                if ($result['success']) {
                    // Log successful send
                    $this->logSend($to, $message, $result, microtime(true) - $startTime);
                    
                    // Update statistics
                    $this->updateStatistics('sent');
                    
                    return $result;
                }
                
                $lastError = $result['error_message'] ?? 'Unknown error';
                
            } catch (Exception $e) {
                $lastError = $e->getMessage();
                Log::error('SMS send attempt failed', [
                    'attempt' => $attempt,
                    'error' => $e->getMessage()
                ]);
            }
            
            if ($attempt < $this->retryAttempts) {
                sleep($this->retryDelay * $attempt); // Exponential backoff
            }
        }
        
        // Log failed send
        $this->logSend($to, $message, [
            'success' => false,
            'error_message' => $lastError
        ], microtime(true) - $startTime);
        
        // Update statistics
        $this->updateStatistics('failed');
        
        return [
            'success' => false,
            'error' => $lastError
        ];
    }
    
    /**
     * Queue SMS for later sending
     */
    public function queue($to, $message, $options = [])
    {
        $data = [
            'to' => $to,
            'message' => $message,
            'provider' => $options['provider'] ?? config('sms.default'),
            'status' => 'pending',
            'priority' => $options['priority'] ?? 'normal',
            'scheduled_at' => $options['scheduled_at'] ?? null,
            'batch_id' => $options['batch_id'] ?? null,
            'metadata' => json_encode($options['metadata'] ?? []),
            'user_id' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now()
        ];
        
        $id = DB::table('admin_sms_queues')->insertGetId($data);
        
        Log::info('SMS queued', [
            'id' => $id,
            'to' => $to
        ]);
        
        return [
            'success' => true,
            'queue_id' => $id
        ];
    }
    
    /**
     * Queue bulk SMS
     */
    public function queueBulk(array $recipients, $message, $options = [])
    {
        $batchId = uniqid('batch_');
        $queueIds = [];
        
        DB::beginTransaction();
        try {
            foreach ($recipients as $recipient) {
                $to = is_array($recipient) ? $recipient['to'] : $recipient;
                $customMessage = is_array($recipient) && isset($recipient['message']) 
                    ? $recipient['message'] 
                    : $message;
                
                $result = $this->queue($to, $customMessage, array_merge($options, [
                    'batch_id' => $batchId
                ]));
                
                if ($result['success']) {
                    $queueIds[] = $result['queue_id'];
                }
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'batch_id' => $batchId,
                'queue_ids' => $queueIds,
                'total' => count($queueIds)
            ];
            
        } catch (Exception $e) {
            DB::rollback();
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Process SMS queue
     */
    public function processQueue($limit = 10)
    {
        $messages = DB::table('admin_sms_queues')
            ->where('status', 'pending')
            ->where(function ($query) {
                $query->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            })
            ->where('attempts', '<', DB::raw('max_attempts'))
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")
            ->orderBy('created_at')
            ->limit($limit)
            ->lockForUpdate()
            ->get();
        
        $results = [
            'processed' => 0,
            'sent' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        foreach ($messages as $message) {
            // Update status to processing
            DB::table('admin_sms_queues')
                ->where('id', $message->id)
                ->update([
                    'status' => 'processing',
                    'attempts' => $message->attempts + 1,
                    'updated_at' => now()
                ]);
            
            // Send SMS
            $result = $this->send($message->to, $message->message);
            
            if ($result['success']) {
                // Update as sent
                DB::table('admin_sms_queues')
                    ->where('id', $message->id)
                    ->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'response_data' => json_encode($result),
                        'message_id' => $result['message_id'] ?? null,
                        'cost' => $result['price'] ?? null,
                        'updated_at' => now()
                    ]);
                
                $results['sent']++;
                
            } else {
                // Check if max attempts reached
                if ($message->attempts + 1 >= $message->max_attempts) {
                    // Mark as failed
                    DB::table('admin_sms_queues')
                        ->where('id', $message->id)
                        ->update([
                            'status' => 'failed',
                            'failed_at' => now(),
                            'error_message' => $result['error'] ?? 'Unknown error',
                            'response_data' => json_encode($result),
                            'updated_at' => now()
                        ]);
                    
                    $results['failed']++;
                    $results['errors'][] = [
                        'id' => $message->id,
                        'to' => $message->to,
                        'error' => $result['error'] ?? 'Unknown error'
                    ];
                    
                } else {
                    // Reset to pending for retry
                    DB::table('admin_sms_queues')
                        ->where('id', $message->id)
                        ->update([
                            'status' => 'pending',
                            'error_message' => $result['error'] ?? 'Unknown error',
                            'updated_at' => now()
                        ]);
                }
            }
            
            $results['processed']++;
        }
        
        return $results;
    }
    
    /**
     * Cancel queued messages
     */
    public function cancelQueue($batchId = null, $queueId = null)
    {
        $query = DB::table('admin_sms_queues')
            ->where('status', 'pending');
        
        if ($batchId) {
            $query->where('batch_id', $batchId);
        }
        
        if ($queueId) {
            $query->where('id', $queueId);
        }
        
        $cancelled = $query->update([
            'status' => 'cancelled',
            'updated_at' => now()
        ]);
        
        return [
            'success' => true,
            'cancelled' => $cancelled
        ];
    }
    
    /**
     * Process webhook
     */
    public function processWebhook($provider, $data)
    {
        // Log webhook
        $webhookId = DB::table('admin_sms_webhooks')->insertGetId([
            'provider' => $provider,
            'message_id' => $data['message_id'] ?? null,
            'event_type' => $data['event_type'] ?? 'unknown',
            'payload' => json_encode($data),
            'ip_address' => request()->ip(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        try {
            // Process based on provider
            switch ($provider) {
                case 'twilio':
                    $this->processTwilioWebhook($data);
                    break;
                case 'vonage':
                    $this->processVonageWebhook($data);
                    break;
                case 'aws_sns':
                    $this->processAwsSnsWebhook($data);
                    break;
                case 'aligo':
                    $this->processAligoWebhook($data);
                    break;
            }
            
            // Mark webhook as processed
            DB::table('admin_sms_webhooks')
                ->where('id', $webhookId)
                ->update([
                    'processed' => true,
                    'processed_at' => now()
                ]);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            // Log error
            DB::table('admin_sms_webhooks')
                ->where('id', $webhookId)
                ->update([
                    'error_message' => $e->getMessage()
                ]);
            
            Log::error('SMS webhook processing failed', [
                'provider' => $provider,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Check rate limiting
     */
    protected function checkRateLimit($phoneNumber)
    {
        $key = 'sms_rate_limit:' . $phoneNumber;
        $limit = config('sms.rate_limit.per_number', 10);
        $window = config('sms.rate_limit.window', 3600); // 1 hour
        
        $current = Cache::get($key, 0);
        
        if ($current >= $limit) {
            return false;
        }
        
        Cache::increment($key);
        
        if ($current == 0) {
            Cache::expire($key, $window);
        }
        
        return true;
    }
    
    /**
     * Log SMS send
     */
    protected function logSend($to, $message, $result, $duration)
    {
        try {
            DB::table('admin_sms_sends')->insert([
                'provider_id' => $this->getProviderId(),
                'phone_number' => $to,
                'message' => $message,
                'status' => $result['success'] ? 'sent' : 'failed',
                'response_data' => json_encode($result),
                'cost' => $result['price'] ?? null,
                'duration' => $duration,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (Exception $e) {
            Log::error('Failed to log SMS send', [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get provider ID
     */
    protected function getProviderId()
    {
        $providerName = $this->driver->getName();
        
        return Cache::remember('sms_provider_id:' . $providerName, 3600, function () use ($providerName) {
            $provider = DB::table('admin_sms_providers')
                ->where('driver_type', $providerName)
                ->where('is_active', true)
                ->first();
            
            return $provider ? $provider->id : null;
        });
    }
    
    /**
     * Update statistics
     */
    protected function updateStatistics($type)
    {
        $key = 'sms_stats:' . date('Y-m-d');
        $stats = Cache::get($key, [
            'sent' => 0,
            'failed' => 0,
            'queued' => 0
        ]);
        
        $stats[$type] = ($stats[$type] ?? 0) + 1;
        
        Cache::put($key, $stats, 86400); // 24 hours
    }
    
    /**
     * Get statistics
     */
    public function getStatistics($days = 7)
    {
        $stats = [
            'total_sent' => 0,
            'total_failed' => 0,
            'total_queued' => 0,
            'total_cost' => 0,
            'by_day' => [],
            'by_provider' => [],
            'by_status' => []
        ];
        
        // Get from database
        $fromDate = now()->subDays($days);
        
        // Total counts
        $stats['total_sent'] = DB::table('admin_sms_sends')
            ->where('created_at', '>=', $fromDate)
            ->where('status', 'sent')
            ->count();
        
        $stats['total_failed'] = DB::table('admin_sms_sends')
            ->where('created_at', '>=', $fromDate)
            ->where('status', 'failed')
            ->count();
        
        $stats['total_queued'] = DB::table('admin_sms_queues')
            ->where('status', 'pending')
            ->count();
        
        $stats['total_cost'] = DB::table('admin_sms_sends')
            ->where('created_at', '>=', $fromDate)
            ->sum('cost');
        
        // By day
        $stats['by_day'] = DB::table('admin_sms_sends')
            ->where('created_at', '>=', $fromDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(cost) as cost')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // By provider
        $stats['by_provider'] = DB::table('admin_sms_sends')
            ->join('admin_sms_providers', 'admin_sms_sends.provider_id', '=', 'admin_sms_providers.id')
            ->where('admin_sms_sends.created_at', '>=', $fromDate)
            ->selectRaw('admin_sms_providers.name, COUNT(*) as count, SUM(admin_sms_sends.cost) as cost')
            ->groupBy('admin_sms_providers.name')
            ->get();
        
        // By status
        $stats['by_status'] = DB::table('admin_sms_sends')
            ->where('created_at', '>=', $fromDate)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();
        
        return $stats;
    }
    
    /**
     * Process Twilio webhook
     */
    protected function processTwilioWebhook($data)
    {
        $messageId = $data['MessageSid'] ?? null;
        $status = $data['MessageStatus'] ?? null;
        
        if ($messageId && $status) {
            // Update queue status if exists
            DB::table('admin_sms_queues')
                ->where('message_id', $messageId)
                ->update([
                    'response_data' => json_encode($data),
                    'updated_at' => now()
                ]);
            
            // Update send log
            DB::table('admin_sms_sends')
                ->where('response_data->message_id', $messageId)
                ->update([
                    'status' => $this->mapTwilioStatus($status),
                    'updated_at' => now()
                ]);
        }
    }
    
    /**
     * Map Twilio status
     */
    protected function mapTwilioStatus($status)
    {
        $statusMap = [
            'delivered' => 'delivered',
            'sent' => 'sent',
            'failed' => 'failed',
            'undelivered' => 'failed',
            'queued' => 'pending',
            'sending' => 'processing'
        ];
        
        return $statusMap[$status] ?? $status;
    }
    
    /**
     * Process other webhook types (placeholder methods)
     */
    protected function processVonageWebhook($data)
    {
        // Implementation specific to Vonage webhook format
    }
    
    protected function processAwsSnsWebhook($data)
    {
        // Implementation specific to AWS SNS webhook format
    }
    
    protected function processAligoWebhook($data)
    {
        // Implementation specific to Aligo webhook format
    }
}