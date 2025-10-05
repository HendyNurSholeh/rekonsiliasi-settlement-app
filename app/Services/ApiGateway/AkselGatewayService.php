<?php

namespace App\Services\ApiGateway;

use CodeIgniter\HTTP\CURLRequest;
use App\Models\ApiGateway\AkselgateTransactionLog;

/**
 * AKSEL Gateway Service
 * 
 * Service untuk komunikasi dengan API Gateway AKSEL
 * Handles login, transaction processing, dan error handling
 */
class AkselGatewayService
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
     * Cek apakah kd_settle dengan transaction_type sudah pernah diproses
     * Untuk prevent duplicate submission
     * 
     * @param string $kdSettle Kode settlement
     * @param string $transactionType Type transaksi (CA_ESCROW atau ESCROW_BILLER_PL)
     * @return array ['exists' => bool, 'status_code_res' => string, 'is_success' => int, ...]
     */
    public function checkDuplicateProcess(string $kdSettle, string $transactionType): array
    {
        try {
            $lastProcess = $this->logModel->getLastProcess($kdSettle, $transactionType);
            
            if ($lastProcess) {
                log_message('info', "Duplicate check: Found existing process for kd_settle: {$kdSettle}, type: {$transactionType}");
                return [
                    'exists' => true,
                    'transaction_type' => $lastProcess['transaction_type'],
                    'status_code_res' => $lastProcess['status_code_res'],
                    'response_code' => $lastProcess['response_code'],
                    'is_success' => $lastProcess['is_success'],
                    'request_id' => $lastProcess['request_id'],
                    'sent_by' => $lastProcess['sent_by'],
                    'sent_at' => $lastProcess['sent_at']
                ];
            }
            
            log_message('info', "Duplicate check: No existing process for kd_settle: {$kdSettle}, type: {$transactionType}");
            return ['exists' => false];
            
        } catch (\Exception $e) {
            log_message('error', 'Error checking duplicate process: ' . $e->getMessage());
            return ['exists' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Cek apakah kd_settle dengan transaction_type sudah pernah diproses dengan sukses
     * Untuk disable button atau show status di UI
     * 
     * @param string $kdSettle Kode settlement
     * @param string $transactionType Type transaksi (CA_ESCROW atau ESCROW_BILLER_PL)
     * @return bool True jika sudah pernah diproses dengan sukses
     */
    public function isAlreadyProcessed(string $kdSettle, string $transactionType): bool
    {
        try {
            return $this->logModel->isProcessed($kdSettle, $transactionType);
        } catch (\Exception $e) {
            log_message('error', 'Error checking process status: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Login ke API Gateway untuk mendapatkan token
     */
    public function login(): array
    {
        try {
            log_message('info', 'AKSEL Gateway: Attempting login to ' . $this->apiUrl . '/login with username: ' . $this->username);
            
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
            
            log_message('info', 'AKSEL Gateway: Login response - Status: ' . $statusCode . ', Body: ' . $responseBody);
            
            // Accept both 200 and 201 for login
            if ($statusCode === 200 || $statusCode === 201) {
                $result = json_decode($responseBody, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    log_message('error', 'AKSEL Gateway: Failed to parse login response as JSON: ' . json_last_error_msg());
                    return [
                        'success' => false,
                        'message' => 'Invalid JSON response from login endpoint'
                    ];
                }
                
                if (isset($result['data']['token'])) {
                    log_message('info', 'AKSEL Gateway: Login successful, token received');
                    return [
                        'success' => true,
                        'token' => $result['data']['token']
                    ];
                } else {
                    log_message('error', 'AKSEL Gateway: Token not found in login response: ' . json_encode($result));
                    return [
                        'success' => false,
                        'message' => 'Token tidak ditemukan dalam response'
                    ];
                }
            } else {
                log_message('error', 'AKSEL Gateway: Login failed with status: ' . $statusCode . ', response: ' . $responseBody);
                return [
                    'success' => false,
                    'message' => 'Login failed: ' . $statusCode . ' - ' . $responseBody
                ];
            }
            
        } catch (\Exception $e) {
            log_message('error', 'AKSEL Gateway: Login exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Login error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Kirim batch transaksi ke API Gateway
     */
    public function sendBatchTransactions(array $transactionData): array
    {
        try {
            // Step 1: Login untuk mendapatkan token
            $loginResult = $this->login();
            
            if (!$loginResult['success']) {
                log_message('error', 'AKSEL Gateway: Login failed: ' . $loginResult['message']);
                return [
                    'success' => false,
                    'message' => 'Login ke API Gateway gagal: ' . $loginResult['message']
                ];
            }
            
            $token = $loginResult['token'];
            log_message('info', 'AKSEL Gateway: Login successful, token received: ' . substr($token, 0, 20) . '...');
            
            // Step 2: Kirim transaksi massal
            $jsonPayload = json_encode($transactionData, JSON_PRETTY_PRINT);
            log_message('info', 'AKSEL Gateway: Sending payload: ' . $jsonPayload);
            
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
            
            log_message('info', 'AKSEL Gateway: Transaction response - Status: ' . $statusCode);
            log_message('info', 'AKSEL Gateway: Transaction response - Body: ' . $responseBody);
            
            // Parse response body
            $result = json_decode($responseBody, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'AKSEL Gateway: Failed to parse transaction response as JSON: ' . json_last_error_msg());
                return [
                    'success' => false,
                    'message' => 'Invalid JSON response from API Gateway'
                ];
            }
            
            // Accept both 200 and 201 as success
            if ($statusCode === 200 || $statusCode === 201) {
                log_message('info', 'AKSEL Gateway: Transaction request successful with status: ' . $statusCode);
                
                return [
                    'success' => true,
                    'data' => $result,
                    'status_code' => $statusCode
                ];
            } else {
                // Handle error response dari AKSEL Gateway
                log_message('error', 'AKSEL Gateway: Transaction HTTP Error: ' . $statusCode . ' - ' . $responseBody);
                
                // Extract error message dari response AKSEL Gateway
                $errorMessage = 'HTTP Error: ' . $statusCode;
                
                // Check for AKSEL Gateway format: {"responseCode":"400","responseMessage":"..."}
                if (isset($result['responseMessage']) && !empty($result['responseMessage'])) {
                    $errorMessage = $result['responseMessage'];
                    log_message('error', 'AKSEL Gateway: Error detail from responseMessage: ' . $errorMessage);
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
            log_message('error', 'AKSEL Gateway: Send transaction error: ' . $e->getCode() . ' : ' . $e->getMessage());
            log_message('error', 'AKSEL Gateway: Exception trace: ' . $e->getTraceAsString());
            
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage() . ' (Code: ' . $e->getCode() . ')'
            ];
        }
    }

    /**
     * Format transaksi data untuk API Gateway
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

        log_message('info', 'AKSEL Gateway: Formatting transaction data for kd_settle: ' . $kdSettle . ', transaction count: ' . count($transactions));

        foreach ($transactions as $index => $trx) {
            $transactionNumber = $index + 1;
            $ref = $trx['d_NO_REF'] ?? 'unknown';
            
            // Validate required fields
            if (empty($trx['d_DEBIT_ACCOUNT'])) {
                $validationErrors[] = "Transaksi #{$transactionNumber} (REF: {$ref}): Debit Account tidak boleh kosong";
                log_message('error', "AKSEL Gateway: Transaction #{$transactionNumber} - Missing debit account: " . json_encode($trx));
                continue;
            }
            
            if (empty($trx['d_CREDIT_ACCOUNT'])) {
                $validationErrors[] = "Transaksi #{$transactionNumber} (REF: {$ref}): Credit Account tidak boleh kosong";
                log_message('error', "AKSEL Gateway: Transaction #{$transactionNumber} - Missing credit account: " . json_encode($trx));
                continue;
            }
            
            if (empty($trx['d_NO_REF'])) {
                $validationErrors[] = "Transaksi #{$transactionNumber}: Reference Number tidak boleh kosong";
                log_message('error', "AKSEL Gateway: Transaction #{$transactionNumber} - Missing reference number: " . json_encode($trx));
                continue;
            }
            
            // Clean and validate amount
            $amount = str_replace([',', '.'], '', $trx['d_AMOUNT'] ?? '0');
            if (!is_numeric($amount) || $amount <= 0) {
                $validationErrors[] = "Transaksi #{$transactionNumber} (REF: {$ref}): Amount tidak valid (" . ($trx['d_AMOUNT'] ?? 'null') . "), harus berupa angka lebih dari 0";
                log_message('error', "AKSEL Gateway: Transaction #{$transactionNumber} - Invalid amount: " . ($trx['d_AMOUNT'] ?? 'null'));
                continue;
            }
            
            $transactionData = [
                'core' => 'K',
                'branchCode' => 'ID0010001',
                'branchName' => 'KANTOR PUSAT',
                'debitAccount' => trim($trx['d_DEBIT_ACCOUNT']),
                'creditAccount' => trim($trx['d_CREDIT_ACCOUNT']),
                'amount' => $amount,
                'description' => 'SETL ' . date('dmy') . '^' . 
                               (trim($trx['d_DEBIT_NAME'] ?? 'SETTLEMENT') ?: 'SETTLEMENT') . '^' . 
                               trim($trx['d_NO_REF']),
                'referenceNumber' => trim($trx['d_NO_REF']),
                'callback_url' => base_url('aksel-gate/callback?ref=' . urlencode($trx['d_NO_REF']))
            ];
            
            log_message('info', 'AKSEL Gateway: Transaction ' . ($index + 1) . ' formatted: ' . json_encode($transactionData));
            $apiData['data'][] = $transactionData;
        }
        
        // Jika ada validation errors, return error response
        if (!empty($validationErrors)) {
            log_message('error', 'AKSEL Gateway: Validation failed with ' . count($validationErrors) . ' errors');
            return [
                'success' => false,
                'message' => 'Validasi data transaksi gagal',
                'errors' => $validationErrors,
                'total_errors' => count($validationErrors)
            ];
        }
        
        // Update totalTx setelah filtering
        $apiData['totalTx'] = (string)count($apiData['data']);
        
        log_message('info', 'AKSEL Gateway: Final payload prepared with ' . count($apiData['data']) . ' valid transactions');
        
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
        
        // Step 3: Kirim ke API Gateway
        $apiResult = $this->sendBatchTransactions($apiData);
        
        // Step 4: Save log
        $logData = [
            'transaction_type' => $transactionType,
            'kd_settle' => $kdSettle,
            'request_id' => $apiData['requestId'],
            'total_transaksi' => count($apiData['data']),
            'request_payload' => json_encode($apiData),
            'status_code_res' => (string)($apiResult['status_code'] ?? 'unknown'),
            'response_code' => $apiResult['response_code'] ?? null,
            'response_message' => $apiResult['message'] ?? null,
            'response_payload' => json_encode($apiResult['data'] ?? $apiResult),
            'is_success' => $apiResult['success'] ? 1 : 0,
            'sent_by' => session('username') ?? 'system',
            'sent_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            $this->logModel->createLog($logData);
            log_message('info', "AKSEL Gateway: Log saved for kd_settle: {$kdSettle}, type: {$transactionType}, success: " . ($apiResult['success'] ? 'YES' : 'NO'));
        } catch (\Exception $e) {
            log_message('error', "AKSEL Gateway: Failed to save log - " . $e->getMessage());
            // Continue even if logging fails
        }
        
        // Step 5: Return result dengan tambahan informasi
        if ($apiResult['success']) {
            return [
                'success' => true,
                'message' => 'Transaksi berhasil dikirim ke API Gateway',
                'request_id' => $apiData['requestId'],
                'total_transaksi' => count($apiData['data']),
                'status_code' => $apiResult['status_code'] ?? 'unknown',
                'api_response' => $apiResult['data'] ?? null
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal mengirim ke API Gateway: ' . $apiResult['message'],
                'error_code' => 'API_GATEWAY_ERROR',
                'status_code' => $apiResult['status_code'] ?? null,
                'response_code' => $apiResult['response_code'] ?? null
            ];
        }
    }

    /**
     * Test connection ke API Gateway (hanya login)
     */
    public function testConnection(): array
    {
        log_message('info', 'AKSEL Gateway: Testing connection - URL: ' . $this->apiUrl);
        
        $loginResult = $this->login();
        
        if ($loginResult['success']) {
            return [
                'success' => true,
                'message' => 'API Gateway connection successful',
                'token_preview' => substr($loginResult['token'], 0, 20) . '...',
                'api_url' => $this->apiUrl,
                'username' => $this->username
            ];
        } else {
            return [
                'success' => false,
                'message' => 'API Gateway connection failed: ' . $loginResult['message'],
                'api_url' => $this->apiUrl,
                'username' => $this->username
            ];
        }
    }
}