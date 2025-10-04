<?php

namespace App\Controllers\Settlement;

use App\Controllers\BaseController;
use App\Libraries\EventLogEnum;
use App\Libraries\LogEnum;
use App\Models\ProsesModel;
use App\Models\Settlement\JurnalCaEscrowProcessLog;
use App\Models\Settlement\SettlementMessageModel;
use App\Services\ApiGateway\AkselGatewayService;
use App\Traits\HasLogActivity;

class JurnalCaEscrowController extends BaseController
{
    use HasLogActivity;
    
    protected $prosesModel;
    protected $akselGatewayService;
    protected $settlementMessageModel;
    protected $processLogModel;

    public function __construct()
    {
        $this->prosesModel = new ProsesModel();
        $this->akselGatewayService = new AkselGatewayService();
        $this->settlementMessageModel = new SettlementMessageModel();
        $this->processLogModel = new JurnalCaEscrowProcessLog();
    }

    /**
     * Jurnal CA to Escrow
     * Menampilkan data jurnal CA to Escrow menggunakan stored procedure p_get_jurnal_ca_to_escrow
     */
    public function index()
    {
        $tanggalData = $this->request->getGet('tanggal') ?? $this->prosesModel->getDefaultDate();

        $data = [
            'title' => 'Jurnal CA to Escrow',
            'tanggalData' => $tanggalData,
            'route' => 'settlement/jurnal-ca-escrow'
        ];

        $this->logActivity([
			'log_name' => LogEnum::VIEW,
			'description' => session('username') . ' mengakses Halaman ' . $data['title'],
			'event' => EventLogEnum::VERIFIED,
			'subject' => '-',
		]);

        return $this->render('settlement/jurnal_ca_escrow/index.blade.php', $data);
    }

