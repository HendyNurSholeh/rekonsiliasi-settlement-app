<?php

namespace App\Models;

use CodeIgniter\Model;

class ProsesModel extends Model
{
    protected $table            = 't_proses';
    protected $primaryKey       = 'ID';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;

    protected $allowedFields = [
        'TGL_REKON', 'STATUS'
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = '';
    protected $updatedField  = '';
    protected $deletedField  = '';

    // Status constants
    const STATUS_PENDING = 0;
    const STATUS_COMPLETED = 1;

    /**
     * Get process by date
     */
    public function getByDate($tanggalRekon)
    {
        return $this->where('TGL_REKON', $tanggalRekon)->first();
    }

    /**
     * Check if date has existing process (simplified)
     */
    public function checkExistingProcess($tanggalRekon)
    {
        $existing = $this->getByDate($tanggalRekon);
        
        if ($existing) {
            return [
                'exists' => true,
                'process' => $existing
            ];
        }
        
        return [
            'exists' => false,
            'process' => null
        ];
    }

    /**
     * Call p_proses_persiapan stored procedure
     */
    public function callProcessPersiapan($tanggalRekon, $isReset = false)
    {
        try {
            $db = \Config\Database::connect();
            $formattedDate = date('Y-m-d', strtotime($tanggalRekon));
            
            // Call stored procedure p_proses_persiapan with only 1 parameter
            $query = "CALL p_proses_persiapan(?)";
            $result = $db->query($query, [$formattedDate]);
            
            if ($result) {
                // Since this stored procedure doesn't return result set, we check if it executed successfully
                return [
                    'success' => true,
                    'message' => $isReset ? 'Proses berhasil direset dan data temporary dibersihkan' : 'Proses rekonsiliasi berhasil dibuat',
                    'data' => [
                        'tanggal_rekon' => $formattedDate,
                        'is_reset' => $isReset
                    ]
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Gagal menjalankan stored procedure',
                'data' => null
            ];
            
        } catch (\Exception $e) {
            log_message('error', 'Error in callProcessPersiapan: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Reset process: Call p_reset_date first, then p_proses_persiapan
     * This is the proper reset flow as designed by senior
     */
    public function resetProcess($tanggalRekon)
    {
        try {
            $db = \Config\Database::connect();
            $formattedDate = date('Y-m-d', strtotime($tanggalRekon));
            
            // Step 1: Reset/clean data for specific date using p_reset_date
            $resetResult = $this->callResetDate($formattedDate);
            if (!$resetResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Gagal membersihkan data: ' . $resetResult['message']
                ];
            }
            
            // Step 2: Create new process preparation using p_proses_persiapan
            $prepResult = $this->callProcessPersiapan($formattedDate, true);
            if (!$prepResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Data berhasil dibersihkan, tapi gagal membuat proses baru: ' . $prepResult['message']
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Proses berhasil direset dan dibuat ulang untuk tanggal ' . date('d/m/Y', strtotime($tanggalRekon))
            ];
            
        } catch (\Exception $e) {
            log_message('error', 'Error in resetProcess: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error saat reset proses: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Call p_reset_date stored procedure
     * Clean data for specific date from all related tables
     */
    private function callResetDate($tanggalRekon)
    {
        try {
            $db = \Config\Database::connect();
            $formattedDate = date('Y-m-d', strtotime($tanggalRekon));
            
            // Call stored procedure p_reset_date
            $query = "CALL p_reset_date(?)";
            $result = $db->query($query, [$formattedDate]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Data untuk tanggal ' . $formattedDate . ' berhasil dibersihkan',
                    'details' => [
                        'tanggal_reset' => $formattedDate,
                        'tables_cleaned' => [
                            't_agn_detail', 't_agn_settle_edu', 't_agn_settle_pajak', 
                            't_agn_trx_mgate', 't_proses', 'temp_tables'
                        ]
                    ]
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Gagal menjalankan stored procedure p_reset_date'
            ];
            
        } catch (\Exception $e) {
            log_message('error', 'Error in callResetDate: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error saat reset data: ' . $e->getMessage()
            ];
        }
    }
}
