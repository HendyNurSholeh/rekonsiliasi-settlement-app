<?php

namespace App\Controllers\Settlement;

use App\Controllers\BaseController;
use App\Models\ProsesModel;
use App\Services\Settlement\JurnalCaEscrowService;

class JurnalCaEscrowController extends BaseController
{
    protected $prosesModel;
    protected $jurnalService;

    public function __construct()
    {
        $this->prosesModel = new ProsesModel();
        $this->jurnalService = new JurnalCaEscrowService();
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

        return $this->render('settlement/jurnal_ca_escrow.blade.php', $data);
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
        $length = $this->request->getGet('length') ?? $this->request->getPost('length') ?? 25;
        
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
            
            $allData = $result->getResultArray();
            
            // Apply search filter if provided
            $filteredData = $allData;
            if (!empty($searchValue)) {
                $filteredData = array_filter($allData, function($row) use ($searchValue) {
                    $searchLower = strtolower($searchValue);
                    return strpos(strtolower($row['r_KD_SETTLE'] ?? ''), $searchLower) !== false ||
                           strpos(strtolower($row['r_NAMA_PRODUK'] ?? ''), $searchLower) !== false ||
                           strpos(strtolower($row['d_NO_REF'] ?? ''), $searchLower) !== false ||
                           strpos(strtolower($row['d_DEBIT_ACCOUNT'] ?? ''), $searchLower) !== false ||
                           strpos(strtolower($row['d_CREDIT_ACCOUNT'] ?? ''), $searchLower) !== false ||
                           strpos(strtolower($row['d_CODE_RES'] ?? ''), $searchLower) !== false;
                });
                $filteredData = array_values($filteredData); // Reset array keys
            }
            
            // Apply sorting
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
            
            $totalRecords = count($allData);
            $filteredRecords = count($filteredData);
            
            // Apply pagination
            $pagedData = array_slice($filteredData, $start, $length);
            
            // Format data for DataTables
            $formattedData = [];
            foreach ($pagedData as $row) {
                $formattedData[] = [
                    'r_KD_SETTLE' => $row['r_KD_SETTLE'] ?? '',
                    'r_NAMA_PRODUK' => $row['r_NAMA_PRODUK'] ?? '',
                    'r_AMOUNT_ESCROW' => $row['r_AMOUNT_ESCROW'] ?? '0',
                    'r_TOTAL_JURNAL' => $row['r_TOTAL_JURNAL'] ?? '0',
                    'r_JURNAL_PENDING' => $row['r_JURNAL_PENDING'] ?? '0',
                    'r_JURNAL_SUKSES' => $row['r_JURNAL_SUKSES'] ?? '0',
                    'd_NO_REF' => $row['d_NO_REF'] ?? '',
                    'd_DEBIT_ACCOUNT' => $row['d_DEBIT_ACCOUNT'] ?? '',
                    'd_DEBIT_NAME' => $row['d_DEBIT_NAME'] ?? '',
                    'd_CREDIT_ACCOUNT' => $row['d_CREDIT_ACCOUNT'] ?? '',
                    'd_CREDIT_NAME' => $row['d_CREDIT_ACCOUNT'] ?? '',
                    'd_AMOUNT' => $row['d_AMOUNT'] ?? '0',
                    'd_CODE_RES' => $row['d_CODE_RES'] ?? '',
                    'd_CORE_REF' => $row['d_CORE_REF'] ?? '',
                    'd_CORE_DATETIME' => $row['d_CORE_DATETIME'] ?? '',
                ];
            }
            
            return $this->response->setJSON([
                'draw' => intval($draw),
                'recordsTotal' => intval($totalRecords),
                'recordsFiltered' => intval($filteredRecords),
                'data' => $formattedData,
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
     * Proses jurnal CA to Escrow menggunakan Service layer
     * Implementasi best practices untuk financial transactions
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

            // Kumpulkan data request untuk service
            $requestData = [
                'kd_settle' => $this->request->getPost('kd_settle'),
                'no_ref' => $this->request->getPost('no_ref'),
                'amount' => $this->request->getPost('amount'),
                'debit_account' => $this->request->getPost('debit_account'),
                'credit_account' => $this->request->getPost('credit_account'),
                'is_reprocess' => $this->request->getPost('is_reprocess', FILTER_VALIDATE_BOOLEAN),
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString()
            ];

            // Proses melalui service layer
            $result = $this->jurnalService->prosesJurnal($requestData);

            // Tambahkan CSRF token baru untuk request berikutnya
            $result['csrf_token'] = csrf_hash();

            return $this->response->setJSON($result);
            
        } catch (\Exception $e) {
            log_message('error', 'JurnalCaEscrowController::proses() error: ' . $e->getMessage(), [
                'request_data' => $this->request->getPost(),
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.',
                'response_code' => '99',
                'timestamp' => date('Y-m-d H:i:s'),
                'csrf_token' => csrf_hash()
            ])->setStatusCode(500);
        }
    }

    /**
     * Get status transaksi untuk monitoring
     */
    public function status()
    {
        try {
            $kdSettle = $this->request->getGet('kd_settle');
            $noRef = $this->request->getGet('no_ref');

            if (empty($kdSettle) || empty($noRef)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Parameter tidak lengkap'
                ]);
            }

            $status = $this->jurnalService->getTransactionStatus($kdSettle, $noRef);

            return $this->response->setJSON([
                'success' => true,
                'data' => $status,
                'csrf_token' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'JurnalCaEscrowController::status() error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal mengambil status transaksi',
                'csrf_token' => csrf_hash()
            ]);
        }
    }
}
