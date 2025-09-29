<?php

namespace Jiny\Admin\Services\Email;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class EnhancedEmailService
{
    protected $templateService;
    protected $trackingService;
    
    public function __construct()
    {
        $this->templateService = new EmailTemplateVersionService();
        $this->trackingService = new EmailTrackingService();
    }
    
    /**
     * Send email with template
     */
    public function send($to, $templateKey, $variables = [], $options = [])
    {
        try {
            // Get subscriber info
            $subscriber = $this->getOrCreateSubscriber($to);
            
            // Check if subscriber is valid
            if (!$this->isSubscriberValid($subscriber)) {
                return [
                    'success' => false,
                    'error' => 'Subscriber is unsubscribed, bounced, or complained'
                ];
            }
            
            // Get template with A/B testing support
            $template = $this->getTemplate($templateKey, $options);
            
            if (!$template) {
                return [
                    'success' => false,
                    'error' => 'Template not found'
                ];
            }
            
            // Process template
            $processed = $this->processTemplate($template, $variables, $subscriber);
            
            // Generate tracking ID
            $messageId = Str::uuid()->toString();
            
            // Add tracking pixels and links
            if ($options['track'] ?? true) {
                $processed['body'] = $this->addTracking($processed['body'], $messageId);
            }
            
            // Queue or send immediately
            if ($options['queue'] ?? false) {
                $this->queueEmail($to, $processed, $messageId, $template, $options);
            } else {
                $this->sendEmail($to, $processed, $messageId, $template, $options);
            }
            
            return [
                'success' => true,
                'message_id' => $messageId
            ];
            
        } catch (Exception $e) {
            Log::error('Email send failed', [
                'to' => $to,
                'template' => $templateKey,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Send bulk emails
     */
    public function sendBulk($recipients, $templateKey, $variables = [], $options = [])
    {
        $batchId = Str::uuid()->toString();
        $results = [
            'batch_id' => $batchId,
            'total' => count($recipients),
            'queued' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        foreach ($recipients as $recipient) {
            $to = is_array($recipient) ? $recipient['email'] : $recipient;
            $customVars = is_array($recipient) ? array_merge($variables, $recipient['variables'] ?? []) : $variables;
            
            $result = $this->send($to, $templateKey, $customVars, array_merge($options, [
                'queue' => true,
                'batch_id' => $batchId
            ]));
            
            if ($result['success']) {
                $results['queued']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'email' => $to,
                    'error' => $result['error']
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Get or create subscriber
     */
    protected function getOrCreateSubscriber($email)
    {
        $subscriber = DB::table('admin_email_subscribers')
            ->where('email', $email)
            ->first();
        
        if (!$subscriber) {
            $id = DB::table('admin_email_subscribers')->insertGetId([
                'email' => $email,
                'is_subscribed' => true,
                'subscribed_at' => now(),
                'source' => 'system',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $subscriber = DB::table('admin_email_subscribers')->find($id);
        }
        
        return $subscriber;
    }
    
    /**
     * Check if subscriber is valid
     */
    protected function isSubscriberValid($subscriber)
    {
        if (!$subscriber->is_subscribed) {
            return false;
        }
        
        if ($subscriber->is_bounced) {
            return false;
        }
        
        if ($subscriber->is_complained) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get template with A/B testing
     */
    protected function getTemplate($templateKey, $options = [])
    {
        // Check for active A/B test
        $abTest = DB::table('admin_email_ab_tests')
            ->join('admin_emailtemplates', 'admin_email_ab_tests.template_id', '=', 'admin_emailtemplates.id')
            ->where('admin_emailtemplates.key', $templateKey)
            ->where('admin_email_ab_tests.status', 'running')
            ->where('admin_email_ab_tests.start_date', '<=', now())
            ->where(function ($query) {
                $query->whereNull('admin_email_ab_tests.end_date')
                    ->orWhere('admin_email_ab_tests.end_date', '>=', now());
            })
            ->first();
        
        if ($abTest) {
            // Select variant based on distribution
            $variants = json_decode($abTest->variants, true);
            $versionId = $this->selectVariant($variants);
            
            $template = DB::table('admin_email_template_versions')
                ->where('id', $versionId)
                ->first();
            
            if ($template) {
                $template->ab_test_id = $abTest->id;
                return $template;
            }
        }
        
        // Get active template version
        return DB::table('admin_emailtemplates')
            ->join('admin_email_template_versions', function ($join) {
                $join->on('admin_emailtemplates.id', '=', 'admin_email_template_versions.template_id')
                    ->where('admin_email_template_versions.is_active', true);
            })
            ->where('admin_emailtemplates.key', $templateKey)
            ->where('admin_emailtemplates.is_active', true)
            ->select('admin_email_template_versions.*', 'admin_emailtemplates.key as template_key')
            ->first();
    }
    
    /**
     * Select A/B test variant
     */
    protected function selectVariant($variants)
    {
        $random = mt_rand(1, 100);
        $cumulative = 0;
        
        foreach ($variants as $variant) {
            $cumulative += $variant['percentage'];
            if ($random <= $cumulative) {
                return $variant['version_id'];
            }
        }
        
        // Fallback to first variant
        return $variants[0]['version_id'];
    }
    
    /**
     * Process template with variables
     */
    protected function processTemplate($template, $variables, $subscriber)
    {
        // Add subscriber variables
        $variables['subscriber_email'] = $subscriber->email;
        $variables['subscriber_name'] = $subscriber->name ?? '';
        $variables['unsubscribe_url'] = route('email.unsubscribe', ['email' => $subscriber->email]);
        
        // Process subject
        $subject = $this->replaceVariables($template->subject, $variables);
        
        // Process body
        $body = $this->replaceVariables($template->body, $variables);
        
        // Apply layout if exists
        if ($template->layout) {
            $layout = $this->getLayout($template->layout);
            if ($layout) {
                $body = str_replace('{{content}}', $body, $layout);
            }
        }
        
        return [
            'subject' => $subject,
            'body' => $body,
            'template_id' => $template->template_id ?? $template->id,
            'version_id' => $template->id,
            'ab_test_id' => $template->ab_test_id ?? null
        ];
    }
    
    /**
     * Replace variables in text
     */
    protected function replaceVariables($text, $variables)
    {
        foreach ($variables as $key => $value) {
            $text = str_replace('{{' . $key . '}}', $value, $text);
        }
        
        // Remove any remaining variables
        $text = preg_replace('/\{\{.*?\}\}/', '', $text);
        
        return $text;
    }
    
    /**
     * Get email layout
     */
    protected function getLayout($layoutName)
    {
        return Cache::remember('email_layout:' . $layoutName, 3600, function () use ($layoutName) {
            $path = resource_path("views/emails/layouts/{$layoutName}.blade.php");
            if (file_exists($path)) {
                return file_get_contents($path);
            }
            return null;
        });
    }
    
    /**
     * Add tracking to email
     */
    protected function addTracking($body, $messageId)
    {
        // Add open tracking pixel
        $pixelUrl = route('email.track.open', ['id' => $messageId]);
        $pixel = '<img src="' . $pixelUrl . '" width="1" height="1" style="display:none;" />';
        $body = str_replace('</body>', $pixel . '</body>', $body);
        
        // Track links
        $body = preg_replace_callback(
            '/<a\s+href=["\']([^"\']+)["\']/',
            function ($matches) use ($messageId) {
                $originalUrl = $matches[1];
                
                // Skip tracking for unsubscribe and system links
                if (strpos($originalUrl, 'unsubscribe') !== false || 
                    strpos($originalUrl, 'mailto:') === 0) {
                    return $matches[0];
                }
                
                $trackUrl = route('email.track.click', [
                    'id' => $messageId,
                    'url' => base64_encode($originalUrl)
                ]);
                
                return '<a href="' . $trackUrl . '"';
            },
            $body
        );
        
        return $body;
    }
    
    /**
     * Queue email for sending
     */
    protected function queueEmail($to, $processed, $messageId, $template, $options)
    {
        DB::table('admin_email_queues')->insert([
            'message_id' => $messageId,
            'to' => $to,
            'subject' => $processed['subject'],
            'body' => $processed['body'],
            'template_id' => $processed['template_id'],
            'template_version_id' => $processed['version_id'],
            'ab_test_id' => $processed['ab_test_id'],
            'batch_id' => $options['batch_id'] ?? null,
            'priority' => $options['priority'] ?? 'normal',
            'scheduled_at' => $options['scheduled_at'] ?? null,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    
    /**
     * Send email immediately
     */
    protected function sendEmail($to, $processed, $messageId, $template, $options)
    {
        // Log email
        $logId = $this->logEmail($to, $processed, $messageId);
        
        // Create tracking record
        $this->createTrackingRecord($messageId, $to, $processed, $logId);
        
        // Send via Laravel Mail
        Mail::html($processed['body'], function ($message) use ($to, $processed, $messageId, $options) {
            $message->to($to)
                ->subject($processed['subject'])
                ->getHeaders()
                ->addTextHeader('X-Message-ID', $messageId);
            
            if (isset($options['from'])) {
                $message->from($options['from']['address'], $options['from']['name'] ?? null);
            }
            
            if (isset($options['reply_to'])) {
                $message->replyTo($options['reply_to']);
            }
        });
    }
    
    /**
     * Log email send
     */
    protected function logEmail($to, $processed, $messageId)
    {
        return DB::table('admin_email_logs')->insertGetId([
            'to' => $to,
            'subject' => $processed['subject'],
            'body' => $processed['body'],
            'status' => 'sent',
            'message_id' => $messageId,
            'template_used' => $processed['template_id'],
            'sent_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    
    /**
     * Create tracking record
     */
    protected function createTrackingRecord($messageId, $to, $processed, $logId)
    {
        DB::table('admin_email_tracking')->insert([
            'message_id' => $messageId,
            'email_log_id' => $logId,
            'recipient_email' => $to,
            'template_id' => $processed['template_id'],
            'template_version_id' => $processed['version_id'],
            'ab_test_id' => $processed['ab_test_id'],
            'sent_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    
    /**
     * Track email open
     */
    public function trackOpen($messageId, $request)
    {
        DB::table('admin_email_tracking')
            ->where('message_id', $messageId)
            ->update([
                'opened_at' => DB::raw('IFNULL(opened_at, NOW())'),
                'open_count' => DB::raw('open_count + 1'),
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
                'device_type' => $this->detectDeviceType($request->userAgent()),
                'email_client' => $this->detectEmailClient($request->userAgent()),
                'updated_at' => now()
            ]);
        
        // Update A/B test metrics if applicable
        $tracking = DB::table('admin_email_tracking')
            ->where('message_id', $messageId)
            ->first();
        
        if ($tracking && $tracking->ab_test_id) {
            $this->updateABTestMetrics($tracking->ab_test_id, 'opens');
        }
    }
    
    /**
     * Track email click
     */
    public function trackClick($messageId, $url, $request)
    {
        // Get tracking record
        $tracking = DB::table('admin_email_tracking')
            ->where('message_id', $messageId)
            ->first();
        
        if ($tracking) {
            // Update click data
            $clickedLinks = json_decode($tracking->clicked_links, true) ?? [];
            $clickedLinks[] = [
                'url' => $url,
                'clicked_at' => now()->toDateTimeString(),
                'ip' => $request->ip()
            ];
            
            DB::table('admin_email_tracking')
                ->where('message_id', $messageId)
                ->update([
                    'clicked_at' => DB::raw('IFNULL(clicked_at, NOW())'),
                    'click_count' => DB::raw('click_count + 1'),
                    'clicked_links' => json_encode($clickedLinks),
                    'updated_at' => now()
                ]);
            
            // Update A/B test metrics
            if ($tracking->ab_test_id) {
                $this->updateABTestMetrics($tracking->ab_test_id, 'clicks');
            }
        }
        
        return $url;
    }
    
    /**
     * Handle unsubscribe
     */
    public function unsubscribe($email, $reason = null)
    {
        DB::table('admin_email_subscribers')
            ->where('email', $email)
            ->update([
                'is_subscribed' => false,
                'unsubscribed_at' => now(),
                'unsubscribe_reason' => $reason,
                'updated_at' => now()
            ]);
        
        // Update tracking records
        DB::table('admin_email_tracking')
            ->where('recipient_email', $email)
            ->whereNull('unsubscribed_at')
            ->update([
                'unsubscribed_at' => now(),
                'updated_at' => now()
            ]);
        
        return true;
    }
    
    /**
     * Handle bounce
     */
    public function handleBounce($messageId, $bounceType, $bounceReason)
    {
        // Update tracking
        DB::table('admin_email_tracking')
            ->where('message_id', $messageId)
            ->update([
                'bounced_at' => now(),
                'bounce_type' => $bounceType,
                'bounce_reason' => $bounceReason,
                'updated_at' => now()
            ]);
        
        // Get recipient
        $tracking = DB::table('admin_email_tracking')
            ->where('message_id', $messageId)
            ->first();
        
        if ($tracking) {
            // Update subscriber if hard bounce
            if ($bounceType === 'hard') {
                DB::table('admin_email_subscribers')
                    ->where('email', $tracking->recipient_email)
                    ->update([
                        'is_bounced' => true,
                        'bounced_at' => now(),
                        'bounce_type' => $bounceType,
                        'updated_at' => now()
                    ]);
            }
        }
    }
    
    /**
     * Update A/B test metrics
     */
    protected function updateABTestMetrics($abTestId, $metricType)
    {
        $test = DB::table('admin_email_ab_tests')->find($abTestId);
        
        if ($test) {
            $metrics = json_decode($test->metrics, true) ?? [];
            $metrics[$metricType] = ($metrics[$metricType] ?? 0) + 1;
            
            DB::table('admin_email_ab_tests')
                ->where('id', $abTestId)
                ->update([
                    'metrics' => json_encode($metrics),
                    'updated_at' => now()
                ]);
        }
    }
    
    /**
     * Detect device type
     */
    protected function detectDeviceType($userAgent)
    {
        if (preg_match('/Mobile|Android|iPhone/i', $userAgent)) {
            return 'mobile';
        } elseif (preg_match('/Tablet|iPad/i', $userAgent)) {
            return 'tablet';
        }
        return 'desktop';
    }
    
    /**
     * Detect email client
     */
    protected function detectEmailClient($userAgent)
    {
        $clients = [
            'Outlook' => '/Outlook/i',
            'Gmail' => '/Gmail/i',
            'Yahoo' => '/Yahoo/i',
            'Apple Mail' => '/Apple Mail/i',
            'Thunderbird' => '/Thunderbird/i',
        ];
        
        foreach ($clients as $client => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return $client;
            }
        }
        
        return 'Unknown';
    }
    
    /**
     * Get email statistics
     */
    public function getStatistics($days = 7)
    {
        $fromDate = now()->subDays($days);
        
        return [
            'sent' => DB::table('admin_email_tracking')
                ->where('sent_at', '>=', $fromDate)
                ->count(),
            
            'opened' => DB::table('admin_email_tracking')
                ->where('sent_at', '>=', $fromDate)
                ->whereNotNull('opened_at')
                ->count(),
            
            'clicked' => DB::table('admin_email_tracking')
                ->where('sent_at', '>=', $fromDate)
                ->whereNotNull('clicked_at')
                ->count(),
            
            'unsubscribed' => DB::table('admin_email_tracking')
                ->where('sent_at', '>=', $fromDate)
                ->whereNotNull('unsubscribed_at')
                ->count(),
            
            'bounced' => DB::table('admin_email_tracking')
                ->where('sent_at', '>=', $fromDate)
                ->whereNotNull('bounced_at')
                ->count(),
            
            'open_rate' => $this->calculateRate('opened_at', 'sent_at', $fromDate),
            'click_rate' => $this->calculateRate('clicked_at', 'opened_at', $fromDate),
            'bounce_rate' => $this->calculateRate('bounced_at', 'sent_at', $fromDate),
            
            'by_day' => DB::table('admin_email_tracking')
                ->where('sent_at', '>=', $fromDate)
                ->selectRaw('DATE(sent_at) as date, COUNT(*) as sent, 
                    SUM(CASE WHEN opened_at IS NOT NULL THEN 1 ELSE 0 END) as opened,
                    SUM(CASE WHEN clicked_at IS NOT NULL THEN 1 ELSE 0 END) as clicked')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            
            'top_templates' => DB::table('admin_email_tracking')
                ->join('admin_emailtemplates', 'admin_email_tracking.template_id', '=', 'admin_emailtemplates.id')
                ->where('admin_email_tracking.sent_at', '>=', $fromDate)
                ->selectRaw('admin_emailtemplates.name, COUNT(*) as sent,
                    SUM(CASE WHEN opened_at IS NOT NULL THEN 1 ELSE 0 END) as opened')
                ->groupBy('admin_emailtemplates.name')
                ->orderByDesc('sent')
                ->limit(10)
                ->get()
        ];
    }
    
    /**
     * Calculate rate
     */
    protected function calculateRate($numeratorField, $denominatorField, $fromDate)
    {
        $denominator = DB::table('admin_email_tracking')
            ->where('sent_at', '>=', $fromDate)
            ->whereNotNull($denominatorField)
            ->count();
        
        if ($denominator == 0) {
            return 0;
        }
        
        $numerator = DB::table('admin_email_tracking')
            ->where('sent_at', '>=', $fromDate)
            ->whereNotNull($numeratorField)
            ->count();
        
        return round(($numerator / $denominator) * 100, 2);
    }
}