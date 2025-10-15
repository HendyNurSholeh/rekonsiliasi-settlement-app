<?php

namespace App\Controllers\Settlement;

use App\Controllers\BaseController;
use App\Libraries\EventLogEnum;
use App\Libraries\LogEnum;
use App\Models\ProsesModel;
use App\Models\ApiGateway\AkselgateTransactionLog;
use App\Models\Settlement\SettlementMessageModel;
use App\Services\ApiGateway\AkselgateService;
use App\Services\ParentChildDataTableService;
use App\Traits\HasLogActivity;

class JurnalEscrowBillerPlController extends BaseController
{
    use HasLogActivity;
    
    protected $prosesModel;
    protected $akselgateService;
    protected $settlementMessageModel;
    protected $akselgateLogModel;
    protected $dataTableService;

    public function __construct()
    {
        $this->prosesModel = new ProsesModel();
        $this->akselgateService = new AkselgateService();
        $this->settlementMessageModel = new SettlementMessageModel();
        $this->akselgateLogModel = new AkselgateTransactionLog();
        $this->dataTableService = new ParentChildDataTableService();
    }

    /**
     * Jurnal Escrow to Biller PL
     * Menampilkan data jurnal Escrow to Biller PL menggunakan stored procedure p_get_jurnal_escrow_to_biller_pl
     */
    public function index()
    {
        $tanggalData = $this->request->getGet('tanggal') ?? $this->prosesModel->getDefaultDate();

        $data = [
            'title' => 'Jurnal Escrow to Biller PL',
            'tanggalData' => $tanggalData,
            'route' => 'settlement/jurnal-escrow-biller-pl'
        ];

        $this->logActivity([
			'log_name' => LogEnum::VIEW,
			'description' => session('username') . ' mengakses Halaman ' . $data['title'],
			'event' => EventLogEnum::VERIFIED,
			'subject' => '-',
		]);

        return $this->render('settlement/jurnal_escrow_biller_pl/index.blade.php', $data);
    }

