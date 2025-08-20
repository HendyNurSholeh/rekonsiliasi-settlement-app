<?php

namespace App\Services\Settlement;

use App\Models\Settlement\JurnalCaEscrowModel;
use App\Models\Settlement\SettlementMessageModel;
use CodeIgniter\Database\Database;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Validation\Validation;
use CodeIgniter\HTTP\CURLRequest;

/**
 * Service untuk menangani business logic Jurnal CA to Escrow
 * Mengikuti best practices untuk transaksi finansial dengan CI4 Features
 */
class JurnalCaEscrowService
{
    protected $model;
    protected $settlementModel;
    protected $db;
    protected $validation;
    protected $request;
    protected $externalApiBaseUrl;
    
    public function __construct()
    {
        $this->model = new JurnalCaEscrowModel();
        $this->settlementModel = new SettlementMessageModel();
        $this->db = \Config\Database::connect();
        $this->validation = \Config\Services::validation();
        $this->request = \Config\Services::request();
        
        // Konfigurasi endpoint eksternal - bisa diubah sesuai environment
        $this->externalApiBaseUrl = env('CA_ESCROW_API_URL', base_url() . 'api/settlement');
    }

    /** 
     * Proses jurnal CA to Escrow dengan transaction safety
     * 
     * @param array $data Data jurnal yang akan diproses
     * @return array Response hasil pemrosesan
     */
    public function prosesJurnal(array $data): array
    {
        // Validasi input menggunakan CI4 Validation
        if (!$this->validateInput($data)) {
            return [
                'success' => false,
                'message' => 'Data input tidak valid: ' . implode(', ', $this->validation->getErrors()),
                'error_code' => 'VALIDATION_ERROR',
                'validation_errors' => $this->validation->getErrors()
            ];
        }

        // Cek apakah jurnal sudah pernah diproses
        $existingProcess = $this->checkExistingProcess($data['kd_settle'], $data['no_ref']);
        if ($existingProcess['exists'] && $existingProcess['is_success']) {
            return [
                'success' => false,
                'message' => 'Jurnal sudah berhasil diproses sebelumnya',
                'error_code' => 'ALREADY_PROCESSED',
                'existing_core_ref' => $existingProcess['core_ref']
            ];
        }

        // Mulai database transaction menggunakan CI4 transaction
        $this->db->transBegin();

        try {
            // Insert/Update log proses dengan status PROCESSING
            $processLogId = $this->createProcessLog($data, 'PROCESSING');

            // Panggil external API untuk proses actual
            $apiResponse = $this->callExternalApi($data);

            if ($apiResponse['success']) {
                // Update status menjadi SUCCESS
                $this->updateProcessLog($processLogId, 'SUCCESS', $apiResponse);
                
                // Update jurnal data dengan response dari core banking
                $this->updateJurnalData($data, $apiResponse);

                // Commit transaction jika semua berhasil
                if ($this->db->transStatus() === false) {
                    $this->db->transRollback();
                    throw new \Exception('Database transaction failed');
                }
                
                $this->db->transCommit();

                return [
                    'success' => true,
                    'message' => 'Jurnal CA to Escrow berhasil diproses',
                    'core_ref' => $apiResponse['core_ref'],
                    'response_code' => $apiResponse['response_code'],
                    'timestamp' => date('Y-m-d H:i:s')
                ];

            } else {
                // Update status menjadi FAILED
                $this->updateProcessLog($processLogId, 'FAILED', $apiResponse);

                $this->db->transCommit(); // Commit log untuk audit trail

                return [
                    'success' => false,
                    'message' => $apiResponse['message'] ?? 'Transaksi gagal diproses',
                    'error_code' => $apiResponse['error_code'] ?? 'EXTERNAL_API_ERROR',
                    'response_code' => $apiResponse['response_code'] ?? null
                ];
            }

        } catch (\Throwable $e) {
            $this->db->transRollback();

            // Log error menggunakan CI4 log service
            log_message('error', 'JurnalCaEscrowService::prosesJurnal Exception: {message}', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem internal',
                'error_code' => 'SYSTEM_ERROR',
                'debug_info' => ENVIRONMENT === 'development' ? $e->getMessage() : null
            ];
        }
    }

