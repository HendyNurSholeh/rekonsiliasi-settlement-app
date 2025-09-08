<?php

namespace App\Controllers\Rekon\Process\IndirectJurnal;

use App\Controllers\BaseController;
use App\Libraries\EventLogEnum;
use App\Libraries\LogEnum;
use App\Models\ProsesModel;
use App\Traits\HasLogActivity;

class RekapIndirectJurnalController extends BaseController
{
    use HasLogActivity;
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
        $tanggalData = $this->request->getGet('tanggal') ?? $this->prosesModel->getDefaultDate();

        $data = [
            'title' => 'Rekap Tx Indirect Jurnal',
            'tanggalData' => $tanggalData,
            'route' => 'rekon/process/indirect-jurnal-rekap'
        ];

        $this->logActivity([
			'log_name' => LogEnum::VIEW,
			'description' => session('username') . ' mengakses Halaman ' . $data['title'],
			'event' => EventLogEnum::VERIFIED,
			'subject' => '-',
		]);

        return $this->render('rekon/process/indirect_jurnal/rekap_indirect_jurnal/index.blade.php', $data);
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
     * Update Sukses Transaksi
     * Menjalankan procedure p_update_sukses_tx dengan parameter IDPARTNER dan TGL_FILE
     */
    public function updateSukses()
    {
        try {
            $group = $this->request->getPost('group');
            $tanggalRekon = $this->request->getPost('tanggal_rekon') ?? $this->prosesModel->getDefaultDate();
            
            if (!$group) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Parameter group tidak ditemukan',
                    'csrf_token' => csrf_hash()
                ]);
            }

            if (!$tanggalRekon) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Parameter tanggal rekonsiliasi tidak ditemukan',
                    'csrf_token' => csrf_hash()
                ]);
            }

            $db = \Config\Database::connect();
            
            // Call the stored procedure with IDPARTNER (sama dengan NAMA_GROUP) dan TGL_FILE
            $query = $db->query("CALL p_update_sukses_tx(?, ?)", [$group, $tanggalRekon]);

            // Log the update activity
            $this->logActivity([
                'log_name' => LogEnum::DATA,
                'description' => 'Update Data Button Proses Rekonsiliasi/Indirect Jurnal/Rekap Tx Indirect Jurnal',
                'event' => EventLogEnum::UPDATED,
                'subject_id' => $group,
                'subject' => 'Menu: Proses Rekonsiliasi/Indirect Jurnal/Rekap Tx Indirect Jurnal',
                'properties' => json_encode([
                    'new' => [
                        'group' => $group,
                        'tanggal_rekon' => $tanggalRekon,
                        'action' => 'konfirmasi_saldo_escrow',
                        'updated_by' => session('username'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]
                ])
            ]);

            log_message('info', "p_update_sukses_tx procedure called successfully for IDPARTNER: {$group}, TGL_FILE: {$tanggalRekon}");

            return $this->response->setJSON([
                'success' => true,
                'message' => "Konfirmasi saldo rekening escrow untuk group {$group} berhasil dilakukan",
                'csrf_token' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in updateSukses: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat melakukan konfirmasi saldo: ' . $e->getMessage(),
                'csrf_token' => csrf_hash()
            ]);
        }
    }
}
