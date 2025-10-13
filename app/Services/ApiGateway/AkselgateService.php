<?php

namespace App\Services\ApiGateway;

use CodeIgniter\HTTP\CURLRequest;
use App\Models\ApiGateway\AkselgateTransactionLog;

/**
 * Akselgate Service
 * 
 * Service untuk komunikasi dengan Akselgate API
 * Handles login, transaction processing, dan error handling
 */
class AkselgateService
{
    private $apiUrl;
    private $username;
    private $password;
    private $client;
    private $logModel;

    public function __construct()
    {
        $this->apiUrl = env('AKSEL_GATE_URL', 'http://localhost:8080');
        $this->username = env('AKSEL_GATE_USERNAME', 'admin');
        $this->password = env('AKSEL_GATE_PASSWORD', 'Bankkalsel1*');
        $this->client = \Config\Services::curlrequest();
        $this->logModel = new AkselgateTransactionLog();
    }

    /**
     * Cek apakah kd_settle dengan transaction_type sudah pernah diproses SUKSES
     * Untuk prevent duplicate submission jika sudah berhasil
     * 
     * Aturan Bisnis:
     * - Jika is_success = 1 (SUKSES): Return exists = true, tidak boleh proses ulang
     * - Jika is_success = 0 (GAGAL): Return exists = false, boleh proses ulang
     * 
     * @param string $kdSettle Kode settlement
     * @param string $transactionType Type transaksi (CA_ESCROW atau ESCROW_BILLER_PL)
     * @return array ['exists' => bool, 'status_code_res' => string, 'is_success' => int, ...]
     */
    public function checkDuplicateProcess(string $kdSettle, string $transactionType): array
    {
        try {
            // Cek apakah ada record dengan is_success = 1 (sudah berhasil)
            $successRecord = $this->logModel->checkSuccessExists($kdSettle, $transactionType);
            
            if ($successRecord) {
                log_message('info', "Duplicate check: Found SUCCESS record for kd_settle: {$kdSettle}, type: {$transactionType}, attempt: {$successRecord['attempt_number']}");
                return [
                    'exists' => true,
                    'transaction_type' => $successRecord['transaction_type'],
                    'attempt_number' => $successRecord['attempt_number'],
                    'status_code_res' => $successRecord['status_code_res'],
                    'response_code' => $successRecord['response_code'],
                    'is_success' => $successRecord['is_success'],
                    'request_id' => $successRecord['request_id'],
                    'sent_by' => $successRecord['sent_by'],
                    'sent_at' => $successRecord['sent_at']
                ];
            }
            
            // Tidak ada record sukses, boleh proses (bisa first attempt atau retry)
            log_message('info', "Duplicate check: No SUCCESS record for kd_settle: {$kdSettle}, type: {$transactionType} - Process allowed");
            return ['exists' => false];
            
        } catch (\Exception $e) {
            log_message('error', 'Error checking duplicate process: ' . $e->getMessage());
            return ['exists' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Cek apakah kd_settle dengan transaction_type sudah pernah diproses
     * Untuk disable/enable button atau show status di UI
     * 
     * Return:
     * - ['processed' => false] jika belum pernah diproses
     * - ['processed' => true, 'is_success' => 1] jika sudah sukses (disable button, show "Sudah Diproses")
     * - ['processed' => true, 'is_success' => 0] jika gagal (enable button "Proses Ulang")
     * 
     * @param string $kdSettle Kode settlement
     * @param string $transactionType Type transaksi (CA_ESCROW atau ESCROW_BILLER_PL)
     * @return array Status dan detail attempt terbaru
     */
    public function isAlreadyProcessed(string $kdSettle, string $transactionType): array
    {
        try {
            $latestAttempt = $this->logModel->getLatestAttempt($kdSettle, $transactionType);
            
            if ($latestAttempt) {
                return [
                    'processed' => true,
                    'is_success' => $latestAttempt['is_success'],
                    'attempt_number' => $latestAttempt['attempt_number'],
                    'status_code_res' => $latestAttempt['status_code_res'],
                    'response_message' => $latestAttempt['response_message'],
                    'sent_at' => $latestAttempt['sent_at']
                ];
            }
            
            return ['processed' => false];
            
        } catch (\Exception $e) {
            log_message('error', 'Error checking process status: ' . $e->getMessage());
            return ['processed' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Login ke Akselgate untuk mendapatkan token
     */
    public function login(): array
    {
        try {
            log_message('info', 'Akselgate: Attempting login to ' . $this->apiUrl . '/login with username: ' . $this->username);
            
            $response = $this->client->post($this->apiUrl . '/login', [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'username' => $this->username,
                    'password' => $this->password
                ]),
                'timeout' => 30
            ]);
            
            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody();
            
            log_message('info', 'Akselgate: Login response - Status: ' . $statusCode . ', Body: ' . $responseBody);
            
            // Accept both 200 and 201 for login
            if ($statusCode === 200 || $statusCode === 201) {
                $result = json_decode($responseBody, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    log_message('error', 'Akselgate: Failed to parse login response as JSON: ' . json_last_error_msg());
                    return [
                        'success' => false,
                        'message' => 'Invalid JSON response from login endpoint'
                    ];
                }
                
                if (isset($result['data']['token'])) {
                    log_message('info', 'Akselgate: Login successful, token received');
                    return [
                        'success' => true,
                        'token' => $result['data']['token']
                    ];
                } else {
                    log_message('error', 'Akselgate: Token not found in login response: ' . json_encode($result));
                    return [
                        'success' => false,
                        'message' => 'Token tidak ditemukan dalam response'
                    ];
                }
            } else {
                log_message('error', 'Akselgate: Login failed with status: ' . $statusCode . ', response: ' . $responseBody);
                return [
                    'success' => false,
                    'message' => 'Login failed: ' . $statusCode . ' - ' . $responseBody
                ];
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Akselgate: Login exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Login error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Kirim batch transaksi ke Akselgate
     */
    public function sendBatchTransactions(array $transactionData): array
    {
        try {
            // Step 1: Login untuk mendapatkan token
            $loginResult = $this->login();
            
            if (!$loginResult['success']) {
                log_message('error', 'Akselgate: Login failed: ' . $loginResult['message']);
                return [
                    'success' => false,
                    'message' => 'Login ke Akselgate gagal: ' . $loginResult['message']
                ];
            }
            
            $token = $loginResult['token'];
            log_message('info', 'Akselgate: Login successful, token received: ' . substr($token, 0, 20) . '...');
            
            // Step 2: Kirim transaksi massal
            $jsonPayload = json_encode($transactionData, JSON_PRETTY_PRINT);
            log_message('info', 'Akselgate: Sending payload: ' . $jsonPayload);
            
            $response = $this->client->post($this->apiUrl . '/transaction/insert', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'body' => json_encode($transactionData),
                'timeout' => 60,
                'http_errors' => false // PENTING: Jangan throw exception untuk 4xx/5xx status codes
            ]);
            
            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody();
            
            log_message('info', 'Akselgate: Transaction response - Status: ' . $statusCode);
            log_message('info', 'Akselgate: Transaction response - Body: ' . $responseBody);
            
            // Parse response body
            $result = json_decode($responseBody, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'Akselgate: Failed to parse transaction response as JSON: ' . json_last_error_msg());
                return [
                    'success' => false,
                    'message' => 'Invalid JSON response from Akselgate'
                ];
            }
            
            // Accept both 200 and 201 as success
            if ($statusCode === 200 || $statusCode === 201) {
                log_message('info', 'Akselgate: Transaction request successful with status: ' . $statusCode);
                
                return [
                    'success' => true,
                    'data' => $result,
                    'status_code' => $statusCode
                ];
            } else {
                // Handle error response dari Akselgate
                log_message('error', 'Akselgate: Transaction HTTP Error: ' . $statusCode . ' - ' . $responseBody);
                
                // Extract error message dari response Akselgate
                $errorMessage = 'HTTP Error: ' . $statusCode;
                
                // Check for Akselgate format: {"responseCode":"400","responseMessage":"..."}
                if (isset($result['responseMessage']) && !empty($result['responseMessage'])) {
                    $errorMessage = $result['responseMessage'];
                    log_message('error', 'Akselgate: Error detail from responseMessage: ' . $errorMessage);
                } 
                // Check for standard error formats
                elseif (isset($result['message']) && !empty($result['message'])) {
                    $errorMessage = $result['message'];
                } 
                elseif (isset($result['error']) && !empty($result['error'])) {
                    $errorMessage = $result['error'];
                }
                
                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'response_code' => $result['responseCode'] ?? null,
                    'status_code' => $statusCode
                ];
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Akselgate: Send transaction error: ' . $e->getCode() . ' : ' . $e->getMessage());
            log_message('error', 'Akselgate: Exception trace: ' . $e->getTraceAsString());
            
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage() . ' (Code: ' . $e->getCode() . ')'
            ];
        }
    }

    /**
     * Format transaksi data untuk Akselgate
     * Return error jika ada transaksi yang tidak valid
     */
    public function formatTransactionData(string $kdSettle, array $transactions): array
    {
        $requestId = 'SETL_' . $kdSettle . '_' . date('YmdHis');
        $apiData = [
            'requestId' => $requestId,
            'totalTx' => (string)count($transactions),
            'data' => []
        ];
        
        $validationErrors = [];

        log_message('info', 'Akselgate: Formatting transaction data for kd_settle: ' . $kdSettle . ', transaction count: ' . count($transactions));

        foreach ($transactions as $index => $trx) {
            $transactionNumber = $index + 1;
            $ref = $trx['d_NO_REF'] ?? 'unknown';
            
            // Validate required fields
            if (empty($trx['d_DEBIT_ACCOUNT'])) {
                $validationErrors[] = "Transaksi #{$transactionNumber} (REF: {$ref}): Debit Account tidak boleh kosong";
                log_message('error', "Akselgate: Transaction #{$transactionNumber} - Missing debit account: " . json_encode($trx));
                continue;
            }
            
            if (empty($trx['d_CREDIT_ACCOUNT'])) {
                $validationErrors[] = "Transaksi #{$transactionNumber} (REF: {$ref}): Credit Account tidak boleh kosong";
                log_message('error', "Akselgate: Transaction #{$transactionNumber} - Missing credit account: " . json_encode($trx));
                continue;
            }
            
            if (empty($trx['d_NO_REF'])) {
                $validationErrors[] = "Transaksi #{$transactionNumber}: Reference Number tidak boleh kosong";
                log_message('error', "Akselgate: Transaction #{$transactionNumber} - Missing reference number: " . json_encode($trx));
                continue;
            }
            
            // Clean and validate amount
            $amount = str_replace([',', '.'], '', $trx['d_AMOUNT'] ?? '0');
            if (!is_numeric($amount) || $amount <= 0) {
                $validationErrors[] = "Transaksi #{$transactionNumber} (REF: {$ref}): Amount tidak valid (" . ($trx['d_AMOUNT'] ?? 'null') . "), harus berupa angka lebih dari 0";
                log_message('error', "Akselgate: Transaction #{$transactionNumber} - Invalid amount: " . ($trx['d_AMOUNT'] ?? 'null'));
                continue;
            }
            
            $transactionData = [
                'core' => trim($trx['d_CORE']),
                'branchCode' => trim($trx['d_BRANCH_CODE']),
                'branchName' => trim($trx['d_BRANCH_NAME']),
                'debitAccount' => trim($trx['d_DEBIT_ACCOUNT']),
                'creditAccount' => trim($trx['d_CREDIT_ACCOUNT']),
                'amount' => $amount,
                'description' => $trx['d_DESCRIPTION'],
                'referenceNumber' => trim($trx['d_NO_REF']),
                'callback_url' => base_url('aksel-gate/callback?ref=' . urlencode($trx['d_NO_REF']))
            ];
            
            log_message('info', 'Akselgate: Transaction ' . ($index + 1) . ' formatted: ' . json_encode($transactionData));
            $apiData['data'][] = $transactionData;
        }
        
        // Jika ada validation errors, return error response
        if (!empty($validationErrors)) {
            log_message('error', 'Akselgate: Validation failed with ' . count($validationErrors) . ' errors');
            return [
                'success' => false,
                'message' => 'Validasi data transaksi gagal',
                'errors' => $validationErrors,
                'total_errors' => count($validationErrors)
            ];
        }
        
        // Update totalTx setelah filtering
        $apiData['totalTx'] = (string)count($apiData['data']);
        
        log_message('info', 'Akselgate: Final payload prepared with ' . count($apiData['data']) . ' valid transactions');
        
        return [
            'success' => true,
            'data' => $apiData
        ];
    }

    /**
     * Process complete batch transaction workflow
     * Menggabungkan format, validate, send, dan logging dalam satu method
     * 
     * @param string $kdSettle Kode settlement
     * @param array $transactions Array data transaksi dari database
     * @param string $transactionType Type transaksi (CA_ESCROW atau ESCROW_BILLER_PL)
     * @return array Response dengan success status dan data/error
     */
    public function processBatchTransaction(string $kdSettle, array $transactions, string $transactionType): array
    {
        // Validasi input
        if (empty($kdSettle)) {
            return [
                'success' => false,
                'message' => 'Kode settle tidak boleh kosong',
                'error_code' => 'INVALID_INPUT'
            ];
        }

        if (empty($transactions)) {
            return [
                'success' => false,
                'message' => 'Tidak ada data transaksi untuk kode settle: ' . $kdSettle,
                'error_code' => 'NO_TRANSACTION_DATA'
            ];
        }

        // Step 1: Format dan validasi transaksi
        $formatResult = $this->formatTransactionData($kdSettle, $transactions);
        
        if (!$formatResult['success']) {
            return $formatResult; // Return validation errors
        }
        
        $apiData = $formatResult['data'];
        
        // Step 2: Cek apakah ada transaksi valid
        if (empty($apiData['data'])) {
            return [
                'success' => false,
                'message' => 'Tidak ada transaksi valid untuk diproses dari kode settle: ' . $kdSettle,
                'error_code' => 'NO_VALID_TRANSACTION'
            ];
        }
        
        // Step 3: Get next attempt number dan mark previous as not latest
        $attemptNumber = $this->logModel->getNextAttemptNumber($kdSettle, $transactionType);
        
        // Mark all previous attempts as not latest before inserting new one
        if ($attemptNumber > 1) {
            $this->logModel->markAsNotLatest($kdSettle, $transactionType);
            log_message('info', "Akselgate: Marked previous attempts as not latest for kd_settle: {$kdSettle}, type: {$transactionType}");
        }
        
        log_message('info', "Akselgate: Processing attempt #{$attemptNumber} for kd_settle: {$kdSettle}, type: {$transactionType}");
        
        // Step 4: Kirim ke Akselgate
        $apiResult = $this->sendBatchTransactions($apiData);
        
        // Step 5: Save log dengan versioning
        $logData = [
            'transaction_type' => $transactionType,
            'kd_settle' => $kdSettle,
            'request_id' => $apiData['requestId'],
            'attempt_number' => $attemptNumber,
            'total_transaksi' => count($apiData['data']),
            'request_payload' => json_encode($apiData),
            'status_code_res' => (string)($apiResult['status_code'] ?? 'unknown'),
            'response_code' => $apiResult['response_code'] ?? null,
            'response_message' => $apiResult['message'] ?? null,
            'response_payload' => json_encode($apiResult['data'] ?? $apiResult),
            'is_success' => $apiResult['success'] ? 1 : 0,
            'is_latest' => 1, // This is the latest attempt
            'sent_by' => session('username') ?? 'system',
            'sent_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            $this->logModel->createLog($logData);
            log_message('info', "Akselgate: Log saved - kd_settle: {$kdSettle}, type: {$transactionType}, attempt: {$attemptNumber}, success: " . ($apiResult['success'] ? 'YES' : 'NO'));
        } catch (\Exception $e) {
            log_message('error', "Akselgate: Failed to save log - " . $e->getMessage());
            // Continue even if logging fails
        }
        
        // Step 6: Return result dengan tambahan informasi
        if ($apiResult['success']) {
            return [
                'success' => true,
                'message' => 'Transaksi berhasil dikirim ke Akselgate',
                'request_id' => $apiData['requestId'],
                'attempt_number' => $attemptNumber,
                'total_transaksi' => count($apiData['data']),
                'status_code' => $apiResult['status_code'] ?? 'unknown',
                'api_response' => $apiResult['data'] ?? null
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal mengirim ke Akselgate: ' . $apiResult['message'],
                'error_code' => 'AKSELGATE_ERROR',
                'attempt_number' => $attemptNumber,
                'status_code' => $apiResult['status_code'] ?? null,
                'response_code' => $apiResult['response_code'] ?? null
            ];
        }
    }

    /**
     * Test connection ke Akselgate (hanya login)
     */
    public function testConnection(): array
    {
        log_message('info', 'Akselgate: Testing connection - URL: ' . $this->apiUrl);
        
        $loginResult = $this->login();
        
        if ($loginResult['success']) {
            return [
                'success' => true,
                'message' => 'Akselgate connection successful',
                'token_preview' => substr($loginResult['token'], 0, 20) . '...',
                'api_url' => $this->apiUrl,
                'username' => $this->username
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Akselgate connection failed: ' . $loginResult['message'],
                'api_url' => $this->apiUrl,
                'username' => $this->username
            ];
        }
    }
}