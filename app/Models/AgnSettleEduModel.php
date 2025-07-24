<?php

namespace App\Models;

use CodeIgniter\Model;

class AgnSettleEduModel extends Model
{
    protected $table            = 't_agn_settle_edu';
    protected $primaryKey       = '';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;

    protected $allowedFields = [
        'TANGGAL', 'KODE_PRODUK', 'NAMA_PRODUK', 'KODE_JURUSAN', 'KODE_BIAYA',
        'NAMA_BIAYA', 'NOREK', 'AMOUNT', 'KODE_PRODUK_PRIVIDER', 'v_TGL_PROSES',
        'v_TGL_FILE_REKON'
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = '';
    protected $updatedField  = '';
    protected $deletedField  = '';

    // Validation
    protected $validationRules      = [
        'TANGGAL' => 'required|valid_date',
        'AMOUNT' => 'required|numeric'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Get settlement education data by date range
     */
    public function getByDateRange($startDate, $endDate)
    {
        return $this->where('TANGGAL >=', $startDate)
                    ->where('TANGGAL <=', $endDate)
                    ->findAll();
    }

    /**
     * Get data by product code
     */
    public function getByProductCode($kodeProduct)
    {
        return $this->where('KODE_PRODUK', $kodeProduct)->findAll();
    }

    /**
     * Get total settlement amount by date
     */
    public function getTotalSettlementByDate($date)
    {
        return $this->selectSum('AMOUNT', 'total_settlement')
                    ->where('TANGGAL', $date)
                    ->first();
    }

    /**
     * Get settlement summary by education provider
     */
    public function getSettlementSummaryByProvider($date)
    {
        return $this->select('NAMA_PRODUK, COUNT(*) as total_transaksi, SUM(AMOUNT) as total_amount')
                    ->where('TANGGAL', $date)
                    ->groupBy('NAMA_PRODUK')
                    ->orderBy('total_amount', 'DESC')
                    ->findAll();
    }

    /**
     * Get settlement data by account number
     */
    public function getByAccountNumber($norek)
    {
        return $this->where('NOREK', $norek)->findAll();
    }

    /**
     * Insert bulk settlement data
     */
    public function insertBulkSettlement($data)
    {
        return $this->insertBatch($data);
    }

    /**
     * Check if settlement data exists for date
     */
    public function hasSettlementForDate($date)
    {
        return $this->where('TANGGAL', $date)->countAllResults() > 0;
    }

    /**
     * Get settlement data for reconciliation process
     */
    public function getForReconciliation($processDate, $fileRekonDate = null)
    {
        $query = $this->where('v_TGL_PROSES', $processDate);
        
        if ($fileRekonDate) {
            $query->where('v_TGL_FILE_REKON', $fileRekonDate);
        }
        
        return $query->findAll();
    }

    /**
     * Get education settlement by department/major
     */
    public function getByDepartment($kodeJurusan, $date = null)
    {
        $query = $this->where('KODE_JURUSAN', $kodeJurusan);
        
        if ($date) {
            $query->where('TANGGAL', $date);
        }
        
        return $query->findAll();
    }
}
