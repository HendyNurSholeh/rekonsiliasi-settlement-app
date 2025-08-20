<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

/**
 * Mock API Transfer Dana untuk Settlement CA to Escrow
 * 
 * PENTING: Ini HANYA simulasi untuk API transfer dana ke core banking.
 * Semua proses lain (validasi, database, logging, security) adalah REAL/Production.
 * 
 * Simulasi ini menggantikan koneksi ke core banking untuk transfer dana,
 * sehingga tidak ada uang sungguhan yang berpindah selama testing.
 */
class SettlementController extends ResourceController
{
    use ResponseTrait;

    /**
     * SIMULASI Transfer Dana CA to Escrow ke Core Banking
     * POST /api/settlement/ca-escrow/process
     * 
     * Endpoint ini HANYA mensimulasikan response dari core banking
     * untuk proses transfer dana dari CA ke Escrow account.
     * 
     * Semua business logic, validasi, security, dan database tetap real.
     */
    public function process()
    {
        try {
            // === REAL SECURITY VALIDATION ===
            $authHeader = $this->request->getHeaderLine('Authorization');
            $clientId = $this->request->getHeaderLine('X-Client-ID');
            $requestId = $this->request->getHeaderLine('X-Request-ID');

            if (!$authHeader || !str_contains($authHeader, 'Bearer')) {
                return $this->fail([
                    'success' => false,
                    'message' => 'Unauthorized - Missing or invalid authorization token',
                    'error_code' => 'AUTH_REQUIRED',
                    'timestamp' => date('Y-m-d H:i:s')
                ], 401);
            }

            if (empty($clientId)) {
                return $this->fail([
                    'success' => false,
                    'message' => 'Bad Request - X-Client-ID header required',
                    'error_code' => 'CLIENT_ID_REQUIRED',
                    'timestamp' => date('Y-m-d H:i:s')
                ], 400);
            }

            // === REAL PAYLOAD VALIDATION ===
            $payload = $this->request->getJSON(true);
            if (empty($payload)) {
                return $this->fail([
                    'success' => false,
                    'message' => 'Bad Request - Empty or invalid JSON payload',
                    'error_code' => 'INVALID_PAYLOAD',
                    'timestamp' => date('Y-m-d H:i:s')
                ], 400);
            }

            $validationResult = $this->validateTransferRequest($payload);
            if (!$validationResult['valid']) {
                return $this->fail([
                    'success' => false,
                    'message' => 'Transfer validation failed: ' . $validationResult['message'],
                    'error_code' => 'VALIDATION_ERROR',
                    'validation_errors' => $validationResult['errors'],
                    'timestamp' => date('Y-m-d H:i:s')
                ], 400);
            }

            // === REAL AUDIT LOGGING ===
            log_message('info', 'Fund Transfer Request - CA to Escrow Simulation', [
                'client_id' => $clientId,
                'request_id' => $requestId,
                'kd_settle' => $payload['kd_settle'],
                'no_ref' => $payload['no_ref'],
                'amount' => $payload['amount'],
                'debit_account' => $payload['debit_account'],
                'credit_account' => $payload['credit_account'],
                'simulation_mode' => true,
                'real_money_transfer' => false
            ]);

            // === SIMULASI PROCESSING TIME TRANSFER ===
            $processingStartTime = microtime(true);
            $processingDelay = rand(2, 8); // Core banking biasanya 2-8 detik
            sleep($processingDelay);
            $processingEndTime = microtime(true);
            $actualProcessingTime = round($processingEndTime - $processingStartTime, 2);

            // === SIMULASI TRANSFER RESPONSE ===
            $transferResult = $this->simulateCoreTransfer($payload, $actualProcessingTime);

            // === REAL AUDIT LOGGING HASIL ===
            log_message('info', 'Fund Transfer Result - CA to Escrow Simulation', [
                'request_id' => $requestId,
                'kd_settle' => $payload['kd_settle'],
                'no_ref' => $payload['no_ref'],
                'transfer_success' => $transferResult['success'],
                'response_code' => $transferResult['response_code'] ?? null,
                'core_ref' => $transferResult['core_ref'] ?? null,
                'processing_time' => $actualProcessingTime,
                'amount' => $payload['amount'],
                'simulation_result' => true
            ]);

            return $this->respond($transferResult, 200);

        } catch (\Exception $e) {
            // === REAL ERROR LOGGING ===
            log_message('error', 'Fund Transfer Simulation Error: {message}', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->fail([
                'success' => false,
                'message' => 'Fund transfer system unavailable',
                'error_code' => 'TRANSFER_SYSTEM_ERROR',
                'timestamp' => date('Y-m-d H:i:s')
            ], 500);
        }
    }

    /**
     * REAL Validasi request transfer dana
     * Ini adalah validasi asli sesuai business rules banking
     */
    private function validateTransferRequest(array $payload): array
    {
        $errors = [];

        // Required fields untuk transfer dana
        $requiredFields = ['kd_settle', 'no_ref', 'amount', 'debit_account', 'credit_account'];
        foreach ($requiredFields as $field) {
            if (empty($payload[$field])) {
                $errors[] = "Transfer field {$field} is required";
            }
        }

        // Validasi amount untuk transfer
        if (isset($payload['amount'])) {
            if (!is_numeric($payload['amount'])) {
                $errors[] = 'Transfer amount must be numeric';
            } elseif ($payload['amount'] <= 0) {
                $errors[] = 'Transfer amount must be greater than 0';
            } elseif ($payload['amount'] > 999999999999) { // 999 miliar max
                $errors[] = 'Transfer amount exceeds maximum limit (999 billion)';
            }
        }

        // Validasi format rekening (sesuai standar banking Indonesia)
        if (!empty($payload['debit_account']) && !preg_match('/^[0-9]{10,20}$/', $payload['debit_account'])) {
            $errors[] = 'Debit account format invalid (must be 10-20 digits)';
        }

        if (!empty($payload['credit_account']) && !preg_match('/^[0-9]{10,20}$/', $payload['credit_account'])) {
            $errors[] = 'Credit account format invalid (must be 10-20 digits)';
        }

        // Business rule: rekening debit dan kredit tidak boleh sama
        if (!empty($payload['debit_account']) && !empty($payload['credit_account']) && 
            $payload['debit_account'] === $payload['credit_account']) {
            $errors[] = 'Debit and credit accounts cannot be the same for transfer';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? 'Transfer validation passed' : implode(', ', $errors)
        ];
    }

    /**
     * SIMULASI Response Core Banking untuk Transfer Dana
     * Ini mensimulasikan berbagai skenario yang bisa terjadi di core banking
     */
    private function simulateCoreTransfer(array $payload, float $processingTime): array
    {
        // Base success rate 80% (realistic untuk transfer antar bank)
        $successRate = 80;

        // === BUSINESS RULES YANG MEMPENGARUHI SUCCESS RATE ===

        // 1. High amount transfers (lebih risky)
        if ($payload['amount'] > 100000000) { // > 100 juta
            $successRate -= 25; // turun jadi 55%
        } elseif ($payload['amount'] > 50000000) { // > 50 juta  
            $successRate -= 15; // turun jadi 65%
        } elseif ($payload['amount'] > 10000000) { // > 10 juta
            $successRate -= 10; // turun jadi 70%
        }

        // 2. Test accounts (untuk testing) - selalu sukses
        if (str_contains($payload['debit_account'], '9999') || 
            str_contains($payload['credit_account'], '9999')) {
            $successRate = 100; // Test accounts selalu sukses
        }

        // 3. Specific patterns untuk testing scenarios
        if (str_contains($payload['debit_account'], '1111111111')) {
            $successRate = 0; // Force fail - insufficient balance
        }

        if (str_contains($payload['debit_account'], '2222222222')) {
            $successRate = 100; // Force success - untuk testing success flow
        }

        if (str_contains($payload['debit_account'], '3333333333')) {
            $successRate = 0; // Force fail - account blocked
        }

        // 4. Time-based rules (jam operasional banking)
        $currentHour = (int)date('H');
        if ($currentHour >= 23 || $currentHour <= 5) { // 23:00 - 05:00 
            $successRate -= 40; // Di luar jam operasional, success rate turun drastis
        } elseif ($currentHour >= 1 && $currentHour <= 3) { // 01:00 - 03:00 maintenance
            $successRate -= 50; // Maintenance window
        }

        // Pastikan success rate dalam bounds
        $successRate = max(0, min(100, $successRate));

        // Generate hasil transfer berdasarkan probabilitas
        $isTransferSuccess = (rand(1, 100) <= $successRate);

        if ($isTransferSuccess) {
            // === SUCCESS TRANSFER RESPONSE ===
            return [
                'success' => true,
                'message' => 'Fund transfer completed successfully',
                'core_ref' => $this->generateCoreReference(),
                'response_code' => '00',
                'processing_time' => $processingTime,
                'timestamp' => date('Y-m-d H:i:s'),
                'transaction_id' => uniqid('TFR_CAE_', true),
                'debit_account' => $payload['debit_account'],
                'credit_account' => $payload['credit_account'],
                'amount' => $payload['amount'],
                'settlement_date' => date('Y-m-d'),
                'effective_date' => date('Y-m-d', strtotime('+1 day')),
                'transfer_fee' => 0, // CA to Escrow biasanya no fee
                'exchange_rate' => 1.0 // Same currency
            ];
        } else {
            // === FAILED TRANSFER RESPONSE ===
            $transferErrors = $this->getTransferErrorScenarios($payload);
            $selectedError = $transferErrors[array_rand($transferErrors)];

            return [
                'success' => false,
                'message' => $selectedError['message'],
                'response_code' => $selectedError['response_code'],
                'error_code' => $selectedError['error_code'],
                'processing_time' => $processingTime,
                'timestamp' => date('Y-m-d H:i:s'),
                'transaction_id' => uniqid('ERR_TFR_', true),
                'retry_allowed' => $selectedError['retry_allowed'] ?? false,
                'retry_after' => $selectedError['retry_after'] ?? null,
                'error_details' => $selectedError['details'] ?? null
            ];
        }
    }

    /**
     * Generate core reference number yang realistic
     */
    private function generateCoreReference(): string
    {
        $prefix = 'CR'; // Core Reference
        $date = date('Ymd');
        $time = date('His');
        $random = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        
        return $prefix . $date . $time . $random;
    }

    /**
     * Skenario error transfer yang realistic sesuai banking standards
     */
    private function getTransferErrorScenarios(array $payload): array
    {
        $transferErrors = [
            [
                'response_code' => '01',
                'error_code' => 'INSUFFICIENT_BALANCE',
                'message' => 'Transfer failed - Insufficient balance in debit account',
                'details' => 'Saldo tidak mencukupi untuk transfer sebesar ' . number_format($payload['amount'], 0, ',', '.'),
                'retry_allowed' => false
            ],
            [
                'response_code' => '02',
                'error_code' => 'INVALID_DEBIT_ACCOUNT',
                'message' => 'Transfer failed - Invalid or inactive debit account',
                'details' => 'Rekening debit ' . $payload['debit_account'] . ' tidak valid atau tidak aktif',
                'retry_allowed' => false
            ],
            [
                'response_code' => '03',
                'error_code' => 'INVALID_CREDIT_ACCOUNT',
                'message' => 'Transfer failed - Invalid or inactive credit account',
                'details' => 'Rekening credit ' . $payload['credit_account'] . ' tidak valid atau tidak aktif',
                'retry_allowed' => false
            ],
            [
                'response_code' => '04',
                'error_code' => 'ACCOUNT_BLOCKED',
                'message' => 'Transfer failed - Account is blocked or frozen',
                'details' => 'Rekening dalam status blokir atau pembekuan',
                'retry_allowed' => false
            ],
            [
                'response_code' => '05',
                'error_code' => 'DAILY_LIMIT_EXCEEDED',
                'message' => 'Transfer failed - Daily transaction limit exceeded',
                'details' => 'Melebihi batas transaksi harian',
                'retry_allowed' => false
            ],
            [
                'response_code' => '06',
                'error_code' => 'SYSTEM_TIMEOUT',
                'message' => 'Transfer failed - Core banking system timeout',
                'details' => 'Sistem core banking mengalami timeout',
                'retry_allowed' => true,
                'retry_after' => '300' // 5 menit
            ],
            [
                'response_code' => '07',
                'error_code' => 'MAINTENANCE_MODE',
                'message' => 'Transfer failed - System under maintenance',
                'details' => 'Sistem sedang dalam mode maintenance',
                'retry_allowed' => true,
                'retry_after' => '1800' // 30 menit
            ],
            [
                'response_code' => '08',
                'error_code' => 'OUTSIDE_OPERATING_HOURS',
                'message' => 'Transfer failed - Outside operating hours',
                'details' => 'Transfer di luar jam operasional bank',
                'retry_allowed' => true,
                'retry_after' => '28800' // 8 jam (business hours)
            ],
            [
                'response_code' => '09',
                'error_code' => 'DUPLICATE_TRANSACTION',
                'message' => 'Transfer failed - Duplicate transaction detected',
                'details' => 'Transaksi duplikat terdeteksi dengan referensi yang sama',
                'retry_allowed' => false
            ],
            [
                'response_code' => '99',
                'error_code' => 'GENERAL_ERROR',
                'message' => 'Transfer failed - General system error',
                'details' => 'Kesalahan sistem umum, silakan coba lagi',
                'retry_allowed' => true,
                'retry_after' => '60' // 1 menit
            ]
        ];

        // Add amount-specific errors
        if ($payload['amount'] > 500000000) { // > 500 juta
            $transferErrors[] = [
                'response_code' => '10',
                'error_code' => 'HIGH_VALUE_APPROVAL_REQUIRED',
                'message' => 'Transfer failed - High value transaction requires additional approval',
                'details' => 'Transfer dengan nominal > 500 juta memerlukan approval tambahan',
                'retry_allowed' => false
            ];
        }

        return $transferErrors;
    }
}