    /**
     * DataTables AJAX endpoint for jurnal CA to Escrow
     */
    public function datatable()
    {
        // Get parameters from both GET and POST to handle DataTables requests
        $tanggalData = $this->request->getGet('tanggal') ?? $this->request->getPost('tanggal') ?? $this->prosesModel->getDefaultDate();
        
        // Debug log
        log_message('info', 'Jurnal CA to Escrow DataTable parameters - Tanggal: ' . $tanggalData);
        
        // DataTables parameters
        $draw = $this->request->getGet('draw') ?? $this->request->getPost('draw') ?? 1;
        $start = $this->request->getGet('start') ?? $this->request->getPost('start') ?? 0;
        $length = $this->request->getGet('length') ?? $this->request->getPost('length') ?? 15; // Ubah default ke 15
        
        // Debug log parameters
        log_message('info', 'DataTable Parameters - Draw: ' . $draw . ', Start: ' . $start . ', Length: ' . $length);
        
        // Handle search parameter
        $searchArray = $this->request->getGet('search') ?? $this->request->getPost('search') ?? [];
        $searchValue = isset($searchArray['value']) ? $searchArray['value'] : '';
        
        // Handle order parameter
        $orderArray = $this->request->getGet('order') ?? $this->request->getPost('order') ?? [];
        $orderColumn = isset($orderArray[0]['column']) ? $orderArray[0]['column'] : 0;
        $orderDir = isset($orderArray[0]['dir']) ? $orderArray[0]['dir'] : 'asc';

        try {
            $db = \Config\Database::connect();
            
            // Call stored procedure to get jurnal CA to Escrow data
            $query = "CALL p_get_jurnal_ca_to_escrow(?)";
            $result = $db->query($query, [$tanggalData]);
            
            if (!$result) {
                throw new \Exception('Failed to execute p_get_jurnal_ca_to_escrow procedure');
            }
            
            $rawData = $result->getResultArray();
            
            // Process and group data by r_KD_SETTLE to create parent-child structure
            $processedData = $this->processJurnalData($rawData);
            
            // Apply search filter if provided
            $filteredData = $processedData;
            if (!empty($searchValue)) {
                $filteredData = array_filter($processedData, function($row) use ($searchValue) {
                    $searchLower = strtolower($searchValue);
                    
                    // Search in parent row data
                    $parentMatch = strpos(strtolower($row['r_KD_SETTLE'] ?? ''), $searchLower) !== false ||
                                  strpos(strtolower($row['r_NAMA_PRODUK'] ?? ''), $searchLower) !== false;
                    
                    // Search in child rows data
                    $childMatch = false;
                    if (isset($row['child_rows'])) {
                        foreach ($row['child_rows'] as $child) {
                            if (strpos(strtolower($child['d_NO_REF'] ?? ''), $searchLower) !== false ||
                                strpos(strtolower($child['d_DEBIT_ACCOUNT'] ?? ''), $searchLower) !== false ||
                                strpos(strtolower($child['d_CREDIT_ACCOUNT'] ?? ''), $searchLower) !== false ||
                                strpos(strtolower($child['d_CODE_RES'] ?? ''), $searchLower) !== false) {
                                $childMatch = true;
                                break;
                            }
                        }
                    }
                    
                    return $parentMatch || $childMatch;
                });
                $filteredData = array_values($filteredData); // Reset array keys
            }
            
            // Apply sorting on parent level data
            if ($orderColumn > 0 && $orderColumn <= 6) {
                $sortColumns = [
                    1 => 'r_KD_SETTLE',
                    2 => 'r_NAMA_PRODUK', 
                    3 => 'r_AMOUNT_ESCROW',
                    4 => 'r_TOTAL_JURNAL',
                    5 => 'r_JURNAL_PENDING',
                    6 => 'r_JURNAL_SUKSES'
                ];
                
                if (isset($sortColumns[$orderColumn])) {
                    $sortKey = $sortColumns[$orderColumn];
                    usort($filteredData, function($a, $b) use ($sortKey, $orderDir) {
                        $valA = $a[$sortKey] ?? '';
                        $valB = $b[$sortKey] ?? '';
                        
                        if (is_numeric($valA) && is_numeric($valB)) {
                            $result = $valA <=> $valB;
                        } else {
                            $result = strcasecmp($valA, $valB);
                        }
                        
                        return $orderDir === 'desc' ? -$result : $result;
                    });
                }
            }
            
            $totalRecords = count($processedData);
            $filteredRecords = count($filteredData);
            
            // Debug log untuk troubleshooting pagination
            log_message('info', 'Pagination Debug - Total Parent Rows: ' . $totalRecords . ', Filtered: ' . $filteredRecords . ', Start: ' . $start . ', Length: ' . $length);
            
            // Apply pagination pada parent level saja
            $pagedData = array_slice($filteredData, $start, $length);
            
            // Format data for DataTables - kirim parent dan child rows tapi recordsTotal tetap parent saja
            $formattedData = [];
            foreach ($pagedData as $parentRow) {
                
                // Check if already processed untuk disable button
                $isProcessed = $this->isAlreadyProcessed($parentRow['r_KD_SETTLE']);
                
                // Add parent row
                $formattedParent = [
                    'r_KD_SETTLE' => $parentRow['r_KD_SETTLE'] ?? '',
                    'r_NAMA_PRODUK' => $parentRow['r_NAMA_PRODUK'] ?? '',
                    'r_AMOUNT_ESCROW' => $parentRow['r_AMOUNT_ESCROW'] ?? '0',
                    'r_TOTAL_JURNAL' => $parentRow['r_TOTAL_JURNAL'] ?? '0',
                    'r_JURNAL_PENDING' => $parentRow['r_JURNAL_PENDING'] ?? '0',
                    'r_JURNAL_SUKSES' => $parentRow['r_JURNAL_SUKSES'] ?? '0',
                    'child_count' => count($parentRow['child_rows']),
                    'is_parent' => true,
                    'has_children' => count($parentRow['child_rows']) > 0,
                    'is_processed' => $isProcessed, // Flag untuk disable button
                    'd_NO_REF' => '',
                    'd_DEBIT_ACCOUNT' => '',
                    'd_DEBIT_NAME' => '',
                    'd_CREDIT_ACCOUNT' => '',
                    'd_CREDIT_NAME' => '',
                    'd_AMOUNT' => '',
                    'd_CODE_RES' => '',
                    'd_CORE_REF' => '',
                    'd_CORE_DATETIME' => '',
                ];
                
                $formattedData[] = $formattedParent;
                
                // Add child rows untuk dikirim ke frontend (untuk disimpan di childDataMap)
                foreach ($parentRow['child_rows'] as $childRow) {
                    $formattedChild = [
                        'r_KD_SETTLE' => '',
                        'r_NAMA_PRODUK' => '',
                        'r_AMOUNT_ESCROW' => '',
                        'r_TOTAL_JURNAL' => '',
                        'r_JURNAL_PENDING' => '',
                        'r_JURNAL_SUKSES' => '',
                        'child_count' => 0,
                        'is_parent' => false,
                        'has_children' => false,
                        'parent_kd_settle' => $parentRow['r_KD_SETTLE'],
                        'd_NO_REF' => $childRow['d_NO_REF'] ?? '',
                        'd_DEBIT_ACCOUNT' => $childRow['d_DEBIT_ACCOUNT'] ?? '',
                        'd_DEBIT_NAME' => $childRow['d_DEBIT_NAME'] ?? '',
                        'd_CREDIT_ACCOUNT' => $childRow['d_CREDIT_ACCOUNT'] ?? '',
                        'd_CREDIT_NAME' => $childRow['d_CREDIT_NAME'] ?? '',
                        'd_AMOUNT' => $childRow['d_AMOUNT'] ?? '0',
                        'd_CODE_RES' => $childRow['d_CODE_RES'] ?? '',
                        'd_CORE_REF' => $childRow['d_CORE_REF'] ?? '',
                        'd_CORE_DATETIME' => $childRow['d_CORE_DATETIME'] ?? '',
                    ];
                    
                    $formattedData[] = $formattedChild;
                }
            }
            
            return $this->response->setJSON([
                'draw' => intval($draw),
                'recordsTotal' => intval($totalRecords), // Total parent rows tanpa filter
                'recordsFiltered' => intval($filteredRecords), // Parent rows setelah filter
                'data' => $formattedData, // Parent + child data
                'debug' => [
                    'rawDataCount' => count($rawData),
                    'processedParentCount' => $totalRecords,
                    'filteredParentCount' => $filteredRecords,
                    'pagedParentCount' => count($pagedData),
                    'formattedDataCount' => count($formattedData),
                    'pagination' => [
                        'start' => $start,
                        'length' => $length,
                        'currentPage' => floor($start / $length) + 1,
                        'totalPages' => ceil($filteredRecords / $length)
                    ]
                ],
                'csrf_token' => csrf_hash()
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in Jurnal CA to Escrow DataTable: ' . $e->getMessage());
            return $this->response->setJSON([
                'draw' => intval($draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage(),
                'csrf_token' => csrf_hash()
            ]);
        }
    }

    /**
     * Process raw data from procedure to create parent-child structure
     * Group rows by r_KD_SETTLE and create child rows for d_ data
     */
    private function processJurnalData(array $rawData): array
    {
        $grouped = [];
        
        foreach ($rawData as $row) {
            $kdSettle = $row['r_KD_SETTLE'] ?? '';
            
            // Create parent row if not exists
            if (!isset($grouped[$kdSettle])) {
                $grouped[$kdSettle] = [
                    'r_KD_SETTLE' => $row['r_KD_SETTLE'] ?? '',
                    'r_NAMA_PRODUK' => $row['r_NAMA_PRODUK'] ?? '',
                    'r_AMOUNT_ESCROW' => $row['r_AMOUNT_ESCROW'] ?? '0',
                    'r_TOTAL_JURNAL' => $row['r_TOTAL_JURNAL'] ?? '0',
                    'r_JURNAL_PENDING' => $row['r_JURNAL_PENDING'] ?? '0',
                    'r_JURNAL_SUKSES' => $row['r_JURNAL_SUKSES'] ?? '0',
                    'child_rows' => []
                ];
            }
            
            // Add child row data (d_ fields)
            if (!empty($row['d_NO_REF'])) {
                $childRow = [
                    'd_NO_REF' => $row['d_NO_REF'] ?? '',
                    'd_DEBIT_ACCOUNT' => $row['d_DEBIT_ACCOUNT'] ?? '',
                    'd_DEBIT_NAME' => $row['d_DEBIT_NAME'] ?? '',
                    'd_CREDIT_ACCOUNT' => $row['d_CREDIT_ACCOUNT'] ?? '',
                    'd_CREDIT_NAME' => $row['d_CREDIT_NAME'] ?? '',
                    'd_AMOUNT' => $row['d_AMOUNT'] ?? '0',
                    'd_CODE_RES' => $row['d_CODE_RES'] ?? '',
                    'd_CORE_REF' => $row['d_CORE_REF'] ?? '',
                    'd_CORE_DATETIME' => $row['d_CORE_DATETIME'] ?? '',
                ];
                
                $grouped[$kdSettle]['child_rows'][] = $childRow;
            }
        }
        
        // Convert to indexed array
        return array_values($grouped);
    }

    /**
     * Proses jurnal CA to Escrow menggunakan API Gateway batch processing
     */
    public function proses()
    {
        try {
            // Validasi CSRF token
            if (!$this->validate(['csrf_test_name' => 'required'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Token CSRF tidak valid'
                ])->setStatusCode(403);
            }

            // Ambil data dari request
            $kdSettle = $this->request->getPost('kd_settle');
            $tanggalData = $this->request->getPost('tanggal') ?? $this->prosesModel->getDefaultDate();

            // Cek apakah kd_settle sudah pernah diproses (prevent duplicate)
            $duplicateCheck = $this->checkDuplicateProcess($kdSettle);
            
            if ($duplicateCheck['exists']) {
                log_message('warning', "Duplicate process attempt for kd_settle: {$kdSettle}, previous request_id: {$duplicateCheck['request_id']}");
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Kode settle ' . $kdSettle . ' sudah pernah diproses sebelumnya',
                    'error_code' => 'DUPLICATE_PROCESS',
                    'previous_data' => [
                        'status_code_res' => $duplicateCheck['status_code_res'],
                        'is_success' => $duplicateCheck['is_success'],
                        'request_id' => $duplicateCheck['request_id'],
                        'sent_by' => $duplicateCheck['sent_by'],
                        'sent_at' => $duplicateCheck['sent_at']
                    ],
                    'csrf_token' => csrf_hash()
                ]);
            }

            // Ambil data transaksi dari database
            $transaksiData = $this->getTransaksiByKdSettle($kdSettle, $tanggalData);

            // Process transaksi menggunakan service (validasi, format, dan send semua di service)
            $result = $this->akselGatewayService->processBatchTransaction($kdSettle, $transaksiData);
            
            // Handle result
            if (!$result['success']) {
                log_message('error', 'Batch transaction failed for kd_settle: ' . $kdSettle . ', error: ' . $result['message']);
                return $this->response->setJSON(array_merge($result, ['csrf_token' => csrf_hash()]));
            }
            
            // Success - Insert log ke database
            try {
                $this->updateTransaksiStatus(
                    $kdSettle,
                    $result['request_id'],
                    (string)$result['status_code'],
                    $result['total_transaksi'],
                    $result
                );
            } catch (\Exception $e) {
                log_message('error', 'Failed to insert process log but transaction sent: ' . $e->getMessage());
                // Lanjutkan karena transaksi sudah terkirim ke API Gateway
            }
            
            log_message('info', 'Batch transaction successful for kd_settle: ' . $kdSettle . ', request_id: ' . $result['request_id'] . ', total: ' . $result['total_transaksi']);
            
            return $this->response->setJSON(array_merge($result, ['csrf_token' => csrf_hash()]));
            
        } catch (\Exception $e) {
            log_message('error', 'JurnalCaEscrowController::proses() error: ' . $e->getMessage(), [
                'kd_settle' => $this->request->getPost('kd_settle'),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
                'csrf_token' => csrf_hash()
            ])->setStatusCode(500);
        }
    }

    /**
     * Ambil data transaksi berdasarkan kode settle
     */
    private function getTransaksiByKdSettle($kdSettle, $tanggalData)
    {
        try {
            $db = \Config\Database::connect();
            
            // Query untuk ambil detail transaksi per kode settle
            $query = "CALL p_get_jurnal_ca_to_escrow(?)";
            $result = $db->query($query, [$tanggalData]);
            
            if (!$result) {
                throw new \Exception('Failed to get transaction data');
            }
            
            $allData = $result->getResultArray();
            
            // Filter data hanya untuk kode settle yang diminta dan yang belum sukses
            $filteredData = [];
            foreach ($allData as $row) {
                if ($row['r_KD_SETTLE'] === $kdSettle && 
                    !empty($row['d_NO_REF']) && 
                    (!isset($row['d_CODE_RES']) || !str_starts_with($row['d_CODE_RES'], '00'))) {
                    $filteredData[] = $row;
                }
            }
            
            return $filteredData;
            
        } catch (\Exception $e) {
            log_message('error', 'Error getting transaction data: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Insert status transaksi ke database
     * Simpan log pemrosesan untuk tracking
     */
    private function updateTransaksiStatus($kdSettle, $requestId, $statusCodeRes, $totalTransaksi = 0, $apiResponse = null)
    {
        try {
            $sentBy = session('username') ?? 'system';
            
            // Tentukan is_success berdasarkan status code response
            $isSuccess = 0;
            if ($statusCodeRes === '200' || $statusCodeRes === '201' || $statusCodeRes === '00') {
                $isSuccess = 1;
            }
            
            // Prepare data to insert
            $insertData = [
                'kd_settle' => $kdSettle,
                'request_id' => $requestId,
                'status_code_res' => $statusCodeRes,
                'is_success' => $isSuccess,
                'total_transaksi' => $totalTransaksi,
                'sent_by' => $sentBy,
                'sent_at' => date('Y-m-d H:i:s'),
            ];
            
            // Add api_response if provided
            if ($apiResponse !== null) {
                $insertData['api_response'] = json_encode($apiResponse, JSON_PRETTY_PRINT);
            }
            
            $this->processLogModel->insert($insertData);
            
            log_message('info', "Process log created - kd_settle: {$kdSettle}, request_id: {$requestId}, status_code: {$statusCodeRes}, is_success: {$isSuccess}, total: {$totalTransaksi}, user: {$sentBy}");
            
        } catch (\Exception $e) {
            log_message('error', 'Error inserting transaction status: ' . $e->getMessage());
            throw $e; // Re-throw untuk di-handle di level atas
        }
    }

    /**
     * Cek apakah kd_settle sudah pernah dikirim ke API Gateway
     * Untuk prevent duplicate submission
     */
    private function checkDuplicateProcess($kdSettle): array
    {
        try {
            $lastProcess = $this->processLogModel->getLastProcess($kdSettle);
            
            if ($lastProcess) {
                return [
                    'exists' => true,
                    'status_code_res' => $lastProcess['status_code_res'],
                    'is_success' => $lastProcess['is_success'],
                    'request_id' => $lastProcess['request_id'],
                    'sent_by' => $lastProcess['sent_by'],
                    'sent_at' => $lastProcess['sent_at']
                ];
            }
            
            return ['exists' => false];
            
        } catch (\Exception $e) {
            log_message('error', 'Error checking duplicate process: ' . $e->getMessage());
            return ['exists' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Cek apakah kd_settle sudah pernah diproses (untuk disable button)
     */
    private function isAlreadyProcessed($kdSettle): bool
    {
        try {
            return $this->processLogModel->isProcessed($kdSettle);
        } catch (\Exception $e) {
            log_message('error', 'Error checking process status: ' . $e->getMessage());
            return false;
        }
    }
}
