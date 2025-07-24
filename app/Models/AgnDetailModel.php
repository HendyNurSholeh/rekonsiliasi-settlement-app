<?php

namespace App\Models;

use CodeIgniter\Model;

class AgnDetailModel extends Model
{
    protected $table            = 't_agn_detail';
    protected $primaryKey       = 'IDTRX';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;

    protected $allowedFields = [
        'IDTRX', 'BLTH', 'TGL_WAKTU', 'IDPARTNER', 'PRODUK', 'MERCHANT',
        'IDPEL', 'RP_BILLER_POKOK', 'RP_BILLER_DENDA', 'RP_BILLER_LAIN',
        'RP_BILLER_POTONGAN', 'RP_BILLER_TAG', 'RP_FEE_APP', 'RP_FEE_PARTNER',
        'RP_FEE_BILLER', 'RP_FEE_AGREGATOR', 'RP_FEE_USER', 'RP_FEE_STRUK',
        'RP_AMOUNT_STRUK', 'RP_AMOUNT', 'LEMBAR', 'AGN_REF', 'CLIENT_REF',
        'CLIENT_STAN', 'CLIENT_IDTRX', 'BLTH_TAGIHAN', 'STATUS', 'KETERANGAN',
        'SOURCE_DB', 'OWNER', 'OUTLET', 'KODE_USER', 'USER', 'SUB_IDPEL',
        'IDPRODUK', 'REFF_BKS', 'TERMINALID', 'v_GROUP_PRODUK', 'v_TGL_PROSES',
        'v_TGL_FILE_REKON', 'v_REK_SUMBER', 'v_TYPE_CORE', 'v_STAT_CORE_AGR',
        'v_VERIFIKASI_DANA'
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = '';
    protected $updatedField  = '';
    protected $deletedField  = '';

    // Validation
    protected $validationRules      = [
        'IDTRX' => 'required|is_unique[t_agn_detail.IDTRX]'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Get data agregator by date range
     */
    public function getByDateRange($startDate, $endDate)
    {
        return $this->where('v_TGL_PROSES >=', $startDate)
                    ->where('v_TGL_PROSES <=', $endDate)
                    ->findAll();
    }

    /**
     * Get data by product
     */
    public function getByProduct($produk)
    {
        return $this->where('PRODUK', $produk)->findAll();
    }

    /**
     * Get total amount by date
     */
    public function getTotalAmountByDate($date)
    {
        return $this->selectSum('RP_AMOUNT', 'total_amount')
                    ->where('v_TGL_PROSES', $date)
                    ->first();
    }

    /**
     * Get summary data for reconciliation
     */
    public function getReconciliationSummary($date)
    {
        return $this->select('PRODUK, COUNT(*) as total_transaksi, SUM(RP_AMOUNT) as total_amount')
                    ->where('v_TGL_PROSES', $date)
                    ->groupBy('PRODUK')
                    ->findAll();
    }

    /**
     * Check if data exists for specific date
     */
    public function hasDataForDate($date)
    {
        return $this->where('v_TGL_PROSES', $date)->countAllResults() > 0;
    }

    /**
     * Get unverified transactions
     */
    public function getUnverifiedTransactions($date = null)
    {
        $query = $this->where('v_VERIFIKASI_DANA', 0);
        
        if ($date) {
            $query->where('v_TGL_PROSES', $date);
        }
        
        return $query->findAll();
    }
}
