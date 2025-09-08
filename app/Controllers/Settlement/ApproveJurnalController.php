<?php

namespace App\Controllers\Settlement;

use App\Controllers\BaseController;
use App\Libraries\EventLogEnum;
use App\Libraries\LogEnum;
use App\Models\ProsesModel;
use App\Traits\HasLogActivity;

class ApproveJurnalController extends BaseController
{
    use HasLogActivity;
    protected $prosesModel;

    public function __construct()
    {
        $this->prosesModel = new ProsesModel();
    }

    /**
     * Approve Jurnal Settlement
     * Menampilkan data dari table t_settle_produk dengan filter tanggal
     */
    public function index()
    {
        $tanggalRekon = $this->request->getGet('tanggal') ?? $this->prosesModel->getDefaultDate();
        $statusApprove = $this->request->getGet('status_approve') ?? '';

        $data = [
            'title' => 'Approve Jurnal Settlement',
            'tanggalRekon' => $tanggalRekon,
            'statusApprove' => $statusApprove,
            'route' => 'settlement/approve-jurnal'
        ];

        $this->logActivity([
			'log_name' => LogEnum::VIEW,
			'description' => session('username') . ' mengakses Halaman ' . $data['title'],
			'event' => EventLogEnum::VERIFIED,
			'subject' => '-',
		]);

        return $this->render('settlement/approve_jurnal/index.blade.php', $data);
    }