    /**
     * Validasi input data menggunakan CI4 Validation
     */
    private function validateInput(array $data): bool
    {
        $rules = [
            'kd_settle' => [
                'rules' => 'required|min_length[1]|max_length[50]',
                'errors' => [
                    'required' => 'Kode settle harus diisi',
                    'min_length' => 'Kode settle minimal 1 karakter',
                    'max_length' => 'Kode settle maksimal 50 karakter'
                ]
            ],
            'no_ref' => [
                'rules' => 'required|min_length[1]|max_length[100]',
                'errors' => [
                    'required' => 'No referensi harus diisi',
                    'min_length' => 'No referensi minimal 1 karakter',
                    'max_length' => 'No referensi maksimal 100 karakter'
                ]
            ],
            'amount' => [
                'rules' => 'required|numeric|greater_than[0]',
                'errors' => [
                    'required' => 'Amount harus diisi',
                    'numeric' => 'Amount harus berupa angka',
                    'greater_than' => 'Amount harus lebih besar dari 0'
                ]
            ],
            'debit_account' => [
                'rules' => 'required|min_length[1]|max_length[50]',
                'errors' => [
                    'required' => 'Debit account harus diisi',
                    'min_length' => 'Debit account minimal 1 karakter',
                    'max_length' => 'Debit account maksimal 50 karakter'
                ]
            ],
            'credit_account' => [
                'rules' => 'required|min_length[1]|max_length[50]',
                'errors' => [
                    'required' => 'Credit account harus diisi',
                    'min_length' => 'Credit account minimal 1 karakter',
                    'max_length' => 'Credit account maksimal 50 karakter'
                ]
            ]
        ];

        return $this->validation->setRules($rules)->run($data);
    }

    /**
     * Cek apakah jurnal sudah pernah diproses
     */
    private function checkExistingProcess(string $kdSettle, string $noRef): array
    {
        $existing = $this->model->getProcessStatus($kdSettle, $noRef);
        
        return [
            'exists' => !empty($existing),
            'is_success' => !empty($existing) && $existing['status'] === 'SUCCESS',
            'core_ref' => $existing['core_ref'] ?? null,
            'status' => $existing['status'] ?? null
        ];
    }

