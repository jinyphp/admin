<?php

namespace Jiny\Admin\Services\Captcha;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;

class CaptchaService
{
    protected $driver;
    protected $fallbackDriver;
    protected $maxAttempts = 5;
    protected $decayMinutes = 60;
    protected $testMode = false;
    
    public function __construct()
    {
        $this->testMode = config('captcha.test_mode', false);
        $this->maxAttempts = config('captcha.rate_limit.max_attempts', 5);
        $this->decayMinutes = config('captcha.rate_limit.decay_minutes', 60);
        
        $this->initializeDriver();
    }
    
    /**
     * Initialize the CAPTCHA driver
     */
    protected function initializeDriver()
    {
        $primaryDriver = config('captcha.default', 'recaptcha');
        $fallbackDriverName = config('captcha.fallback', 'hcaptcha');
        
        try {
            $this->driver = $this->createDriver($primaryDriver);
        } catch (Exception $e) {
            Log::error('Failed to initialize primary CAPTCHA driver', [
                'driver' => $primaryDriver,
                'error' => $e->getMessage()
            ]);
            
            // Try fallback driver
            if ($fallbackDriverName && $fallbackDriverName !== $primaryDriver) {
                try {
                    $this->driver = $this->createDriver($fallbackDriverName);
                    Log::info('Using fallback CAPTCHA driver', ['driver' => $fallbackDriverName]);
                } catch (Exception $fe) {
                    throw new Exception('Failed to initialize both primary and fallback CAPTCHA drivers');
                }
            }
        }
        
        // Setup fallback driver for automatic switching
        if ($fallbackDriverName && $fallbackDriverName !== $primaryDriver) {
            try {
                $this->fallbackDriver = $this->createDriver($fallbackDriverName);
            } catch (Exception $e) {
                // Fallback driver is optional
                Log::warning('Failed to initialize fallback CAPTCHA driver', [
                    'driver' => $fallbackDriverName,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
    
    /**
     * Create a CAPTCHA driver instance
     */
    protected function createDriver($driver)
    {
        switch ($driver) {
            case 'recaptcha':
                return new RecaptchaDriver();
            case 'hcaptcha':
                return new HcaptchaDriver();
            case 'cloudflare':
                return new CloudflareDriver();
            default:
                throw new Exception("Unknown CAPTCHA driver: {$driver}");
        }
    }
    
    /**
     * Verify CAPTCHA with rate limiting and logging
     */
    public function verify($token, Request $request = null, $action = null)
    {
        $ipAddress = $request ? $request->ip() : request()->ip();
        $userAgent = $request ? $request->userAgent() : request()->userAgent();
        $sessionId = $request ? $request->session()->getId() : session()->getId();
        
        // Check if IP is whitelisted
        if ($this->isWhitelisted($ipAddress)) {
            $this->logAttempt($ipAddress, $userAgent, $action, true, null, null, [
                'whitelisted' => true
            ], $sessionId);
            return ['success' => true, 'whitelisted' => true];
        }
        
        // Check if IP is blacklisted
        if ($this->isBlacklisted($ipAddress)) {
            $this->logAttempt($ipAddress, $userAgent, $action, false, 'blacklisted', 'IP address is blacklisted', [
                'blacklisted' => true
            ], $sessionId);
            return ['success' => false, 'error' => 'Access denied'];
        }
        
        // Check rate limiting
        if (!$this->checkRateLimit($ipAddress)) {
            $this->logAttempt($ipAddress, $userAgent, $action, false, 'rate_limit', 'Rate limit exceeded', [
                'rate_limited' => true
            ], $sessionId, true);
            return ['success' => false, 'error' => 'Too many attempts. Please try again later.'];
        }
        
        // Test mode bypass
        if ($this->testMode) {
            $this->logAttempt($ipAddress, $userAgent, $action, true, null, null, [
                'test_mode' => true
            ], $sessionId);
            return ['success' => true, 'test_mode' => true];
        }
        
        // Verify with primary driver
        $result = $this->verifyWithRetry($token, $ipAddress);
        
        // Log the attempt
        $this->logAttempt(
            $ipAddress,
            $userAgent,
            $action,
            $result['success'] ?? false,
            $result['error_code'] ?? null,
            $result['error_message'] ?? null,
            $result,
            $sessionId,
            $this->isSuspicious($ipAddress, $result)
        );
        
        // Check for suspicious activity
        if ($this->isSuspicious($ipAddress, $result)) {
            $this->handleSuspiciousActivity($ipAddress);
        }
        
        return $result;
    }
    
    /**
     * Verify with retry logic and fallback
     */
    protected function verifyWithRetry($token, $ipAddress, $retries = 3)
    {
        $attempt = 0;
        $lastError = null;
        
        while ($attempt < $retries) {
            try {
                $result = $this->driver->verify($token, $ipAddress);
                
                if (isset($result['success'])) {
                    return $result;
                }
                
                // If primary driver fails but we have a fallback
                if (!($result['success'] ?? false) && $this->fallbackDriver && $attempt === $retries - 1) {
                    Log::warning('Primary CAPTCHA driver failed, trying fallback', [
                        'primary_driver' => get_class($this->driver),
                        'error' => $result['error_message'] ?? 'Unknown error'
                    ]);
                    
                    return $this->fallbackDriver->verify($token, $ipAddress);
                }
                
            } catch (Exception $e) {
                $lastError = $e;
                Log::error('CAPTCHA verification attempt failed', [
                    'attempt' => $attempt + 1,
                    'error' => $e->getMessage()
                ]);
                
                if ($attempt === $retries - 1 && $this->fallbackDriver) {
                    try {
                        Log::info('Attempting with fallback CAPTCHA driver');
                        return $this->fallbackDriver->verify($token, $ipAddress);
                    } catch (Exception $fe) {
                        Log::error('Fallback CAPTCHA driver also failed', [
                            'error' => $fe->getMessage()
                        ]);
                    }
                }
            }
            
            $attempt++;
            if ($attempt < $retries) {
                usleep(500000); // 500ms delay between retries
            }
        }
        
        return [
            'success' => false,
            'error_code' => 'verification_failed',
            'error_message' => $lastError ? $lastError->getMessage() : 'CAPTCHA verification failed after retries'
        ];
    }
    
    /**
     * Check rate limiting
     */
    protected function checkRateLimit($ipAddress)
    {
        $key = 'captcha_attempts:' . $ipAddress;
        $attempts = Cache::get($key, 0);
        
        if ($attempts >= $this->maxAttempts) {
            return false;
        }
        
        Cache::put($key, $attempts + 1, now()->addMinutes($this->decayMinutes));
        return true;
    }
    
    /**
     * Check if IP is whitelisted
     */
    protected function isWhitelisted($ipAddress)
    {
        return Cache::remember('captcha_whitelist:' . $ipAddress, 300, function () use ($ipAddress) {
            return DB::table('admin_captcha_ip_lists')
                ->where('ip_address', $ipAddress)
                ->where('list_type', 'whitelist')
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->exists();
        });
    }
    
    /**
     * Check if IP is blacklisted
     */
    protected function isBlacklisted($ipAddress)
    {
        return Cache::remember('captcha_blacklist:' . $ipAddress, 300, function () use ($ipAddress) {
            return DB::table('admin_captcha_ip_lists')
                ->where('ip_address', $ipAddress)
                ->where('list_type', 'blacklist')
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->exists();
        });
    }
    
    /**
     * Log CAPTCHA attempt
     */
    protected function logAttempt($ipAddress, $userAgent, $action, $success, $errorCode, $errorMessage, $responseData, $sessionId, $suspicious = false)
    {
        try {
            // Get existing attempt count for this IP in the last hour
            $attemptCount = DB::table('admin_captcha_logs')
                ->where('ip_address', $ipAddress)
                ->where('created_at', '>=', now()->subHour())
                ->count() + 1;
            
            DB::table('admin_captcha_logs')->insert([
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'action' => $action,
                'provider' => get_class($this->driver),
                'success' => $success,
                'score' => $responseData['score'] ?? null,
                'error_code' => $errorCode,
                'error_message' => $errorMessage,
                'response_data' => json_encode($responseData),
                'attempt_count' => $attemptCount,
                'suspicious' => $suspicious,
                'session_id' => $sessionId,
                'user_id' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (Exception $e) {
            Log::error('Failed to log CAPTCHA attempt', [
                'error' => $e->getMessage(),
                'ip' => $ipAddress
            ]);
        }
    }
    
    /**
     * Check if activity is suspicious
     */
    protected function isSuspicious($ipAddress, $result)
    {
        // Low score for reCAPTCHA v3
        if (isset($result['score']) && $result['score'] < 0.3) {
            return true;
        }
        
        // Too many failed attempts
        $failedAttempts = DB::table('admin_captcha_logs')
            ->where('ip_address', $ipAddress)
            ->where('success', false)
            ->where('created_at', '>=', now()->subHour())
            ->count();
        
        if ($failedAttempts >= 10) {
            return true;
        }
        
        // Rapid succession attempts
        $rapidAttempts = DB::table('admin_captcha_logs')
            ->where('ip_address', $ipAddress)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();
        
        if ($rapidAttempts >= 20) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Handle suspicious activity
     */
    protected function handleSuspiciousActivity($ipAddress)
    {
        Log::warning('Suspicious CAPTCHA activity detected', ['ip' => $ipAddress]);
        
        // Mark all recent logs as suspicious
        DB::table('admin_captcha_logs')
            ->where('ip_address', $ipAddress)
            ->where('created_at', '>=', now()->subHour())
            ->update(['suspicious' => true]);
        
        // Auto-block if too suspicious
        $suspiciousCount = DB::table('admin_captcha_logs')
            ->where('ip_address', $ipAddress)
            ->where('suspicious', true)
            ->where('created_at', '>=', now()->subDay())
            ->count();
        
        if ($suspiciousCount >= 50) {
            $this->blockIpAddress($ipAddress, 'Automated blocking due to suspicious activity');
        }
    }
    
    /**
     * Block an IP address
     */
    public function blockIpAddress($ipAddress, $reason = null, $duration = null)
    {
        $expiresAt = $duration ? now()->addMinutes($duration) : null;
        
        DB::table('admin_captcha_ip_lists')->updateOrInsert(
            [
                'ip_address' => $ipAddress,
                'list_type' => 'blacklist'
            ],
            [
                'reason' => $reason,
                'added_by' => auth()->user()->name ?? 'System',
                'is_active' => true,
                'expires_at' => $expiresAt,
                'updated_at' => now()
            ]
        );
        
        // Clear cache
        Cache::forget('captcha_blacklist:' . $ipAddress);
        
        // Mark logs as blocked
        DB::table('admin_captcha_logs')
            ->where('ip_address', $ipAddress)
            ->where('created_at', '>=', now()->subHour())
            ->update(['blocked' => true]);
        
        Log::info('IP address blocked', [
            'ip' => $ipAddress,
            'reason' => $reason,
            'expires_at' => $expiresAt
        ]);
    }
    
    /**
     * Add honeypot field check
     */
    public function checkHoneypot(Request $request, $fieldName = 'website')
    {
        $honeypotValue = $request->input($fieldName);
        
        if (!empty($honeypotValue)) {
            $this->logAttempt(
                $request->ip(),
                $request->userAgent(),
                'honeypot',
                false,
                'honeypot_filled',
                'Honeypot field was filled',
                ['honeypot_field' => $fieldName, 'value' => $honeypotValue],
                $request->session()->getId(),
                true
            );
            
            $this->handleSuspiciousActivity($request->ip());
            
            return false;
        }
        
        return true;
    }
    
    /**
     * Get CAPTCHA statistics
     */
    public function getStatistics($ipAddress = null, $days = 7)
    {
        $query = DB::table('admin_captcha_logs')
            ->where('created_at', '>=', now()->subDays($days));
        
        if ($ipAddress) {
            $query->where('ip_address', $ipAddress);
        }
        
        return [
            'total_attempts' => $query->count(),
            'successful' => (clone $query)->where('success', true)->count(),
            'failed' => (clone $query)->where('success', false)->count(),
            'suspicious' => (clone $query)->where('suspicious', true)->count(),
            'blocked' => (clone $query)->where('blocked', true)->count(),
            'unique_ips' => (clone $query)->distinct('ip_address')->count('ip_address'),
            'by_provider' => (clone $query)->select('provider', DB::raw('count(*) as count'))
                ->groupBy('provider')->pluck('count', 'provider'),
            'by_action' => (clone $query)->select('action', DB::raw('count(*) as count'))
                ->groupBy('action')->pluck('count', 'action')
        ];
    }
    
    /**
     * Clean old logs
     */
    public function cleanOldLogs($days = 30)
    {
        $deleted = DB::table('admin_captcha_logs')
            ->where('created_at', '<', now()->subDays($days))
            ->delete();
        
        Log::info('Cleaned old CAPTCHA logs', ['deleted' => $deleted]);
        
        return $deleted;
    }
}