    /**
     * DataTables AJAX endpoint for approve jurnal settlement
     */
    public function datatable()
    {
        // Get parameters from both GET and POST to handle DataTables requests
        $tanggalRekon = $this->request->getGet('tanggal') ?? $this->request->getPost('tanggal') ?? $this->prosesModel->getDefaultDate();
        $statusApprove = $this->request->getGet('status_approve') ?? $this->request->getPost('status_approve') ?? '';
        
        // Debug log
        log_message('info', 'Approve Jurnal DataTable parameters - Tanggal: ' . $tanggalRekon . ', Status Approve: ' . $statusApprove);
        
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

        // Column mapping
        $columns = [
            0 => 'id', // For row number
            1 => 'TGL_DATA',
            2 => 'NAMA_PRODUK',
            3 => 'KD_SETTLE',
            4 => 'STAT_APPROVER',
            5 => 'USER_APPROVER',
            6 => 'TGL_APPROVER',
            7 => 'id' // For action column
        ];

        try {
            $db = \Config\Database::connect();
            
            $baseQuery = "
                SELECT id, KD_SETTLE, NAMA_PRODUK, TGL_DATA, TOT_JURNAL_KR_ECR, 
                       STAT_APPROVER, USER_APPROVER, TGL_APPROVER
                FROM t_settle_produk 
                WHERE DATE(TGL_DATA) = ?
            ";
            
            // Add filters
            $queryParams = [$tanggalRekon];
            
            if ($statusApprove !== '') {
                if ($statusApprove === 'pending') {
                    $baseQuery .= " AND (STAT_APPROVER IS NULL OR STAT_APPROVER = '')";
                } else {
                    $baseQuery .= " AND STAT_APPROVER = ?";
                    $queryParams[] = $statusApprove;
                }
                log_message('info', 'Adding status_approve filter: ' . $statusApprove);
            }
            
            // Add search conditions
            if (!empty($searchValue)) {
                $baseQuery .= " AND (
                    KD_SETTLE LIKE ? OR 
                    NAMA_PRODUK LIKE ? OR 
                    CAST(TOT_JURNAL_KR_ECR AS CHAR) LIKE ? OR
                    USER_APPROVER LIKE ?
                )";
                $searchTerm = "%{$searchValue}%";
                $queryParams = array_merge($queryParams, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }
            
            $totalQuery = "
                SELECT COUNT(id) as total 
                FROM t_settle_produk 
                WHERE DATE(TGL_DATA) = ?
            ";
            $totalParams = [$tanggalRekon];
            if ($statusApprove !== '') {
                if ($statusApprove === 'pending') {
                    $totalQuery .= " AND (STAT_APPROVER IS NULL OR STAT_APPROVER = '')";
                } else {
                    $totalQuery .= " AND STAT_APPROVER = ?";
                    $totalParams[] = $statusApprove;
                }
            }
            
            $totalResult = $db->query($totalQuery, $totalParams);
            $totalRecords = $totalResult->getRow()->total;
            
            // Count filtered records
            $filteredRecords = $totalRecords;
            if (!empty($searchValue)) {
                $filteredQuery = str_replace('COUNT(id)', 'COUNT(id)', $totalQuery) . " AND (
                    KD_SETTLE LIKE ? OR 
                    NAMA_PRODUK LIKE ? OR 
                    CAST(TOT_JURNAL_KR_ECR AS CHAR) LIKE ? OR
                    USER_APPROVER LIKE ?
                )";
                $searchTerm = "%{$searchValue}%";
                $filteredParams = array_merge($totalParams, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
                $filteredResult = $db->query($filteredQuery, $filteredParams);
                $filteredRecords = $filteredResult->getRow()->total;
            }
            
            // Add ordering with consistent sorting
            if (isset($columns[$orderColumn]) && $orderColumn > 0 && $orderColumn < 7) {
                $orderColumnName = $columns[$orderColumn];
                // Always add id as secondary sort for consistent pagination
                $baseQuery .= " ORDER BY {$orderColumnName} {$orderDir}, id DESC";
            } else {
                // Default ordering with id to ensure consistent pagination
                $baseQuery .= " ORDER BY TGL_DATA DESC, id DESC";
            }
            
            // Add pagination
            $baseQuery .= " LIMIT {$length} OFFSET {$start}";
            
            // Log the final query
            log_message('info', 'Final approve jurnal query: ' . $baseQuery);
            log_message('info', 'Query parameters: ' . json_encode($queryParams));
            log_message('info', 'Pagination - Start: ' . $start . ', Length: ' . $length);
            log_message('info', 'Total Records: ' . $totalRecords . ', Filtered Records: ' . $filteredRecords);
            
            // Execute query
            $result = $db->query($baseQuery, $queryParams);
            $data = $result->getResultArray();

            log_message('info', 'Data fetched count: ' . count($data));
            // Only log first few records to avoid spam
            if (count($data) > 0) {
                log_message('info', 'First record: ' . json_encode($data[0]));
                if (count($data) > 1) {
                    log_message('info', 'Last record: ' . json_encode($data[count($data) - 1]));
                }
            }
            
            // Format data for DataTables
            $formattedData = [];
            foreach ($data as $row) {
                $formattedData[] = [
                    'id' => $row['id'] ?? '',
                    'KD_SETTLE' => $row['KD_SETTLE'] ?? '',
                    'NAMA_PRODUK' => $row['NAMA_PRODUK'] ?? '',
                    'TGL_DATA' => $row['TGL_DATA'] ?? '',
                    'TOT_JURNAL_KR_ECR' => $row['TOT_JURNAL_KR_ECR'] ?? '0',
                    'STAT_APPROVER' => $row['STAT_APPROVER'] ?? null,
                    'USER_APPROVER' => $row['USER_APPROVER'] ?? '',
                    'TGL_APPROVER' => $row['TGL_APPROVER'] ?? '',
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
            log_message('error', 'Error in Approve Jurnal DataTable: ' . $e->getMessage());
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
     * Get detail jurnal for approval modal
     */
    public function getDetailJurnal()
    {
        $kdSettle = $this->request->getPost('kd_settle');
        
        if (!$kdSettle) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Kode settle tidak ditemukan',
                'csrf_token' => csrf_hash()
            ]);
        }

        try {
            $db = \Config\Database::connect();
            
            // Get settlement product info
            $settleQuery = "
                SELECT NAMA_PRODUK, TGL_DATA, TOT_JURNAL_KR_ECR
                FROM t_settle_produk 
                WHERE KD_SETTLE = ?
            ";
            $settleResult = $db->query($settleQuery, [$kdSettle]);
            $settleInfo = $settleResult->getRowArray();
            
            if (!$settleInfo) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Data settlement tidak ditemukan',
                    'csrf_token' => csrf_hash()
                ]);
            }
            
