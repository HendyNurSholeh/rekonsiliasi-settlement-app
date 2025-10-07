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
}
