<?php

namespace App\Controllers\Settlement;

use App\Controllers\BaseController;
use App\Models\ProsesModel;
class JurnalEscrowBillerPlController extends BaseController
{
    protected $prosesModel;

    public function __construct()
    {
        $this->prosesModel = new ProsesModel();
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

        return $this->render('settlement/jurnal_escrow_biller_pl.blade.php', $data);
    }

    /**
     * DataTables AJAX endpoint for jurnal Escrow to Biller PL
     */
    public function datatable()
    {
        // Get parameters from both GET and POST to handle DataTables requests
        $tanggalData = $this->request->getGet('tanggal') ?? $this->request->getPost('tanggal') ?? $this->prosesModel->getDefaultDate();
        
        // Debug log
        log_message('info', 'Jurnal Escrow to Biller PL DataTable parameters - Tanggal: ' . $tanggalData);
        
        // DataTables parameters
        $draw = $this->request->getGet('draw') ?? $this->request->getPost('draw') ?? 1;
        $start = $this->request->getGet('start') ?? $this->request->getPost('start') ?? 0;
        $length = $this->request->getGet('length') ?? $this->request->getPost('length') ?? 15;
        
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
            
            // Call stored procedure to get jurnal Escrow to Biller PL data
            $query = "CALL p_get_jurnal_escrow_to_biller_pl(?)";
            $result = $db->query($query, [$tanggalData]);
            
            if (!$result) {
                throw new \Exception('Failed to execute p_get_jurnal_escrow_to_biller_pl procedure');
            }
            
            $rawData = $result->getResultArray();
            
            // Process and group data by r_KD_SETTLE to create parent-child structure
            $processedData = $this->processEscrowBillerData($rawData);
            
            // Apply search filter if provided
            $filteredData = $processedData;
            if (!empty($searchValue)) {
                $filteredData = array_filter($processedData, function($row) use ($searchValue) {
                    $searchLower = strtolower($searchValue);
                    
                    // Search in parent row data - HANYA kode settle dan nama produk
                    $parentMatch = strpos(strtolower($row['r_KD_SETTLE'] ?? ''), $searchLower) !== false ||
                                  strpos(strtolower($row['r_NAMA_PRODUK'] ?? ''), $searchLower) !== false;
                    
                    // Search in child rows data
                    $childMatch = false;
                    if (isset($row['child_rows'])) {
                        foreach ($row['child_rows'] as $child) {
                            if (strpos(strtolower($child['d_STATUS_KR_ESCROW'] ?? ''), $searchLower) !== false ||
                                strpos(strtolower($child['d_NO_REF'] ?? ''), $searchLower) !== false ||
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
                    3 => 'd_STATUS_KR_ESCROW',
                    4 => 'r_TOTAL_AMOUNT',
                    5 => 'r_TOTAL_JURNAL',
                    6 => 'r_JURNAL_STATUS'
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
            
            // Format data for DataTables - kirim parent dan child rows sesuai database
            $formattedData = [];
            foreach ($pagedData as $parentRow) {
                
                // Add parent row - HANYA kode settle dan nama produk
                $formattedParent = [
                    'r_KD_SETTLE' => $parentRow['r_KD_SETTLE'] ?? '',
                    'r_NAMA_PRODUK' => $parentRow['r_NAMA_PRODUK'] ?? '',
                    'child_count' => count($parentRow['child_rows']),
                    'is_parent' => true,
                    'has_children' => count($parentRow['child_rows']) > 0,
                    // Child fields kosong untuk parent
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
                
                // Add child rows untuk dikirim ke frontend (untuk disimpan di childDataMap)
                foreach ($parentRow['child_rows'] as $childRow) {
                    $formattedChild = [
                        'r_KD_SETTLE' => '',
                        'r_NAMA_PRODUK' => '',
                        'child_count' => 0,
                        'is_parent' => false,
                        'has_children' => false,
                        'parent_kd_settle' => $parentRow['r_KD_SETTLE'],
                        // Child data sesuai database
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
            log_message('error', 'Error in Jurnal Escrow to Biller PL DataTable: ' . $e->getMessage());
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
     * Group rows by r_KD_SETTLE and create child rows for d_ data for Escrow to Biller PL
     */
    private function processEscrowBillerData(array $rawData): array
    {
        $grouped = [];
        
        foreach ($rawData as $row) {
            $kdSettle = $row['r_KD_SETTLE'] ?? '';
            
            // Create parent row if not exists - HANYA r_KD_SETTLE dan r_NAMA_PRODUK (sesuai database)
            if (!isset($grouped[$kdSettle])) {
                $grouped[$kdSettle] = [
                    'r_KD_SETTLE' => $row['r_KD_SETTLE'] ?? '',
                    'r_NAMA_PRODUK' => $row['r_NAMA_PRODUK'] ?? '',
                    'child_rows' => []
                ];
            }
            
            // Add child row data (d_ fields) - sesuai database, hanya jika ada d_NO_REF
            if (!empty($row['d_NO_REF'])) {
                $childRow = [
                    'd_STATUS_KR_ESCROW' => $row['d_STATUS_KR_ESCROW'] ?? '',
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
        
        // Convert to indexed array - TIDAK ADA KALKULASI TAMBAHAN
        return array_values($grouped);
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

            // For now, return a basic status response
            // This can be enhanced later when process functionality is implemented
            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'kd_settle' => $kdSettle,
                    'no_ref' => $noRef,
                    'status' => 'Monitoring ready',
                    'message' => 'Status monitoring untuk Escrow to Biller PL'
                ],
                'csrf_token' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'JurnalEscrowBillerPlController::status() error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal mengambil status transaksi',
                'csrf_token' => csrf_hash()
            ]);
        }
    }
}
