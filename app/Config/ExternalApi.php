<?php

namespace App\Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Configuration untuk external services/APIs
 * Digunakan untuk integrasi dengan Core Banking System
 */
class ExternalApi extends BaseConfig
{
    /**
     * Core Banking System Configuration
     */
    public $coreBanking = [
        'base_url' => 'https://corebanking.bank.com/api/v1',
        'timeout' => 30, // seconds
        'retry_attempts' => 3,
        'retry_delay' => 1, // seconds between retries
        
        // Authentication
        'auth' => [
            'type' => 'bearer', // bearer, basic, api_key
            'username' => 'api_user',
            'password' => 'api_password',
            'api_key' => 'your_api_key_here',
            'token_endpoint' => '/auth/token'
        ],
        
        // Endpoints
        'endpoints' => [
            'jurnal_ca_escrow' => '/journal/ca-to-escrow',
            'jurnal_escrow_biller' => '/journal/escrow-to-biller',
            'account_validation' => '/accounts/validate',
            'balance_inquiry' => '/accounts/balance',
            'transaction_status' => '/transactions/status'
        ],
        
        // Request settings
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-API-Version' => '1.0'
        ]
    ];

    /**
     * Transaction Settings
     */
    public $transaction = [
        // Timeout settings
        'max_processing_time' => 60, // seconds
        'lock_timeout' => 30, // seconds untuk transaction lock
        
        // Retry settings
        'max_retry_attempts' => 3,
        'retry_delay_seconds' => 2,
        
        // Amount limits
        'min_amount' => 1000, // minimum amount in rupiah
        'max_amount' => 10000000000, // maximum amount in rupiah (10 billion)
        
        // Business rules
        'duplicate_check_hours' => 24, // check for duplicates within 24 hours
        'auto_reversal_enabled' => true,
        'audit_log_retention_days' => 365
    ];

    /**
     * Notification Settings
     */
    public $notification = [
        'email' => [
            'enabled' => true,
            'success_notification' => false, // don't send email for successful transactions
            'failure_notification' => true,
            'recipients' => [
                'admin@bank.com',
                'settlement@bank.com'
            ]
        ],
        
        'slack' => [
            'enabled' => false,
            'webhook_url' => '',
            'channel' => '#settlement-alerts'
        ]
    ];

    /**
     * Security Settings
     */
    public $security = [
        'encryption' => [
            'enabled' => true,
            'algorithm' => 'AES-256-GCM',
            'key_rotation_days' => 90
        ],
        
        'rate_limiting' => [
            'enabled' => true,
            'max_requests_per_minute' => 60,
            'max_requests_per_hour' => 1000
        ],
        
        'ip_whitelist' => [
            'enabled' => false,
            'allowed_ips' => [
                '127.0.0.1',
                '192.168.1.0/24'
            ]
        ]
    ];

    /**
     * Logging Configuration
     */
    public $logging = [
        'enabled' => true,
        'log_level' => 'info', // debug, info, warning, error
        'log_requests' => true,
        'log_responses' => true,
        'mask_sensitive_data' => true,
        'sensitive_fields' => [
            'password', 'token', 'api_key', 'authorization'
        ]
    ];

    /**
     * Environment-specific settings
     */
    public function __construct()
    {
        parent::__construct();
        
        // Override settings based on environment
        if (ENVIRONMENT === 'development') {
            $this->coreBanking['base_url'] = 'https://dev-corebanking.bank.com/api/v1';
            $this->coreBanking['timeout'] = 60;
            $this->logging['log_level'] = 'debug';
            $this->security['rate_limiting']['enabled'] = false;
        } elseif (ENVIRONMENT === 'testing') {
            $this->coreBanking['base_url'] = 'https://test-corebanking.bank.com/api/v1';
            $this->transaction['max_retry_attempts'] = 1;
        }
    }

    /**
     * Get configuration for specific service
     * 
     * @param string $service
     * @return array
     */
    public function getServiceConfig(string $service): array
    {
        switch ($service) {
            case 'core_banking':
                return $this->coreBanking;
            case 'transaction':
                return $this->transaction;
            case 'notification':
                return $this->notification;
            case 'security':
                return $this->security;
            case 'logging':
                return $this->logging;
            default:
                return [];
        }
    }
}
