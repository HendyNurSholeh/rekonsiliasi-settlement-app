<?php

namespace App\Services\ApiGateway;

use CodeIgniter\HTTP\CURLRequest;

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

    public function __construct()
    {
        $this->apiUrl = env('AKSEL_GATE_URL', 'http://localhost:8080');
        $this->username = env('AKSEL_GATE_USERNAME', 'admin');
        $this->password = env('AKSEL_GATE_PASSWORD', 'Bankkalsel1*');
        $this->client = \Config\Services::curlrequest();
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
            // Validate payload structure
            $validationErrors = $this->validateTransactionPayload($transactionData);
            if (!empty($validationErrors)) {
                log_message('error', 'AKSEL Gateway: Payload validation failed: ' . implode(', ', $validationErrors));
                return [
                    'success' => false,
                    'message' => 'Format data tidak valid: ' . implode(', ', $validationErrors)
                ];
            }

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
     * Validate transaction payload structure
     */
    private function validateTransactionPayload(array $data): array
    {
        $errors = [];
        
        // Check main structure
        if (!isset($data['requestId']) || empty($data['requestId'])) {
            $errors[] = 'requestId is required';
        }
        
        if (!isset($data['totalTx']) || !is_string($data['totalTx'])) {
            $errors[] = 'totalTx must be string';
        }
        
        if (!isset($data['data']) || !is_array($data['data'])) {
            $errors[] = 'data must be array';
        }
        
        if (isset($data['data'])) {
            foreach ($data['data'] as $index => $transaction) {
                $prefix = "Transaction[$index]: ";
                
                // Required fields
                $required = ['core', 'branchCode', 'branchName', 'debitAccount', 'creditAccount', 
                           'amount', 'description', 'referenceNumber'];
                
                foreach ($required as $field) {
                    if (!isset($transaction[$field]) || empty($transaction[$field])) {
                        $errors[] = $prefix . "$field is required";
                    }
                }
                
                // Amount must be numeric string
                if (isset($transaction['amount']) && !is_numeric($transaction['amount'])) {
                    $errors[] = $prefix . "amount must be numeric string";
                }
                
                // Account numbers validation
                if (isset($transaction['debitAccount']) && strlen($transaction['debitAccount']) < 5) {
                    $errors[] = $prefix . "debitAccount too short";
                }
                
                if (isset($transaction['creditAccount']) && strlen($transaction['creditAccount']) < 5) {
                    $errors[] = $prefix . "creditAccount too short";
                }
            }
        }
        
        return $errors;
    }

    /**
     * Format transaksi data untuk API Gateway
     */
    public function formatTransactionData(string $kdSettle, array $transactions): array
    {
        $requestId = 'SETL_' . $kdSettle . '_' . date('YmdHis');
        $apiData = [
            'requestId' => $requestId,
            'totalTx' => (string)count($transactions),
            'data' => []
        ];

        log_message('info', 'AKSEL Gateway: Formatting transaction data for kd_settle: ' . $kdSettle . ', transaction count: ' . count($transactions));

        foreach ($transactions as $index => $trx) {
            // Validate required fields
            if (empty($trx['d_DEBIT_ACCOUNT']) || empty($trx['d_CREDIT_ACCOUNT']) || empty($trx['d_NO_REF'])) {
                log_message('warning', 'AKSEL Gateway: Skipping transaction with missing required fields: ' . json_encode($trx));
                continue;
            }
            
            // Clean and validate amount
            $amount = str_replace([',', '.'], '', $trx['d_AMOUNT'] ?? '0');
            if (!is_numeric($amount)) {
                log_message('warning', 'AKSEL Gateway: Invalid amount for transaction ' . $trx['d_NO_REF'] . ': ' . ($trx['d_AMOUNT'] ?? 'null'));
                $amount = '0';
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
        
        // Update totalTx setelah filtering
        $apiData['totalTx'] = (string)count($apiData['data']);
        
        log_message('info', 'AKSEL Gateway: Final payload prepared with ' . count($apiData['data']) . ' valid transactions');
        
        return $apiData;
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