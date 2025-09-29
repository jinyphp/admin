<?php

namespace Jiny\Admin\Services\Sms;

use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;
use Illuminate\Support\Facades\Log;

class AwsSnsDriver implements SmsDriverInterface
{
    protected $client;
    protected $config;
    
    public function __construct(array $config)
    {
        $this->config = $config;
        
        $this->client = new SnsClient([
            'version' => 'latest',
            'region' => $config['region'] ?? 'us-east-1',
            'credentials' => [
                'key' => $config['key'] ?? '',
                'secret' => $config['secret'] ?? '',
            ],
        ]);
    }
    
    /**
     * Send SMS message
     */
    public function send(string $toNumber, string $message, ?string $fromNumber = null): array
    {
        try {
            $params = [
                'Message' => $message,
                'PhoneNumber' => $this->formatPhoneNumber($toNumber),
            ];
            
            // Set sender ID if provided
            if ($fromNumber) {
                $params['MessageAttributes'] = [
                    'AWS.SNS.SMS.SenderID' => [
                        'DataType' => 'String',
                        'StringValue' => $fromNumber
                    ],
                ];
            }
            
            // Set SMS type (Promotional or Transactional)
            $smsType = $this->config['sms_type'] ?? 'Transactional';
            $params['MessageAttributes']['AWS.SNS.SMS.SMSType'] = [
                'DataType' => 'String',
                'StringValue' => $smsType
            ];
            
            // Set max price if configured
            if (isset($this->config['max_price'])) {
                $params['MessageAttributes']['AWS.SNS.SMS.MaxPrice'] = [
                    'DataType' => 'Number',
                    'StringValue' => (string) $this->config['max_price']
                ];
            }
            
            $result = $this->client->publish($params);
            
            Log::info('AWS SNS SMS sent', [
                'to' => $toNumber,
                'message_id' => $result['MessageId']
            ]);
            
            return [
                'success' => true,
                'message_id' => $result['MessageId'],
                'response_data' => $result->toArray()
            ];
            
        } catch (AwsException $e) {
            Log::error('AWS SNS SMS send failed', [
                'to' => $toNumber,
                'error' => $e->getMessage(),
                'error_code' => $e->getAwsErrorCode()
            ]);
            
            return [
                'success' => false,
                'error_code' => $e->getAwsErrorCode(),
                'error_message' => $this->getErrorMessage($e->getAwsErrorCode(), $e->getMessage())
            ];
            
        } catch (\Exception $e) {
            Log::error('Unexpected error sending AWS SNS SMS', [
                'to' => $toNumber,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error_message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get account balance/spending limit
     */
    public function getBalance(): array
    {
        try {
            // Get SMS attributes
            $result = $this->client->getSMSAttributes();
            
            $attributes = $result['attributes'] ?? [];
            
            return [
                'success' => true,
                'monthly_spend_limit' => $attributes['MonthlySpendLimit'] ?? null,
                'default_sms_type' => $attributes['DefaultSMSType'] ?? null,
                'default_sender_id' => $attributes['DefaultSenderID'] ?? null,
                'usage_report_s3_bucket' => $attributes['UsageReportS3Bucket'] ?? null,
            ];
            
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error_code' => $e->getAwsErrorCode(),
                'error_message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get message status
     */
    public function getStatus(string $messageId): array
    {
        // AWS SNS doesn't provide direct message status query
        // Status updates come through CloudWatch or delivery status topics
        return [
            'success' => false,
            'error_message' => 'AWS SNS does not support direct status queries. Use CloudWatch or delivery status topics.'
        ];
    }
    
    /**
     * Get driver name
     */
    public function getName(): string
    {
        return 'aws_sns';
    }
    
    /**
     * Format phone number
     */
    public function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove all non-numeric characters
        $number = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Add + if not present
        if (!str_starts_with($phoneNumber, '+')) {
            // Korean number
            if (substr($number, 0, 2) == '01') {
                $number = '82' . substr($number, 1);
            }
            
            // Add country code if not present
            if (substr($number, 0, 2) != '82' && strlen($number) == 10) {
                $number = '82' . ltrim($number, '0');
            }
            
            $number = '+' . $number;
        }
        
        return $number;
    }
    
    /**
     * Subscribe phone number to topic
     */
    public function subscribeToTopic(string $phoneNumber, string $topicArn): array
    {
        try {
            $result = $this->client->subscribe([
                'Protocol' => 'sms',
                'TopicArn' => $topicArn,
                'Endpoint' => $this->formatPhoneNumber($phoneNumber),
            ]);
            
            return [
                'success' => true,
                'subscription_arn' => $result['SubscriptionArn']
            ];
            
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error_code' => $e->getAwsErrorCode(),
                'error_message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Send bulk SMS via topic
     */
    public function sendToTopic(string $topicArn, string $message): array
    {
        try {
            $result = $this->client->publish([
                'Message' => $message,
                'TopicArn' => $topicArn,
                'MessageAttributes' => [
                    'AWS.SNS.SMS.SMSType' => [
                        'DataType' => 'String',
                        'StringValue' => $this->config['sms_type'] ?? 'Transactional'
                    ],
                ],
            ]);
            
            return [
                'success' => true,
                'message_id' => $result['MessageId']
            ];
            
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error_code' => $e->getAwsErrorCode(),
                'error_message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get error message
     */
    protected function getErrorMessage($code, $defaultMessage)
    {
        $errorMessages = [
            'InvalidParameter' => '잘못된 매개변수입니다. 전화번호 형식을 확인해주세요.',
            'InvalidParameterValue' => '잘못된 매개변수 값입니다.',
            'Throttled' => '요청이 너무 많습니다. 잠시 후 다시 시도해주세요.',
            'OptedOut' => '수신자가 SMS 수신을 거부했습니다.',
            'AccountSendingPaused' => '계정의 SMS 발송이 일시 중지되었습니다.',
            'SpendLimitExceeded' => '월간 SMS 지출 한도를 초과했습니다.',
            'PhoneNumberIsBlacklisted' => '전화번호가 블랙리스트에 있습니다.',
        ];
        
        return $errorMessages[$code] ?? $defaultMessage;
    }
}