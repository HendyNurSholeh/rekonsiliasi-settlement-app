<?php

namespace App\Controllers\Rekon\Process;

use App\Controllers\BaseController;
use App\Models\ProsesModel;

class DetailVsRekapController extends BaseController
{
    protected $prosesModel;

    public function __construct()
    {
        $this->prosesModel = new ProsesModel();
    }

    /**
     * Laporan Detail vs Rekap
     * Menampilkan data dari procedure p_compare_rekap
     */
    public function index()
    {
        $tanggalRekon = $this->request->getGet('tanggal') ?? $this->prosesModel->getDefaultDate();
        $filterSelisih = $this->request->getGet('filter_selisih') ?? '';

        $data = [
            'title' => 'Laporan Detail vs Rekap',
            'tanggalRekon' => $tanggalRekon,
            'filterSelisih' => $filterSelisih,
            'route' => 'rekon/process/detail-vs-rekap'
        ];

        // For statistics, still get data from procedure if date is provided
        if ($tanggalRekon) {
            try {
                $db = \Config\Database::connect();
                $query = $db->query("CALL p_compare_rekap(?)", [$tanggalRekon]);
                $data['compareData'] = $query->getResultArray();
            } catch (\Exception $e) {
                log_message('error', 'Error calling p_compare_rekap: ' . $e->getMessage());
                $data['compareData'] = [];
            }
        } else {
            $data['compareData'] = [];
        }

        return $this->render('rekon/process/detail_vs_rekap.blade.php', $data);
    }

    /**
     * DataTables AJAX endpoint for detail vs rekap
     */
    public function datatable()
    {
        // Get parameters from both GET and POST to handle DataTables requests
        $tanggalRekon = $this->request->getGet('tanggal') ?? $this->request->getPost('tanggal') ?? $this->prosesModel->getDefaultDate();
        $filterSelisih = $this->request->getGet('filter_selisih') ?? $this->request->getPost('filter_selisih') ?? '';
        
        // Debug log
        log_message('info', 'DetailVsRekap DataTable parameters - Tanggal: ' . $tanggalRekon . ', Filter Selisih: ' . $filterSelisih);
        
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
            0 => 'ROW_NUMBER', // For row number
            1 => 'NAMA_GROUP',
            2 => 'FILE_SETTLE', 
            3 => 'AMOUNT_DETAIL',
            4 => 'AMOUNT_REKAP',
            5 => 'SELISIH'
        ];

