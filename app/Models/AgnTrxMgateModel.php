<?php

namespace App\Models;

use CodeIgniter\Model;

class AgnTrxMgateModel extends Model
{
    protected $table            = 't_agn_trx_mgate';
    protected $primaryKey       = '';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;

    protected $allowedFields = [
        'BRANCH', 'STMT_BOOKING_DATE', 'FT_BIL_PRODUCT', 'STMT_DATE_TIME',
        'FT_BIL_CUSTOMER', 'FT_TERM_ID', 'KET', 'FT_DEBIT_ACCT_NO',
        'FT_TRANS_REFF', 'STMT_OUR_REFF', 'RECIPT_NO', 'AMOUNT', 'FEE',
        'v_STAT_CORE_AGR'
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = '';
    protected $updatedField  = '';
    protected $deletedField  = '';

    // Validation
    protected $validationRules      = [
        'STMT_BOOKING_DATE' => 'required|valid_date',
        'AMOUNT' => 'required|numeric'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Get MGate transaction data by date range
     */
    public function getByDateRange($startDate, $endDate)
    {
        return $this->where('STMT_BOOKING_DATE >=', $startDate)
                    ->where('STMT_BOOKING_DATE <=', $endDate)
                    ->findAll();
    }

    /**
     * Get data by product
     */
    public function getByProduct($product)
    {
        return $this->where('FT_BIL_PRODUCT', $product)->findAll();
    }

    /**
     * Get total amount by date
     */
    public function getTotalAmountByDate($date)
    {
        return $this->selectSum('AMOUNT', 'total_amount')
                    ->selectSum('FEE', 'total_fee')
                    ->where('STMT_BOOKING_DATE', $date)
                    ->first();
    }

    /**
     * Get transaction summary by branch
     */
    public function getSummaryByBranch($date)
    {
        return $this->select('BRANCH, COUNT(*) as total_transaksi, SUM(AMOUNT) as total_amount, SUM(FEE) as total_fee')
                    ->where('STMT_BOOKING_DATE', $date)
                    ->groupBy('BRANCH')
                    ->findAll();
    }

    /**
     * Get transaction by reference number
     */
    public function getByReferenceNumber($refNumber)
    {
        return $this->where('FT_TRANS_REFF', $refNumber)
                    ->orWhere('STMT_OUR_REFF', $refNumber)
                    ->orWhere('RECIPT_NO', $refNumber)
                    ->findAll();
    }

    /**
     * Get data by customer
     */
    public function getByCustomer($customer)
    {
        return $this->where('FT_BIL_CUSTOMER', $customer)->findAll();
    }

    /**
     * Insert bulk MGate transaction data
     */
    public function insertBulkTransaction($data)
    {
        return $this->insertBatch($data);
    }

    /**
     * Check if MGate data exists for date
     */
    public function hasDataForDate($date)
    {
        return $this->where('STMT_BOOKING_DATE', $date)->countAllResults() > 0;
    }

    /**
     * Get transaction data for reconciliation matching
     */
    public function getForReconciliation($date)
    {
        return $this->where('STMT_BOOKING_DATE', $date)
                    ->where('v_STAT_CORE_AGR', 0) // Unmatched transactions
                    ->findAll();
    }

    /**
     * Update reconciliation status
     */
    public function updateReconciliationStatus($conditions, $status)
    {
        return $this->where($conditions)
                    ->set('v_STAT_CORE_AGR', $status)
                    ->update();
    }

    /**
     * Get unreconciled transactions
     */
    public function getUnreconciledTransactions($date = null)
    {
        $query = $this->where('v_STAT_CORE_AGR', 0);
        
        if ($date) {
            $query->where('STMT_BOOKING_DATE', $date);
        }
        
        return $query->findAll();
    }

    /**
     * Get transaction by terminal ID
     */
    public function getByTerminalId($terminalId, $date = null)
    {
        $query = $this->where('FT_TERM_ID', $terminalId);
        
        if ($date) {
            $query->where('STMT_BOOKING_DATE', $date);
        }
        
        return $query->findAll();
    }

    /**
     * Get transaction by debit account
     */
    public function getByDebitAccount($accountNo, $date = null)
    {
        $query = $this->where('FT_DEBIT_ACCT_NO', $accountNo);
        
        if ($date) {
            $query->where('STMT_BOOKING_DATE', $date);
        }
        
        return $query->findAll();
    }

    /**
     * Get unique products
     */
    public function getUniqueProducts()
    {
        return $this->select('FT_BIL_PRODUCT')
                    ->distinct()
                    ->orderBy('FT_BIL_PRODUCT')
                    ->findAll();
    }
}
