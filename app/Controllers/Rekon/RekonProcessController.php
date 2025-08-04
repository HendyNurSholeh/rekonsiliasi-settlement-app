<?php

namespace App\Controllers\Rekon;

use App\Controllers\BaseController;
use App\Models\ProsesModel;

class RekonProcessController extends BaseController
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
    public function detailVsRekap()
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
     * Rekap Tx Direct Jurnal
     * Menampilkan data dari procedure p_direct_jurnal_rekap
     */
    public function directJurnalRekap()
    {
        $tanggalRekon = $this->request->getGet('tanggal') ?? $this->prosesModel->getDefaultDate();

        $data = [
            'title' => 'Rekap Tx Direct Jurnal',
            'tanggalRekon' => $tanggalRekon,
            'route' => 'rekon/process/direct-jurnal/rekap'
        ];

        // Get data from procedure if date is provided
        if ($tanggalRekon) {
            try {
                $db = \Config\Database::connect();
                $query = $db->query("CALL p_direct_jurnal_rekap(?)", [$tanggalRekon]);
                $data['rekapData'] = $query->getResultArray();
            } catch (\Exception $e) {
                log_message('error', 'Error calling p_direct_jurnal_rekap: ' . $e->getMessage());
                $data['rekapData'] = [];
            }
        } else {
            $data['rekapData'] = [];
        }

        return $this->render('rekon/process/direct_jurnal_rekap.blade.php', $data);
    }

    /**
     * Penyelesaian Dispute
     * Menampilkan data dari v_cek_biller_dispute_direct
     */
    public function disputeResolution()
    {
        $tanggalRekon = $this->request->getGet('tanggal') ?? $this->prosesModel->getDefaultDate();

        $data = [
            'title' => 'Penyelesaian Dispute',
            'tanggalRekon' => $tanggalRekon,
            'route' => 'rekon/process/direct-jurnal/dispute'
        ];

        // Get data from view if date is provided
        if ($tanggalRekon) {
            try {
                $db = \Config\Database::connect();
                $query = $db->query("
                    SELECT IDPARTNER, TERMINALID, v_GROUP_PRODUK AS PRODUK, IDPEL, 
                           RP_BILLER_TAG, STATUS AS STATUS_BILLER, v_STAT_CORE_AGR AS STATUS_CORE, v_ID
                    FROM v_cek_biller_dispute_direct 
                    WHERE v_TGL_FILE_REKON = ?
                ", [$tanggalRekon]);
                $data['disputeData'] = $query->getResultArray();
            } catch (\Exception $e) {
                log_message('error', 'Error fetching dispute data: ' . $e->getMessage());
                $data['disputeData'] = [];
            }
        } else {
            $data['disputeData'] = [];
        }

        return $this->render('rekon/process/dispute_resolution.blade.php', $data);
    }

    /**
     * Get dispute detail for modal
     */
    public function getDisputeDetail()
    {
        $id = $this->request->getPost('id');
        
        if (!$id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID tidak ditemukan',
                'csrf_token' => csrf_hash()
            ]);
        }

        try {
            $db = \Config\Database::connect();
            $query = $db->query("
                SELECT * FROM v_cek_biller_dispute_direct 
                WHERE v_ID = ?
            ", [$id]);
            
            $disputeDetail = $query->getRowArray();
            
            if (!$disputeDetail) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                    'csrf_token' => csrf_hash()
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $disputeDetail,
                'csrf_token' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error fetching dispute detail: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data',
                'csrf_token' => csrf_hash()
            ]);
        }
    }

    /**
     * Update dispute data
     */
    public function updateDispute()
    {
        $id = $this->request->getPost('id');
        $statusBiller = $this->request->getPost('status_biller');
        $statusCore = $this->request->getPost('status_core');
        $statusSettlement = $this->request->getPost('status_settlement');
        $idpartner = $this->request->getPost('idpartner');

        if (!$id || $statusBiller === null || $statusCore === null || $statusSettlement === null || !$idpartner) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak lengkap',
                'csrf_token' => csrf_hash()
            ]);
        }

        try {
            $db = \Config\Database::connect();
            $query = $db->query("CALL p_direct_jurnal_update(?, ?, ?, ?, ?)", [
                $id, $statusBiller, $statusCore, $statusSettlement, $idpartner
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Data berhasil diupdate',
                'csrf_token' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error updating dispute: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupdate data',
                'csrf_token' => csrf_hash()
            ]);
        }
    }

    /**
     * DataTables AJAX endpoint for dispute resolution
     */
    public function disputeDataTable()
    {
        // Get parameters from both GET and POST to handle DataTables requests
        $tanggalRekon = $this->request->getGet('tanggal') ?? $this->request->getPost('tanggal') ?? $this->prosesModel->getDefaultDate();
        $statusBiller = $this->request->getGet('status_biller') ?? $this->request->getPost('status_biller') ?? '';
        $statusCore = $this->request->getGet('status_core') ?? $this->request->getPost('status_core') ?? '';
        
        // Debug log
        log_message('info', 'DataTable parameters - Tanggal: ' . $tanggalRekon . ', Status Biller: ' . $statusBiller . ', Status Core: ' . $statusCore);
        
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
            0 => 'v_ID', // For row number (not actual column)
            1 => 'IDPARTNER',
            2 => 'TERMINALID', 
            3 => 'v_GROUP_PRODUK',
            4 => 'IDPEL',
            5 => 'RP_BILLER_TAG',
            6 => 'STATUS',
            7 => 'v_STAT_CORE_AGR',
            8 => 'v_ID' // For action column
        ];

        try {
            $db = \Config\Database::connect();
            
            // Base query
            $baseQuery = "
                SELECT IDPARTNER, TERMINALID, v_GROUP_PRODUK AS PRODUK, IDPEL, 
                       RP_BILLER_TAG, STATUS AS STATUS_BILLER, v_STAT_CORE_AGR AS STATUS_CORE, v_ID
                FROM v_cek_biller_dispute_direct 
                WHERE v_TGL_FILE_REKON = ?
            ";
            
            // Add status filters
            $queryParams = [$tanggalRekon];
            if ($statusBiller !== '') {
                $baseQuery .= " AND STATUS = ?";
                $queryParams[] = $statusBiller;
                log_message('info', 'Adding status_biller filter: ' . $statusBiller);
            }
            if ($statusCore !== '') {
                $baseQuery .= " AND v_STAT_CORE_AGR = ?";
                $queryParams[] = $statusCore;
                log_message('info', 'Adding status_core filter: ' . $statusCore);
            }
            
            // Add search conditions
            $searchConditions = [];
            
            if (!empty($searchValue)) {
                $searchConditions[] = "(
                    IDPARTNER LIKE ? OR 
                    TERMINALID LIKE ? OR 
                    v_GROUP_PRODUK LIKE ? OR 
                    IDPEL LIKE ? OR 
                    CAST(RP_BILLER_TAG AS CHAR) LIKE ?
                )";
                $searchTerm = "%{$searchValue}%";
                $queryParams = array_merge($queryParams, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }
            
            if (!empty($searchConditions)) {
                $baseQuery .= " AND " . implode(" AND ", $searchConditions);
            }
            
            // Count total records without filtering (but with status filters)
            $totalQuery = "
                SELECT COUNT(*) as total 
                FROM v_cek_biller_dispute_direct 
                WHERE v_TGL_FILE_REKON = ?
            ";
            $totalParams = [$tanggalRekon];
            if ($statusBiller !== '') {
                $totalQuery .= " AND STATUS = ?";
                $totalParams[] = $statusBiller;
            }
            if ($statusCore !== '') {
                $totalQuery .= " AND v_STAT_CORE_AGR = ?";
                $totalParams[] = $statusCore;
            }
            $totalResult = $db->query($totalQuery, $totalParams);
            $totalRecords = $totalResult->getRow()->total;
            
            // Count filtered records (with status filters and search)
            if (!empty($searchConditions)) {
                $filteredQuery = "
                    SELECT COUNT(*) as total 
                    FROM v_cek_biller_dispute_direct 
                    WHERE v_TGL_FILE_REKON = ?
                ";
                $filteredParams = [$tanggalRekon];
                if ($statusBiller !== '') {
                    $filteredQuery .= " AND STATUS = ?";
                    $filteredParams[] = $statusBiller;
                }
                if ($statusCore !== '') {
                    $filteredQuery .= " AND v_STAT_CORE_AGR = ?";
                    $filteredParams[] = $statusCore;
                }
                $filteredQuery .= " AND " . implode(" AND ", $searchConditions);
                // Add search parameters
                if (!empty($searchValue)) {
                    $searchTerm = "%{$searchValue}%";
                    $filteredParams = array_merge($filteredParams, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
                }
                $filteredResult = $db->query($filteredQuery, $filteredParams);
                $filteredRecords = $filteredResult->getRow()->total;
            } else {
                $filteredRecords = $totalRecords;
            }
            
            // Add ordering
            if (isset($columns[$orderColumn]) && $orderColumn > 0 && $orderColumn < 8) {
                $orderColumnName = $columns[$orderColumn];
                if ($orderColumn == 3) $orderColumnName = 'v_GROUP_PRODUK'; // Handle alias
                $baseQuery .= " ORDER BY {$orderColumnName} {$orderDir}";
            } else {
                $baseQuery .= " ORDER BY v_ID ASC";
            }
            
            // Add pagination
            $baseQuery .= " LIMIT {$length} OFFSET {$start}";
            
            // Log the final query and parameters
            log_message('info', 'Final query: ' . $baseQuery);
            log_message('info', 'Query parameters: ' . json_encode($queryParams));
            
            // Execute query
            $result = $db->query($baseQuery, $queryParams);
            $data = $result->getResultArray();
            
            // Format data for DataTables
            $formattedData = [];
            foreach ($data as $row) {
                $formattedData[] = [
                    'IDPARTNER' => $row['IDPARTNER'] ?? '',
                    'TERMINALID' => $row['TERMINALID'] ?? '',
                    'PRODUK' => $row['PRODUK'] ?? '',
                    'IDPEL' => $row['IDPEL'] ?? '',
                    'RP_BILLER_TAG' => $row['RP_BILLER_TAG'] ?? '0',
                    'STATUS_BILLER' => $row['STATUS_BILLER'] ?? '0',
                    'STATUS_CORE' => $row['STATUS_CORE'] ?? '0',
                    'v_ID' => $row['v_ID'] ?? ''
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
            log_message('error', 'Error in disputeDataTable: ' . $e->getMessage());
            return $this->response->setJSON([
                'draw' => intval($draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Terjadi kesalahan saat mengambil data',
                'csrf_token' => csrf_hash()
            ]);
        }
    }

    /**
     * DataTables AJAX endpoint for detail vs rekap
     */
    public function detailVsRekapDataTable()
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
     * Rekap Tx Indirect Jurnal
     * Menampilkan data dari procedure p_indirect_jurnal_rekap
     */
    public function indirectJurnalRekap()
    {
        $tanggalRekon = $this->request->getGet('tanggal') ?? $this->prosesModel->getDefaultDate();

        $data = [
            'title' => 'Rekap Tx Indirect Jurnal',
            'tanggalRekon' => $tanggalRekon,
            'route' => 'rekon/process/indirect-jurnal-rekap'
        ];

        return $this->render('rekon/process/indirect_jurnal_rekap.blade.php', $data);
    }

    /**
     * DataTables AJAX endpoint for indirect jurnal rekap
     */
    public function indirectJurnalRekapDataTable()
    {
        $tanggalRekon = $this->request->getGet('tanggal') ?? $this->request->getPost('tanggal') ?? $this->prosesModel->getDefaultDate();
        
        // DataTables parameters
        $draw = $this->request->getGet('draw') ?? $this->request->getPost('draw') ?? 1;
        $start = $this->request->getGet('start') ?? $this->request->getPost('start') ?? 0;
        $length = $this->request->getGet('length') ?? $this->request->getPost('length') ?? 25;

        try {
            $db = \Config\Database::connect();
            
            // Call the stored procedure to get data
            $procedureQuery = $db->query("CALL p_indirect_jurnal_rekap(?)", [$tanggalRekon]);
            $allData = $procedureQuery->getResultArray();
            
            // Calculate totals
            $totalRecords = count($allData);
            $filteredRecords = $totalRecords;
            
            // Apply pagination
            $pagedData = array_slice($allData, $start, $length);
            
            // Format data for DataTables
            $formattedData = [];
            foreach ($pagedData as $row) {
                // Remove v_tanggal_rekon from display
                unset($row['v_tanggal_rekon']);
                $formattedData[] = $row;
            }
            
            return $this->response->setJSON([
                'draw' => intval($draw),
                'recordsTotal' => intval($totalRecords),
                'recordsFiltered' => intval($filteredRecords),
                'data' => $formattedData,
                'csrf_token' => csrf_hash()
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in indirectJurnalRekapDataTable: ' . $e->getMessage());
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
     * Konfirmasi Setoran
     */
    public function konfirmasiSetoran()
    {
        try {
            $db = \Config\Database::connect();
            $query = $db->query("CALL p_indirect_jurnal_update(?)", ['PPOB KON']);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Konfirmasi setoran berhasil diproses',
                'csrf_token' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in konfirmasiSetoran: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses konfirmasi',
                'csrf_token' => csrf_hash()
            ]);
        }
    }

    /**
     * Penyelesaian Dispute Indirect
     */
    public function indirectDispute()
    {
        $tanggalRekon = $this->request->getGet('tanggal') ?? $this->prosesModel->getDefaultDate();

        $data = [
            'title' => 'Penyelesaian Dispute Indirect',
            'tanggalRekon' => $tanggalRekon,
            'route' => 'rekon/process/indirect-dispute'
        ];

        return $this->render('rekon/process/indirect_dispute.blade.php', $data);
    }

    /**
     * DataTables AJAX endpoint for indirect dispute
     */
    public function indirectDisputeDataTable()
    {
        $tanggalRekon = $this->request->getGet('tanggal') ?? $this->request->getPost('tanggal') ?? $this->prosesModel->getDefaultDate();
        $statusBiller = $this->request->getGet('status_biller') ?? $this->request->getPost('status_biller') ?? '';
        $statusCore = $this->request->getGet('status_core') ?? $this->request->getPost('status_core') ?? '';
        
        // DataTables parameters
        $draw = $this->request->getGet('draw') ?? $this->request->getPost('draw') ?? 1;
        $start = $this->request->getGet('start') ?? $this->request->getPost('start') ?? 0;
        $length = $this->request->getGet('length') ?? $this->request->getPost('length') ?? 25;

        try {
            $db = \Config\Database::connect();
            
            // Query sesuai arahan senior - hanya kolom yang diperlukan
            $baseQuery = "SELECT v_ID, IDPARTNER, TERMINALID, v_GROUP_PRODUK AS PRODUK, IDPEL, RP_BILLER_TAG, STATUS AS STATUS_BILLER, v_STAT_CORE_AGR AS STATUS_CORE
                         FROM v_cek_biller_dispute_indirect";
            $queryParams = [];
            $whereConditions = [];
            
            // Filter tanggal (wajib)
            if (!empty($tanggalRekon)) {
                $whereConditions[] = "v_TGL_FILE_REKON = ?";
                $queryParams[] = $tanggalRekon;
            }
            
            // Filter status biller
            if ($statusBiller !== '') {
                $whereConditions[] = "STATUS = ?";
                $queryParams[] = $statusBiller;
            }
            
            // Filter status core
            if ($statusCore !== '') {
                $whereConditions[] = "v_STAT_CORE_AGR = ?";
                $queryParams[] = $statusCore;
            }
            
            // Tambahkan WHERE conditions
            if (!empty($whereConditions)) {
                $baseQuery .= " WHERE " . implode(" AND ", $whereConditions);
            }
            
            // Get total records count
            $countQuery = str_replace("SELECT v_ID, IDPARTNER, TERMINALID, v_GROUP_PRODUK AS PRODUK, IDPEL, RP_BILLER_TAG, STATUS AS STATUS_BILLER, v_STAT_CORE_AGR AS STATUS_CORE", "SELECT COUNT(*) as total", $baseQuery);
            $countResult = $db->query($countQuery, $queryParams);
            $totalRecords = $countResult->getRow()->total ?? 0;
            
            // Add ORDER BY and pagination
            $baseQuery .= " ORDER BY v_ID DESC LIMIT {$length} OFFSET {$start}";
            
            // Execute main query
            $result = $db->query($baseQuery, $queryParams);
            $data = $result->getResultArray();

            log_message('info', "IndirectDispute query: {$baseQuery}");
            log_message('info', "IndirectDispute params: " . json_encode($queryParams));
            log_message('info', "IndirectDispute total records: {$totalRecords}");
            
            return $this->response->setJSON([
                'draw' => intval($draw),
                'recordsTotal' => intval($totalRecords),
                'recordsFiltered' => intval($totalRecords),
                'data' => $data,
                'csrf_token' => csrf_hash()
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in indirectDisputeDataTable: ' . $e->getMessage());
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
     * Get indirect dispute detail
     */
    public function getIndirectDisputeDetail()
    {
        $id = $this->request->getVar('id');
        
        if (!$id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID tidak ditemukan',
                'csrf_token' => csrf_hash()
            ]);
        }

        try {
            $db = \Config\Database::connect();
            $query = $db->query("
                SELECT * FROM v_cek_biller_dispute_indirect 
                WHERE v_ID = ?
            ", [$id]);
            
            $disputeDetail = $query->getRowArray();
            
            if (!$disputeDetail) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                    'csrf_token' => csrf_hash()
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $disputeDetail,
                'csrf_token' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error fetching indirect dispute detail: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data',
                'csrf_token' => csrf_hash()
            ]);
        }
    }

    /**
     * Update indirect dispute data
     */
    public function updateIndirectDispute()
    {
        $vId = $this->request->getVar('v_id');
        $statusBiller = $this->request->getVar('status_biller');
        $statusCore = $this->request->getVar('status_core');
        $statusSettlement = $this->request->getVar('status_settlement');
        $idpartner = $this->request->getVar('idpartner');

        // Debug log untuk melihat parameter yang diterima
        log_message('info', 'Received parameters: v_id=' . $vId . ', status_biller=' . $statusBiller . ', status_core=' . $statusCore . ', status_settlement=' . $statusSettlement . ', idpartner=' . $idpartner);

        if (!$vId || $statusBiller === '' || $statusCore === '' || $statusSettlement === '' || !$idpartner) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak lengkap. Harap isi semua field yang diperlukan.',
                'csrf_token' => csrf_hash()
            ]);
        }

        try {
            $db = \Config\Database::connect();
            
            // Call stored procedure p_update_dispute_tx sesuai arahan
            // Parameter: ID, STATUS_AGR, STATUS_CORE, STATUS_VERIF, IDPARTNER
            $query = "CALL p_update_dispute_tx(?, ?, ?, ?, ?)";
            $result = $db->query($query, [
                $vId,
                $statusBiller,
                $statusCore, 
                $statusSettlement,
                $idpartner
            ]);

            log_message('info', "Called p_update_dispute_tx with params: {$vId}, {$statusBiller}, {$statusCore}, {$statusSettlement}, {$idpartner}");

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Data dispute berhasil diproses',
                'csrf_token' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error updating indirect dispute: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses data: ' . $e->getMessage(),
                'csrf_token' => csrf_hash()
            ]);
        }
    }

    /**
     * Konfirmasi Saldo CA
     */
    public function konfirmasiSaldoCA()
    {
        $data = [
            'title' => 'Konfirmasi Saldo CA',
            'route' => 'rekon/process/konfirmasi-saldo-ca'
        ];

        return $this->render('rekon/process/konfirmasi_saldo_ca.blade.php', $data);
    }

    /**
     * Get fresh CSRF token
     */
    public function getCSRFToken()
    {
        return $this->response->setJSON([
            'success' => true,
            'csrf_token' => csrf_hash()
        ]);
    }
    
    /**
     * Get konfirmasi saldo CA datatable
     */
    public function konfirmasiSaldoCADataTable()
    {
        try {
            // Basic DataTable parameters
            $start = intval($this->request->getVar('start') ?? 0);
            $length = intval($this->request->getVar('length') ?? 10);
            $draw = intval($this->request->getVar('draw') ?? 1);
            
            // Filters
            $tanggal = $this->request->getVar('tanggal') ?? date('Y-m-d');
            $channel = $this->request->getVar('channel') ?? '';
            $status = $this->request->getVar('status') ?? '';
            
            // For now, return dummy data since we don't have actual stored procedure
            $mockData = [
                [
                    'ID' => '1',
                    'CHANNEL' => 'BCA',
                    'TANGGAL_TRX' => $tanggal,
                    'JUMLAH_TRANSAKSI' => 150,
                    'TOTAL_AMOUNT' => 15000000,
                    'SALDO_CA' => null,
                    'STATUS_KONFIRMASI' => 'PENDING',
                    'KETERANGAN' => '',
                    'CREATED_AT' => date('Y-m-d H:i:s'),
                    'UPDATED_AT' => date('Y-m-d H:i:s')
                ],
                [
                    'ID' => '2',
                    'CHANNEL' => 'MANDIRI',
                    'TANGGAL_TRX' => $tanggal,
                    'JUMLAH_TRANSAKSI' => 200,
                    'TOTAL_AMOUNT' => 25000000,
                    'SALDO_CA' => 25000000,
                    'STATUS_KONFIRMASI' => 'CONFIRMED',
                    'KETERANGAN' => 'Saldo sesuai dengan transaksi',
                    'CREATED_AT' => date('Y-m-d H:i:s'),
                    'UPDATED_AT' => date('Y-m-d H:i:s')
                ],
                [
                    'ID' => '3',
                    'CHANNEL' => 'BNI',
                    'TANGGAL_TRX' => $tanggal,
                    'JUMLAH_TRANSAKSI' => 75,
                    'TOTAL_AMOUNT' => 8500000,
                    'SALDO_CA' => 8400000,
                    'STATUS_KONFIRMASI' => 'REJECTED',
                    'KETERANGAN' => 'Selisih Rp 100,000 - perlu pengecekan lebih lanjut',
                    'CREATED_AT' => date('Y-m-d H:i:s'),
                    'UPDATED_AT' => date('Y-m-d H:i:s')
                ]
            ];
            
            // Apply filters
            $filteredData = array_filter($mockData, function($row) use ($channel, $status) {
                $channelMatch = empty($channel) || stripos($row['CHANNEL'], $channel) !== false;
                $statusMatch = empty($status) || $row['STATUS_KONFIRMASI'] === $status;
                return $channelMatch && $statusMatch;
            });
            
            $totalRecords = count($mockData);
            $filteredRecords = count($filteredData);
            
            // Pagination
            $pagedData = array_slice($filteredData, $start, $length);
            
            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => array_values($pagedData)
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Konfirmasi Saldo CA DataTable Error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'draw' => intval($this->request->getVar('draw') ?? 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Terjadi kesalahan saat memuat data'
            ]);
        }
    }
    
    /**
     * Get konfirmasi saldo CA summary
     */
    public function konfirmasiSaldoCASummary()
    {
        try {
            $tanggal = $this->request->getVar('tanggal') ?? date('Y-m-d');
            $channel = $this->request->getVar('channel') ?? '';
            $status = $this->request->getVar('status') ?? '';
            
            // Mock summary data
            $summaryData = [
                'total_pending' => 5,
                'total_confirmed' => 3,
                'total_rejected' => 1,
                'total_amount' => 75000000
            ];
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $summaryData
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Konfirmasi Saldo CA Summary Error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal memuat summary data'
            ]);
        }
    }
    
    /**
     * Submit konfirmasi saldo CA
     */
    public function submitKonfirmasiSaldoCA()
    {
        try {
            $validation = \Config\Services::validation();
            $validation->setRules([
                'id' => 'required',
                'saldo_ca' => 'required|numeric',
                'status' => 'required|in_list[CONFIRMED,REJECTED]',
                'keterangan' => 'required|min_length[10]'
            ]);
            
            if (!$validation->withRequest($this->request)->run()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Data tidak valid: ' . implode(', ', $validation->getErrors())
                ]);
            }
            
            $id = $this->request->getPost('id');
            $saldoCA = $this->request->getPost('saldo_ca');
            $status = $this->request->getPost('status');
            $keterangan = $this->request->getPost('keterangan');
            
            // TODO: Implement actual database update using stored procedure
            // For now, just return success
            
            log_message('info', "Konfirmasi Saldo CA - ID: $id, Status: $status, Saldo: $saldoCA");
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Konfirmasi saldo berhasil disimpan'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Submit Konfirmasi Saldo CA Error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan konfirmasi'
            ]);
        }
    }
    
    /**
     * Bulk konfirmasi saldo CA
     */
    public function bulkKonfirmasiSaldoCA()
    {
        try {
            $ids = $this->request->getPost('ids');
            $status = $this->request->getPost('status');
            $keterangan = $this->request->getPost('keterangan');
            
            if (empty($ids) || !is_array($ids)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tidak ada data yang dipilih'
                ]);
            }
            
            $validation = \Config\Services::validation();
            $validation->setRules([
                'status' => 'required|in_list[CONFIRMED,REJECTED]',
                'keterangan' => 'required|min_length[5]'
            ]);
            
            if (!$validation->withRequest($this->request)->run()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Data tidak valid: ' . implode(', ', $validation->getErrors())
                ]);
            }
            
            // TODO: Implement actual bulk update using stored procedure
            // For now, just return success
            
            $count = count($ids);
            $action = $status === 'CONFIRMED' ? 'dikonfirmasi' : 'ditolak';
            
            log_message('info', "Bulk Konfirmasi Saldo CA - Count: $count, Status: $status");
            
            return $this->response->setJSON([
                'success' => true,
                'message' => "$count data berhasil $action"
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Bulk Konfirmasi Saldo CA Error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses bulk konfirmasi'
            ]);
        }
    }
}
