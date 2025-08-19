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
            
            // Call stored procedure to get jurnal Escrow to Biller PL data
            $query = "CALL p_get_jurnal_escrow_to_biller_pl(?)";
            $result = $db->query($query, [$tanggalData]);
            
            if (!$result) {
                throw new \Exception('Failed to execute p_get_jurnal_escrow_to_biller_pl procedure');
            }
            
            $allData = $result->getResultArray();
            
            // Apply search filter if provided
            $filteredData = $allData;
            if (!empty($searchValue)) {
                $filteredData = array_filter($allData, function($row) use ($searchValue) {
                    $searchLower = strtolower($searchValue);
                    return strpos(strtolower($row['r_KD_SETTLE'] ?? ''), $searchLower) !== false ||
                           strpos(strtolower($row['r_NAMA_PRODUK'] ?? ''), $searchLower) !== false ||
                           strpos(strtolower($row['d_STATUS_KR_ESCROW'] ?? ''), $searchLower) !== false ||
                           strpos(strtolower($row['d_NO_REF'] ?? ''), $searchLower) !== false ||
                           strpos(strtolower($row['d_DEBIT_ACCOUNT'] ?? ''), $searchLower) !== false ||
                           strpos(strtolower($row['d_CREDIT_ACCOUNT'] ?? ''), $searchLower) !== false ||
                           strpos(strtolower($row['d_CODE_RES'] ?? ''), $searchLower) !== false;
                });
                $filteredData = array_values($filteredData); // Reset array keys
            }
            
            // Apply sorting
            if ($orderColumn > 0 && $orderColumn <= 3) {
                $sortColumns = [
                    1 => 'r_KD_SETTLE',
                    2 => 'r_NAMA_PRODUK', 
                    3 => 'd_STATUS_KR_ESCROW'
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
                    'd_STATUS_KR_ESCROW' => $row['d_STATUS_KR_ESCROW'] ?? '',
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
}
