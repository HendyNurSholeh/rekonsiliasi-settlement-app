<?php

namespace App\Models;

use CodeIgniter\Model;

class TampAgnTrxMgateModel extends Model
{
    protected $table      = 'tamp_agn_trx_mgate';
    protected $primaryKey = 'ID';

    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'BRANCH', 'STMT_BOOKING_DATE', 'FT_BIL_PRODUCT', 'STMT_DATE_TIME', 
        'FT_BIL_CUSTOMER', 'FT_TERM_ID', 'KET', 'FT_DEBIT_ACCT_NO', 
        'FT_TRANS_REFF', 'STMT_OUR_REFF', 'RECIPT_NO', 'AMOUNT', 'FEE', 
        'v_STAT_CORE_AGR', 'v_TGL_PROSES', 'v_TGL_FILE_REKON'
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
                log_message('error', 'TampAgnTrxMgateModel insertBatchData error: ' . $e->getMessage());
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
            'total_amount' => $this->selectSum('AMOUNT')->first()['AMOUNT'] ?? 0,
            'latest_date' => $this->selectMax('v_TGL_FILE_REKON')->first()['v_TGL_FILE_REKON'] ?? null
        ];
    }
}