        try {
            $db = \Config\Database::connect();
            
            // Call the stored procedure to get data
            $procedureQuery = $db->query("CALL p_compare_rekap(?)", [$tanggalRekon]);
            $allData = $procedureQuery->getResultArray();
            
            // Debug log for raw data
            if (count($allData) > 0) {
                log_message('info', 'Raw data from procedure (first 3): ' . json_encode(array_slice($allData, 0, 3)));
            }
            
            // Apply filter if specified
            $filteredData = $allData;
            if ($filterSelisih !== '') {
                log_message('info', 'Applying filter: ' . $filterSelisih);
                $filteredData = array_filter($allData, function($item) use ($filterSelisih) {
                    $selisih = (float)str_replace(',', '', $item['SELISIH'] ?? 0);
                    log_message('info', 'Item SELISIH: ' . ($item['SELISIH'] ?? 'null') . ' -> parsed: ' . $selisih);
                    if ($filterSelisih === 'ada_selisih') {
                        $result = $selisih != 0;
                        log_message('info', 'ada_selisih filter - result: ' . ($result ? 'true' : 'false'));
                        return $result;
                    } else if ($filterSelisih === 'tidak_ada_selisih') {
                        $result = $selisih == 0;
                        log_message('info', 'tidak_ada_selisih filter - result: ' . ($result ? 'true' : 'false'));
                        return $result;
                    }
                    return true;
                });
                $filteredData = array_values($filteredData); // Re-index array
                log_message('info', 'After filter - count: ' . count($filteredData));
            }
            
            // Apply search filter
            if (!empty($searchValue)) {
                $filteredData = array_filter($filteredData, function($item) use ($searchValue) {
                    $searchTerm = strtolower($searchValue);
                    return (
                        strpos(strtolower($item['NAMA_GROUP'] ?? ''), $searchTerm) !== false ||
                        strpos(strtolower((string)($item['AMOUNT_DETAIL'] ?? '')), $searchTerm) !== false ||
                        strpos(strtolower((string)($item['AMOUNT_REKAP'] ?? '')), $searchTerm) !== false ||
                        strpos(strtolower((string)($item['SELISIH'] ?? '')), $searchTerm) !== false
                    );
                });
                $filteredData = array_values($filteredData); // Re-index array
            }
            
            // Handle sorting
            if (isset($columns[$orderColumn]) && $orderColumn > 0 && $orderColumn < 6) {
                $sortColumn = $columns[$orderColumn];
                usort($filteredData, function($a, $b) use ($sortColumn, $orderDir) {
                    if ($sortColumn === 'AMOUNT_DETAIL' || $sortColumn === 'AMOUNT_REKAP' || $sortColumn === 'SELISIH') {
                        $aVal = (float)str_replace(',', '', $a[$sortColumn] ?? 0);
                        $bVal = (float)str_replace(',', '', $b[$sortColumn] ?? 0);
                    } else {
                        $aVal = $a[$sortColumn] ?? '';
                        $bVal = $b[$sortColumn] ?? '';
                    }
                    
                    if ($orderDir === 'desc') {
                        return $bVal <=> $aVal;
                    } else {
                        return $aVal <=> $bVal;
                    }
                });
            }
            
            // Calculate totals
            $totalRecords = count($allData);
            $filteredRecords = count($filteredData);
            
            // Apply pagination
            $pagedData = array_slice($filteredData, $start, $length);
            
            // Format data for DataTables
            $formattedData = [];
            foreach ($pagedData as $row) {
                $formattedData[] = [
                    'NAMA_GROUP' => $row['NAMA_GROUP'] ?? '',
                    'FILE_SETTLE' => $row['FILE_SETTLE'] ?? '0',
                    'AMOUNT_DETAIL' => $row['AMOUNT_DETAIL'] ?? '0',
                    'AMOUNT_REKAP' => $row['AMOUNT_REKAP'] ?? '0',
                    'SELISIH' => $row['SELISIH'] ?? '0'
                ];
            }
            
            // Debug log for first few records
            if (count($formattedData) > 0) {
                log_message('info', 'Sample formatted data: ' . json_encode(array_slice($formattedData, 0, 3)));
            }
            
            log_message('info', 'DetailVsRekap DataTable - Total: ' . $totalRecords . ', Filtered: ' . $filteredRecords . ', Returned: ' . count($formattedData));
            
            return $this->response->setJSON([
                'draw' => intval($draw),
                'recordsTotal' => intval($totalRecords),
                'recordsFiltered' => intval($filteredRecords),
                'data' => $formattedData,
                'csrf_token' => csrf_hash()
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in detailVsRekapDataTable: ' . $e->getMessage());
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
     * AJAX endpoint for statistics
     */
    public function statistics()
    {
        $tanggalRekon = $this->request->getGet('tanggal') ?? $this->prosesModel->getDefaultDate();
        $filterSelisih = $this->request->getGet('filter_selisih') ?? '';
        
        try {
            $db = \Config\Database::connect();
            
            // Call the stored procedure to get data
            $query = $db->query("CALL p_compare_rekap(?)", [$tanggalRekon]);
            $allData = $query->getResultArray();
            
            // Calculate statistics
            $total = count($allData);
            $adaSelisih = 0;
            $tidakAdaSelisih = 0;
            
            foreach ($allData as $item) {
                $selisih = (float)str_replace(',', '', $item['SELISIH'] ?? 0);
                if ($selisih != 0) {
                    $adaSelisih++;
                } else {
                    $tidakAdaSelisih++;
                }
            }
            
            // Calculate accuracy percentage
            $akurasi = $total > 0 ? round(($tidakAdaSelisih / $total) * 100, 2) : 0;
            
            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'ada_selisih' => $adaSelisih,
                    'tidak_ada_selisih' => $tidakAdaSelisih,
                    'akurasi' => $akurasi
                ],
                'csrf_token' => csrf_hash()
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in statistics: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil statistik: ' . $e->getMessage(),
                'data' => [
                    'total' => 0,
                    'ada_selisih' => 0,
                    'tidak_ada_selisih' => 0,
                    'akurasi' => 0
                ],
                'csrf_token' => csrf_hash()
            ]);
        }
    }
}
