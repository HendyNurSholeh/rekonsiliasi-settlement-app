<?php

namespace App\Models;

use CodeIgniter\Model;

class AgnSettlePajakModel extends Model
{
    protected $table            = 't_agn_settle_pajak';
    protected $primaryKey       = 'ID';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;

    protected $allowedFields = [
        'TANGGAL', 'KODE_PRODUK', 'NAMA_PRODUK', 'KODE_JURUSAN', 'KODE_BIAYA',
        'NAMA_BIAYA', 'JENIS', 'NOREK', 'NAMA_REKENING', 'AMOUNT', 'NARATIVE',
        'KODE_PRODUK_PROVIDER', 'v_TGL_PROSES', 'v_TGL_FILE_REKON'
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
     * Get settlement pajak data by date range
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
     * Get total settlement amount by date and jenis
     */
    public function getTotalSettlementByDateAndJenis($date, $jenis = null)
    {
        $query = $this->selectSum('AMOUNT', 'total_settlement')
                      ->where('TANGGAL', $date);
                      
        if ($jenis) {
            $query->where('JENIS', $jenis);
        }
        
        return $query->first();
    }

    /**
     * Get settlement summary by tax type (POKOK, DENDA, OPSEN)
     */
    public function getSettlementSummaryByTaxType($date)
    {
        return $this->select('JENIS, COUNT(*) as total_transaksi, SUM(AMOUNT) as total_amount')
                    ->where('TANGGAL', $date)
                    ->groupBy('JENIS')
                    ->findAll();
    }

    /**
     * Get settlement data by region/area
     */
    public function getByRegion($namaProduk, $date = null)
    {
        $query = $this->where('NAMA_PRODUK', $namaProduk);
        
        if ($date) {
            $query->where('TANGGAL', $date);
        }
        
        return $query->findAll();
    }

    /**
     * Get settlement by account number
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
     * Get tax settlement by type (POKOK, DENDA, OPSEN)
     */
    public function getByTaxType($jenis, $date = null)
    {
        $query = $this->where('JENIS', $jenis);
        
        if ($date) {
            $query->where('TANGGAL', $date);
        }
        
        return $query->findAll();
    }

    /**
     * Get unique regions/areas
     */
    public function getUniqueRegions()
    {
        return $this->select('NAMA_PRODUK')
                    ->distinct()
                    ->orderBy('NAMA_PRODUK')
                    ->findAll();
    }

    /**
     * Get tax settlement by narrative pattern
     */
    public function getByNarrativePattern($pattern, $date = null)
    {
        $query = $this->like('NARATIVE', $pattern);
        
        if ($date) {
            $query->where('TANGGAL', $date);
        }
        
        return $query->findAll();
    }
}
