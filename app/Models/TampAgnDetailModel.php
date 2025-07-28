<?php

namespace App\Models;

use CodeIgniter\Model;

class TampAgnDetailModel extends Model
{
    protected $table      = 'tamp_agn_detail';
    protected $primaryKey = 'IDTRX';

    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'IDTRX', 'BLTH', 'TGL_WAKTU', 'IDPARTNER', 'PRODUK', 'MERCHANT', 'IDPEL', 
        'RP_BILLER_POKOK', 'RP_BILLER_DENDA', 'RP_BILLER_LAIN', 'RP_BILLER_POTONGAN', 
        'RP_BILLER_TAG', 'RP_FEE_APP', 'RP_FEE_PARTNER', 'RP_FEE_BILLER', 
        'RP_FEE_AGREGATOR', 'RP_FEE_USER', 'RP_FEE_STRUK', 'RP_AMOUNT_STRUK', 
        'RP_AMOUNT', 'LEMBAR', 'AGN_REF', 'CLIENT_REF', 'CLIENT_STAN', 'CLIENT_IDTRX', 
        'BLTH_TAGIHAN', 'STATUS', 'KETERANGAN', 'SOURCE_DB', 'OWNER', 'OUTLET', 
        'KODE_USER', 'USER', 'SUB_IDPEL', 'IDPRODUK', 'REFF_BKS', 'TERMINALID',
        'v_GROUP_PRODUK', 'v_TGL_PROSES', 'v_TGL_FILE_REKON', 'v_REK_SUMBER', 
        'v_TYPE_CORE', 'v_IS_DIRECT_JURNAL', 'v_IS_DIRECT_FEE', 'v_STAT_CORE_AGR', 
        'v_CORE_RP_TAG', 'v_CORE_RP_FEE', 'v_SETTLE_VERIFIKASI', 'v_SETTLE_RP_TAG', 
        'v_SETTLE_RP_FEE'
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Insert batch data with chunk size to avoid memory issues
     */
    public function insertBatchData($data, $batchSize = 500)
    {
        if (empty($data)) {
            return false;
        }

        $chunks = array_chunk($data, $batchSize);
        
        foreach ($chunks as $chunk) {
            try {
                $this->db->transStart();
                $this->insertBatch($chunk);
                $this->db->transCommit();
            } catch (\Exception $e) {
                $this->db->transRollback();
                log_message('error', 'TampAgnDetailModel insertBatchData error: ' . $e->getMessage());
                throw $e;
            }
        }

        return true;
    }

    /**
     * Truncate table before inserting new data
     */
    public function truncateTable()
    {
        return $this->db->query("TRUNCATE TABLE {$this->table}");
    }

    /**
     * Get statistics for the table
     */
    public function getStatistics()
    {
        return [
            'total_records' => $this->countAll(),
            'total_amount' => $this->selectSum('RP_AMOUNT')->first()['RP_AMOUNT'] ?? 0,
            'latest_date' => $this->selectMax('v_TGL_FILE_REKON')->first()['v_TGL_FILE_REKON'] ?? null
        ];
    }
}
