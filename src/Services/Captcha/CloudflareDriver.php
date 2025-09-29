<?php

namespace Jiny\Admin\Services\Captcha;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudflareDriver implements CaptchaDriverInterface
{
    protected $secretKey;
    protected $verifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    
    public function __construct()
    {
        $this->secretKey = config('captcha.drivers.cloudflare.secret_key');
    }
    
    /**
     * Verify the CAPTCHA response
     */
    public function verify($token, $ipAddress = null)
    {
        if (!$this->secretKey) {
            return [
                'success' => false,
                'error_code' => 'missing_secret_key',
                'error_message' => 'Cloudflare Turnstile secret key is not configured'
            ];
        }
        
        try {
            $response = Http::asForm()->post($this->verifyUrl, [
                'secret' => $this->secretKey,
                'response' => $token,
                'remoteip' => $ipAddress
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => $data['success'] ?? false,
                    'challenge_ts' => $data['challenge_ts'] ?? null,
                    'hostname' => $data['hostname'] ?? null,
                    'error_codes' => $data['error-codes'] ?? [],
                    'error_code' => isset($data['error-codes'][0]) ? $data['error-codes'][0] : null,
                    'error_message' => $this->getErrorMessage($data['error-codes'] ?? []),
                    'action' => $data['action'] ?? null,
                    'cdata' => $data['cdata'] ?? null
                ];
            }
            
            return [
                'success' => false,
                'error_code' => 'http_error',
                'error_message' => 'Failed to connect to Cloudflare Turnstile API',
                'http_status' => $response->status()
            ];
            
        } catch (\Exception $e) {
            Log::error('Cloudflare Turnstile verification failed', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error_code' => 'exception',
                'error_message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get the widget HTML
     */
    public function getWidget($options = [])
    {
        $siteKey = config('captcha.drivers.cloudflare.site_key');
        $theme = $options['theme'] ?? 'auto';
        $size = $options['size'] ?? 'normal';
        $action = $options['action'] ?? null;
        $cData = $options['cdata'] ?? null;
        $callback = $options['callback'] ?? null;
        $errorCallback = $options['error-callback'] ?? null;
        
        $attributes = [
            'class' => 'cf-turnstile',
            'data-sitekey' => $siteKey,
            'data-theme' => $theme,
            'data-size' => $size
        ];
        
        if ($action) {
            $attributes['data-action'] = $action;
        }
        
        if ($cData) {
            $attributes['data-cdata'] = $cData;
        }
        
        if ($callback) {
            $attributes['data-callback'] = $callback;
        }
        
        if ($errorCallback) {
            $attributes['data-error-callback'] = $errorCallback;
        }
        
        $attributeString = '';
        foreach ($attributes as $key => $value) {
            $attributeString .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }
        
        return '<div' . $attributeString . '></div>';
    }
    
    /**
     * Get the script URL
     */
    public function getScriptUrl()
    {
        return 'https://challenges.cloudflare.com/turnstile/v0/api.js';
    }
    
    /**
     * Get human-readable error message
     */
    protected function getErrorMessage($errorCodes)
    {
        if (empty($errorCodes)) {
            return null;
        }
        
        $messages = [
            'missing-input-secret' => 'The secret key is missing',
            'invalid-input-secret' => 'The secret key is invalid',
            'missing-input-response' => 'The response token is missing',
            'invalid-input-response' => 'The response token is invalid',
            'bad-request' => 'The request is invalid',
            'timeout-or-duplicate' => 'The response token has expired or has already been used',
            'internal-error' => 'An internal error occurred'
        ];
        
        $errorMessages = [];
        foreach ($errorCodes as $code) {
            $errorMessages[] = $messages[$code] ?? $code;
        }
        
        return implode(', ', $errorMessages);
    }
}