    /**
     * DataTables AJAX endpoint for jurnal Escrow to Biller PL
     */
    public function datatable()
    {
        // Get tanggal parameter
        $tanggalData = $this->request->getGet('tanggal') ?? $this->request->getPost('tanggal') ?? $this->prosesModel->getDefaultDate();
        
        // Debug log
        log_message('info', 'Jurnal Escrow to Biller PL DataTable parameters - Tanggal: ' . $tanggalData);

        try {
            $db = \Config\Database::connect();
            
            // Call stored procedure to get jurnal Escrow to Biller PL data
            $query = "CALL p_get_jurnal_escrow_to_biller_pl(?)";
            $result = $db->query($query, [$tanggalData]);
            
            if (!$result) {
                throw new \Exception('Failed to execute p_get_jurnal_escrow_to_biller_pl procedure');
            }
            
            $rawData = $result->getResultArray();
            
            // Process and group data by r_KD_SETTLE to create parent-child structure
            $processedData = $this->processEscrowBillerData($rawData);
            
            // Prepare DataTables request parameters
            $dtRequest = [
                'draw' => $this->request->getGet('draw') ?? $this->request->getPost('draw') ?? 1,
                'start' => $this->request->getGet('start') ?? $this->request->getPost('start') ?? 0,
                'length' => $this->request->getGet('length') ?? $this->request->getPost('length') ?? 15,
                'search' => $this->request->getGet('search') ?? $this->request->getPost('search') ?? ['value' => ''],
                'order' => $this->request->getGet('order') ?? $this->request->getPost('order') ?? [['column' => 0, 'dir' => 'asc']],
                'columns' => $this->request->getGet('columns') ?? $this->request->getPost('columns') ?? []
            ];
            
            // Define searchable fields (bisa search di parent dan child)
            $searchFields = ['r_KD_SETTLE', 'r_NAMA_PRODUK'];
            
            // Use service to handle filtering, sorting, and pagination
            $dtResponse = $this->dataTableService->handleRequest($dtRequest, $processedData, $searchFields);
            
            // Format data dengan parent-child structure
            $formattedData = [];
            foreach ($dtResponse['data'] as $parentRow) {
                
                // Check if already processed untuk disable button
                $processStatus = $this->akselgateService->isAlreadyProcessed(
                    $parentRow['r_KD_SETTLE'], 
                    AkselgateTransactionLog::TYPE_ESCROW_BILLER_PL
                );
                
                // Determine button state (sama seperti JurnalCaEscrow)
                $isProcessed = $processStatus['processed'] ?? false;
                $isSuccess = $processStatus['is_success'] ?? null;
                $attemptNumber = $processStatus['attempt_number'] ?? 0;
                $responseMessage = $processStatus['response_message'] ?? '';
                
                // Add parent row
                $formattedParent = [
                    'r_KD_SETTLE' => $parentRow['r_KD_SETTLE'] ?? '',
                    'r_NAMA_PRODUK' => $parentRow['r_NAMA_PRODUK'] ?? '',
                    'r_TOTAL_JURNAL' => $parentRow['r_TOTAL_JURNAL'] ?? '0',
                    'r_JURNAL_PENDING' => $parentRow['r_JURNAL_PENDING'] ?? '0',
                    'r_JURNAL_SUKSES' => $parentRow['r_JURNAL_SUKSES'] ?? '0',
                    'child_count' => count($parentRow['child_rows']),
                    'is_parent' => true,
                    'has_children' => count($parentRow['child_rows']) > 0,
                    'is_processed' => $isProcessed,
                    'is_success' => $isSuccess,
                    'attempt_number' => $attemptNumber,
                    'response_message' => $responseMessage,
                    'd_STATUS_KR_ESCROW' => '',
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
                
                // Add child rows
                foreach ($parentRow['child_rows'] as $childRow) {
                    $formattedChild = [
                        'r_KD_SETTLE' => '',
                        'r_NAMA_PRODUK' => '',
                        'r_TOTAL_JURNAL' => '',
                        'r_JURNAL_PENDING' => '',
                        'r_JURNAL_SUKSES' => '',
                        'child_count' => 0,
                        'is_parent' => false,
                        'has_children' => false,
                        'parent_kd_settle' => $parentRow['r_KD_SETTLE'],
                        'd_STATUS_KR_ESCROW' => $childRow['d_STATUS_KR_ESCROW'] ?? '',
                        'd_NO_REF' => $childRow['d_NO_REF'] ?? '',
                        'd_DEBIT_ACCOUNT' => $childRow['d_DEBIT_ACCOUNT'] ?? '',
                        'd_DEBIT_NAME' => $childRow['d_DEBIT_NAME'] ?? '',
                        'd_CREDIT_ACCOUNT' => $childRow['d_CREDIT_ACCOUNT'] ?? '',
                        'd_CREDIT_NAME' => $childRow['d_CREDIT_NAME'] ?? '',
                        'd_AMOUNT' => $childRow['d_AMOUNT'] ?? '0',
                        'd_CODE_RES' => $childRow['d_CODE_RES'] ?? '',
                        'd_CORE_REF' => $childRow['d_CORE_REF'] ?? '',
                        'd_CORE_DATETIME' => $childRow['d_CORE_DATETIME'] ?? '',
                        'd_ERROR_MESSAGE' => $childRow['d_ERROR_MESSAGE'] ?? '',
                    ];
                    
                    $formattedData[] = $formattedChild;
                }
            }
            
            // Return DataTables response with CSRF token
            $dtResponse['data'] = $formattedData;
            $dtResponse['csrf_token'] = csrf_hash();
            
            return $this->response->setJSON($dtResponse);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in Jurnal Escrow to Biller PL DataTable: ' . $e->getMessage());
            return $this->response->setJSON([
                'draw' => intval($dtRequest['draw'] ?? 1),
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
     * Group rows by r_KD_SETTLE and create child rows for d_ data for Escrow to Biller PL
     */
    private function processEscrowBillerData(array $rawData): array
    {
        // Get error messages from transaction log untuk semua kd_settle
        $kdSettleList = array_unique(array_column($rawData, 'r_KD_SETTLE'));
        $errorMessages = $this->dataTableService->getErrorMessages(
            $this->akselgateLogModel,
            $kdSettleList,
            AkselgateTransactionLog::TYPE_ESCROW_BILLER_PL
        );
        
        // Define parent and child fields for Escrow Biller PL
        // Parent sekarang memiliki field untuk total, pending, dan sukses
        $parentFields = [
            'r_TOTAL_JURNAL',
            'r_JURNAL_PENDING',
            'r_JURNAL_SUKSES'
        ];
        
        $childFields = [
            'd_STATUS_KR_ESCROW', // Unique field untuk Escrow Biller PL
            'd_NO_REF',
            'd_DEBIT_ACCOUNT',
            'd_DEBIT_NAME',
            'd_CREDIT_ACCOUNT',
            'd_CREDIT_NAME',
            'd_AMOUNT',
            'd_CODE_RES',
            'd_CORE_REF',
            'd_CORE_DATETIME'
        ];
        
        // Use service to process parent-child data
        return $this->dataTableService->processParentChildData(
            $rawData,
            $parentFields,
            $childFields,
            $errorMessages
        );
    }

    /**
     * Proses jurnal Escrow to Biller PL menggunakan Akselgate batch processing
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
            $duplicateCheck = $this->akselgateService->checkDuplicateProcess($kdSettle, AkselgateTransactionLog::TYPE_ESCROW_BILLER_PL);
            
            if ($duplicateCheck['exists']) {
                log_message('warning', "Duplicate process attempt for kd_settle: {$kdSettle}, previous request_id: {$duplicateCheck['request_id']}");
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Kode settle ' . $kdSettle . ' sudah berhasil diproses sebelumnya pada attempt #' . $duplicateCheck['attempt_number'],
                    'error_code' => 'DUPLICATE_PROCESS',
                    'previous_data' => [
                        'attempt_number' => $duplicateCheck['attempt_number'],
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

            // Process transaksi menggunakan service (validasi, format, send, dan logging semua di service)
            $result = $this->akselgateService->processBatchTransaction(
                $kdSettle, 
                $transaksiData, 
                AkselgateTransactionLog::TYPE_ESCROW_BILLER_PL
            );
            
            // Handle result (logging sudah di-handle oleh service)
            if (!$result['success']) {
                log_message('error', 'Batch transaction failed for kd_settle: ' . $kdSettle . ', attempt: ' . ($result['attempt_number'] ?? 'N/A') . ', error: ' . $result['message']);
                return $this->response->setJSON(array_merge($result, ['csrf_token' => csrf_hash()]));
            }
            
            log_message('info', 'Batch transaction successful for kd_settle: ' . $kdSettle . ', attempt: ' . $result['attempt_number'] . ', request_id: ' . $result['request_id'] . ', total: ' . $result['total_transaksi']);
            
            return $this->response->setJSON(array_merge($result, ['csrf_token' => csrf_hash()]));
            
        } catch (\Exception $e) {
            log_message('error', 'JurnalEscrowBillerPlController::proses() error: ' . $e->getMessage(), [
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
            $query = "CALL p_get_jurnal_escrow_to_biller_pl(?)";
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
}