    /**
     * Buat log proses transaksi
     */
    private function createProcessLog(array $data, string $status): int
    {
        $logData = [
            'kd_settle' => $data['kd_settle'],
            'no_ref' => $data['no_ref'],
            'amount' => $data['amount'],
            'debit_account' => $data['debit_account'],
            'credit_account' => $data['credit_account'],
            'status' => $status,
            'request_data' => json_encode($data),
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()?->getAgentString() ?? 'Unknown',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->model->insertProcessLog($logData);
    }

    /**
     * Update log proses transaksi
     */
    private function updateProcessLog(int $logId, string $status, array $response): void
    {
        $updateData = [
            'status' => $status,
            'response_code' => $response['response_code'] ?? null,
            'core_ref' => $response['core_ref'] ?? null,
            'response_data' => json_encode($response),
            'processed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (isset($response['processing_time'])) {
            $updateData['processing_time'] = $response['processing_time'];
        }

        $this->model->updateProcessLog($logId, $updateData);
    }

    /**
     * Update data jurnal dengan response dari core banking
     */
    private function updateJurnalData(array $data, array $response): void
    {
        // Update ke tabel settlement utama menggunakan model baru
        $this->settlementModel->updateSettlementResponse(
            $data['kd_settle'],
            $data['no_ref'],
            [
                'response_code' => $response['response_code'],
                'core_ref' => $response['core_ref'],
                'message' => $response['message'],
                'timestamp' => date('Y-m-d H:i:s')
            ]
        );

        // Log update untuk debugging
        log_message('info', 'Settlement data updated successfully', [
            'kd_settle' => $data['kd_settle'],
            'no_ref' => $data['no_ref'],
            'response_code' => $response['response_code'],
            'core_ref' => $response['core_ref']
        ]);
    }

    /**
     * Panggil external API menggunakan CI4 CURLRequest
     */
    private function callExternalApi(array $data): array
    {
        try {
            // Gunakan CI4 CURLRequest service
            $client = \Config\Services::curlrequest([
                'timeout' => 30,
                'connect_timeout' => 10,
                'verify' => false // Untuk development, set true untuk production
            ]);
            
            $endpoint = $this->externalApiBaseUrl . '/ca-escrow/process';
            
            $payload = [
                'kd_settle' => $data['kd_settle'],
                'no_ref' => $data['no_ref'],
                'amount' => $data['amount'],
                'debit_account' => $data['debit_account'],
                'credit_account' => $data['credit_account'],
                'timestamp' => date('Y-m-d H:i:s'),
                'client_ref' => uniqid('CAE_', true)
            ];

            // Log request untuk debugging
            log_message('info', 'Calling External API: {endpoint}', [
                'endpoint' => $endpoint,
                'payload' => $payload
            ]);

            $response = $client->post($endpoint, [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'json' => $payload
            ]);

            $statusCode = $response->getStatusCode();
            $responseData = json_decode($response->getBody(), true);

            // Log response untuk debugging
            log_message('info', 'External API Response: {status_code}', [
                'status_code' => $statusCode,
                'response' => $responseData
            ]);

            if ($statusCode === 200 && isset($responseData['success']) && $responseData['success']) {
                return [
                    'success' => true,
                    'core_ref' => $responseData['core_ref'] ?? null,
                    'response_code' => $responseData['response_code'] ?? '00',
                    'message' => $responseData['message'] ?? 'Success',
                    'processing_time' => $responseData['processing_time'] ?? null
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $responseData['message'] ?? 'External API error',
                    'error_code' => $responseData['error_code'] ?? 'API_ERROR',
                    'response_code' => $responseData['response_code'] ?? null
                ];
            }

        } catch (\Exception $e) {
            log_message('error', 'External API Call Failed: ' . $e->getMessage(), [
                'endpoint' => $endpoint ?? 'unknown',
                'error' => $e->getMessage(),
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal terhubung ke sistem core banking',
                'error_code' => 'CONNECTION_ERROR'
            ];
        }
    }

    /**
     * Get status transaksi untuk monitoring
     * 
     * @param string $kdSettle
     * @param string $noRef
     * @return array
     */
    public function getTransactionStatus(string $kdSettle, string $noRef): array
    {
        try {
            // Get status dari database log
            $logData = $this->model->getProcessStatus($kdSettle, $noRef);
            
            if (!$logData) {
                return [
                    'status' => 'NOT_FOUND',
                    'message' => 'Transaksi tidak ditemukan',
                    'data' => null
                ];
            }
            
            return [
                'status' => 'FOUND',
                'message' => 'Status transaksi ditemukan',
                'data' => [
                    'kd_settle' => $logData['kd_settle'],
                    'no_ref' => $logData['no_ref'],
                    'status' => $logData['status'],
                    'response_code' => $logData['response_code'],
                    'core_ref' => $logData['core_ref'],
                    'processing_time' => $logData['processing_time'],
                    'processed_at' => $logData['processed_at'],
                    'created_at' => $logData['created_at'],
                    'updated_at' => $logData['updated_at']
                ]
            ];
            
        } catch (\Exception $e) {
            log_message('error', 'Error getting transaction status: {message}', [
                'message' => $e->getMessage(),
                'kd_settle' => $kdSettle,
                'no_ref' => $noRef
            ]);
            
            return [
                'status' => 'ERROR',
                'message' => 'Gagal mengambil status transaksi',
                'data' => null
            ];
        }
    }

    /**
     * Set external API URL (untuk testing atau environment berbeda)
     */
    public function setExternalApiUrl(string $url): void
    {
        $this->externalApiBaseUrl = $url;
    }

    /**
     * Get external API URL
     */
    public function getExternalApiUrl(): string
    {
        return $this->externalApiBaseUrl;
    }

    /**
     * Get settlement data untuk diproses
     * 
     * @param string $kdSettle
     * @param string $refNumber
     * @return array|null
     */
    public function getSettlementData(string $kdSettle, string $refNumber): ?array
    {
        return $this->settlementModel->getSettlementByRef($kdSettle, $refNumber);
    }

    /**
     * Cek apakah settlement sudah diproses sebelumnya
     * 
     * @param string $kdSettle
     * @param string $refNumber
     * @return bool
     */
    public function isSettlementAlreadyProcessed(string $kdSettle, string $refNumber): bool
    {
        return $this->settlementModel->isSettlementProcessed($kdSettle, $refNumber);
    }

    /**
     * Get account mapping untuk settlement CA to Escrow
     * 
     * @param string $kdSettle
     * @return array
     */
    public function getCA2EscrowAccountMapping(string $kdSettle): array
    {
        return $this->settlementModel->getAccountMapping($kdSettle, 'CA-ESCR');
    }
}
