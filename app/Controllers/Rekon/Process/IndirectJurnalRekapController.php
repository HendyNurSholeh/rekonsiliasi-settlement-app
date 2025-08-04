<?php

namespace App\Controllers\Rekon\Process;

use App\Controllers\BaseController;
use App\Models\ProsesModel;

class IndirectJurnalRekapController extends BaseController
{
    protected $prosesModel;

    public function __construct()
    {
        $this->prosesModel = new ProsesModel();
    }

    /**
     * Rekap Tx Indirect Jurnal
     * Menampilkan data dari procedure p_indirect_jurnal_rekap
     */
    public function index()
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
    public function datatable()
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
                'csrf_token' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in konfirmasiSetoran: ' . $e->getMessage());
            return $this->response->setJSON([
                'csrf_token' => csrf_hash()
            ]);
        }
    }
}