            // Get detail jurnal from tamp_settle_message
            $detailQuery = "
                SELECT JENIS_SETTLE, IDPARTNER, CORE, DEBIT_ACCOUNT, DEBIT_NAME, 
                       CREDIT_CORE, CREDIT_ACCOUNT, CREDIT_NAME, AMOUNT, DESCRIPTION, REF_NUMBER
                FROM tamp_settle_message 
                WHERE KD_SETTLE = ?
                ORDER BY JENIS_SETTLE, IDPARTNER
            ";
            $detailResult = $db->query($detailQuery, [$kdSettle]);
            $detailData = $detailResult->getResultArray();

            return $this->response->setJSON([
                'success' => true,
                'settle_info' => $settleInfo,
                'detail_data' => $detailData,
                'csrf_token' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error fetching jurnal detail: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil detail jurnal: ' . $e->getMessage(),
                'csrf_token' => csrf_hash()
            ]);
        }
    }

    /**
     * Approve or reject settlement journal
     */
    public function processApproval()
    {
        $kdSettle = $this->request->getPost('kd_settle');
        $tanggalRekon = $this->request->getPost('tanggal_rekon');
        $namaProduk = $this->request->getPost('nama_produk');
        $action = $this->request->getPost('action'); // 'approve' or 'reject'
        $username = session()->get('username') ?? 'SYSTEM';

        if (!$kdSettle || !$tanggalRekon || !$namaProduk || !$action) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Parameter tidak lengkap (kd_settle, tanggal_rekon, nama_produk, action)',
                'csrf_token' => csrf_hash()
            ]);
        }

        $approvalStatus = ($action === 'approve') ? '1' : '0';
        $actionText = ($action === 'approve') ? 'disetujui' : 'ditolak';

        try {
            $db = \Config\Database::connect();
            
            // Call procedure to approve/reject settlement journal with new parameters
            // Parameters: p_kd_settle, p_nama_produk, p_tgl_data, p_user, p_status
            $query = "CALL p_approve_settle_jurnal(?, ?, ?, ?, ?)";
            $result = $db->query($query, [$kdSettle, $namaProduk, $tanggalRekon, $username, $approvalStatus]);
            
            if (!$result) {
                throw new \Exception('Failed to execute p_approve_settle_jurnal procedure');
            }

            // Log successful operation
            log_message('info', "Settlement approval processed - KD_SETTLE: {$kdSettle}, NAMA_PRODUK: {$namaProduk}, ACTION: {$action}, USER: {$username}");

            return $this->response->setJSON([
                'success' => true,
                'message' => "Jurnal settlement berhasil {$actionText}",
                'csrf_token' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error processing approval: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => "Terjadi kesalahan saat {$actionText} jurnal: " . $e->getMessage(),
                'csrf_token' => csrf_hash()
            ]);
        }
    }

    /**
     * Get settlement journal summary for reporting
     */
    public function getSummary()
    {
        $tanggalRekon = $this->request->getGet('tanggal') ?? $this->prosesModel->getDefaultDate();

        try {
            $db = \Config\Database::connect();
            
            $summaryQuery = "
                SELECT 
                    COUNT(*) as total_jurnal,
                    SUM(CASE WHEN STAT_APPROVER = 1 THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN STAT_APPROVER IS NULL THEN 1 ELSE 0 END) as pending,
                    SUM(TOT_JURNAL_KR_ECR) as total_amount,
                    SUM(CASE WHEN STAT_APPROVER = 1 THEN TOT_JURNAL_KR_ECR ELSE 0 END) as approved_amount
                FROM t_settle_produk 
                WHERE DATE(TGL_DATA) = ?
            ";
            
            $result = $db->query($summaryQuery, [$tanggalRekon]);
            $summary = $result->getRowArray();

            return $this->response->setJSON([
                'success' => true,
                'summary' => $summary,
                'csrf_token' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error getting summary: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil ringkasan: ' . $e->getMessage(),
                'csrf_token' => csrf_hash()
            ]);
        }
    }
}
