<?php

namespace App\Services;

/**
 * ParentChildDataTableService
 * 
 * Service sederhana untuk handle DataTable dengan parent-child rows
 * Bisa digunakan untuk JurnalCaEscrowController dan JurnalEscrowBillerPlController
 * 
 * Method utama:
 * - handleRequest(): Orchestrator utama yang handle semua proses
 * - applyFilters(): Apply search, sort, dan pagination
 * - formatResponse(): Format data jadi response JSON DataTable
 */
class ParentChildDataTableService
{
    /**
     * Handle DataTable request secara lengkap
     * 
     * @param array $request Request dari DataTable (draw, start, length, search, order, columns)
     * @param array $data Data yang sudah diproses (array of parent rows)
     * @param array $searchFields Field-field yang bisa di-search
     * @return array Response untuk DataTable
     */
    public function handleRequest(array $request, array $data, array $searchFields = []): array
    {
        $draw = (int) ($request['draw'] ?? 1);
        $start = (int) ($request['start'] ?? 0);
        $length = (int) ($request['length'] ?? 10);
        $searchValue = $request['search']['value'] ?? '';
        
        // Parse column sorting
        $orderColumnIndex = (int) ($request['order'][0]['column'] ?? 0);
        $orderDir = $request['order'][0]['dir'] ?? 'asc';
        $orderColumnName = $request['columns'][$orderColumnIndex]['data'] ?? '';
        
        // Total data sebelum filter
        $recordsTotal = count($data);
        
        // Apply filters (search, sort, pagination)
        $filteredData = $this->applyFilters(
            $data,
            $searchValue,
            $searchFields,
            $orderColumnName,
            $orderDir,
            $start,
            $length
        );
        
        // Total data setelah filter
        $recordsFiltered = $filteredData['total'];
        
        // Format response
        return $this->formatResponse($draw, $filteredData['data'], $recordsTotal, $recordsFiltered);
    }
    
    /**
     * Apply search, sort, dan pagination ke data
     * 
     * @param array $data Data asli
     * @param string $searchValue Keyword search
     * @param array $searchFields Field yang bisa di-search
     * @param string $orderColumn Column untuk sort
     * @param string $orderDir Direction sort (asc/desc)
     * @param int $start Pagination start
     * @param int $length Pagination length
     * @return array ['data' => filtered data, 'total' => total filtered]
     */
    public function applyFilters(
        array $data,
        string $searchValue,
        array $searchFields,
        string $orderColumn,
        string $orderDir,
        int $start,
        int $length
    ): array {
        // 1. Apply search filter
        if (!empty($searchValue) && !empty($searchFields)) {
            $data = array_filter($data, function ($row) use ($searchValue, $searchFields) {
                foreach ($searchFields as $field) {
                    if (isset($row[$field]) && stripos($row[$field], $searchValue) !== false) {
                        return true;
                    }
                }
                return false;
            });
            
            // Re-index array setelah filter
            $data = array_values($data);
        }
        
        // 2. Apply sorting
        if (!empty($orderColumn) && isset($data[0][$orderColumn])) {
            usort($data, function ($a, $b) use ($orderColumn, $orderDir) {
                $aVal = $a[$orderColumn] ?? '';
                $bVal = $b[$orderColumn] ?? '';
                
                // Compare values
                $comparison = strcmp($aVal, $bVal);
                
                return ($orderDir === 'desc') ? -$comparison : $comparison;
            });
        }
        
        // Total setelah search tapi sebelum pagination
        $total = count($data);
        
        // 3. Apply pagination
        $data = array_slice($data, $start, $length);
        
        return [
            'data' => $data,
            'total' => $total
        ];
    }
    
    /**
     * Format response untuk DataTable
     * 
     * @param int $draw Draw counter dari request
     * @param array $data Data yang sudah difilter dan dipaginate
     * @param int $recordsTotal Total semua record
     * @param int $recordsFiltered Total record setelah filter
     * @return array Response JSON untuk DataTable
     */
    public function formatResponse(int $draw, array $data, int $recordsTotal, int $recordsFiltered): array
    {
        return [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ];
    }
    
    /**
     * Process raw data from stored procedure to create parent-child structure
     * Group rows by r_KD_SETTLE and create child rows for detail data
     * 
     * @param array $rawData Raw data from stored procedure
     * @param array $parentFields List of parent field names to extract (e.g., ['r_AMOUNT_ESCROW', 'r_TOTAL_JURNAL'])
     * @param array $childFields List of child field names to extract (e.g., ['d_NO_REF', 'd_DEBIT_ACCOUNT'])
     * @param array $errorMessages Map of kd_settle => error_message
     * @return array Grouped data with parent-child structure
     */
    public function processParentChildData(
        array $rawData,
        array $parentFields,
        array $childFields,
        array $errorMessages = []
    ): array {
        $grouped = [];
        
        foreach ($rawData as $row) {
            $kdSettle = $row['r_KD_SETTLE'] ?? '';
            
            // Create parent row if not exists
            if (!isset($grouped[$kdSettle])) {
                // Always include r_KD_SETTLE and r_NAMA_PRODUK
                $parentRow = [
                    'r_KD_SETTLE' => $row['r_KD_SETTLE'] ?? '',
                    'r_NAMA_PRODUK' => $row['r_NAMA_PRODUK'] ?? '',
                ];
                
                // Add additional parent fields
                foreach ($parentFields as $field) {
                    $parentRow[$field] = $row[$field] ?? '0';
                }
                
                $parentRow['child_rows'] = [];
                $grouped[$kdSettle] = $parentRow;
            }
            
            // Add child row data (d_ fields)
            if (!empty($row['d_NO_REF'])) {
                $childRow = [];
                
                foreach ($childFields as $field) {
                    $childRow[$field] = $row[$field] ?? '';
                }
                
                // Always add error message if exists
                $childRow['d_ERROR_MESSAGE'] = $errorMessages[$kdSettle] ?? '';
                
                $grouped[$kdSettle]['child_rows'][] = $childRow;
            }
        }
        
        // Convert to indexed array
        return array_values($grouped);
    }
    
    /**
     * Get error messages dari t_akselgate_transaction_log untuk kd_settle yang gagal
     * Hanya ambil dari attempt terbaru (is_latest = 1)
     * 
     * @param \App\Models\ApiGateway\AkselgateTransactionLog $logModel Instance of log model
     * @param array $kdSettleList Array of kd_settle
     * @param string $transactionType Transaction type (CA_ESCROW atau ESCROW_BILLER_PL)
     * @return array Map of kd_settle => error_message
     */
    public function getErrorMessages($logModel, array $kdSettleList, string $transactionType): array
    {
        if (empty($kdSettleList)) {
            return [];
        }
        
        try {
            // Query menggunakan model CI4
            $results = $logModel->select('kd_settle, response_message, status_code_res, attempt_number')
                ->whereIn('kd_settle', $kdSettleList)
                ->where('transaction_type', $transactionType)
                ->where('is_latest', 1) // PENTING: Hanya ambil attempt terbaru
                ->where('is_success', 0) // Hanya ambil yang gagal
                ->findAll();
            
            // Map kd_settle => error_message
            $errorMap = [];
            foreach ($results as $result) {
                $kdSettle = $result['kd_settle'];
                $errorMap[$kdSettle] = $result['response_message'] ?? 'Error: ' . ($result['status_code_res'] ?? 'Unknown');
            }
            
            return $errorMap;
            
        } catch (\Exception $e) {
            log_message('error', 'ParentChildDataTableService: Error fetching error messages: ' . $e->getMessage());
            return [];
        }
    }
}
