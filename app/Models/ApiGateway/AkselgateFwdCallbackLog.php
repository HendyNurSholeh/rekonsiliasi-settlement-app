<?php

namespace App\Models\ApiGateway;

use CodeIgniter\Model;

/**
 * Model untuk t_akselgatefwd_callback_log
 * 
 * Menyimpan log callback dari Aksel FWD API
 * Setiap transaksi individual mengirim callback terpisah dengan delay
 */
class AkselgateFwdCallbackLog extends Model
{
    protected $table = 't_akselgatefwd_callback_log';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'ref_number',
        'kd_settle',
        'res_code',
        'res_coreref',
        'status',
        'callback_data',
        'ip_address',
        'is_processed',
        'processed_at',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'ref_number' => 'required|max_length[15]',
        'status' => 'required|in_list[SUCCESS,FAILED]',
    ];

    protected $validationMessages = [
        'ref_number' => [
            'required' => 'Reference Number wajib diisi',
            'max_length' => 'Reference Number maksimal 15 karakter'
        ],
        'status' => [
            'required' => 'Status wajib diisi',
            'in_list' => 'Status harus SUCCESS atau FAILED'
        ]
    ];

    /**
     * Get unprocessed callbacks (belum di-update ke t_settle_message)
     * 
     * @return array
     */
    public function getUnprocessed()
    {
        return $this->where('is_processed', 0)
                    ->orderBy('created_at', 'ASC')
                    ->findAll();
    }

    /**
     * Get callbacks by REF_NUMBER
     * 
     * @param string $refNumber
     * @return array
     */
    public function getByRefNumber($refNumber)
    {
        return $this->where('ref_number', $refNumber)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get callbacks by KD_SETTLE
     * 
     * @param string $kdSettle
     * @return array
     */
    public function getByKdSettle($kdSettle)
    {
        return $this->where('kd_settle', $kdSettle)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get callbacks by status
     * 
     * @param string $status - SUCCESS or FAILED
     * @return array
     */
    public function getByStatus($status)
    {
        return $this->where('status', $status)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Mark callback as processed
     * 
     * @param int $id
     * @return bool
     */
    public function markAsProcessed($id)
    {
        return $this->update($id, [
            'is_processed' => 1,
            'processed_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get callback statistics by date range
     * 
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getStatistics($startDate, $endDate)
    {
        $sql = "
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as total_callbacks,
                SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) as total_success,
                SUM(CASE WHEN status = 'FAILED' THEN 1 ELSE 0 END) as total_failed,
                SUM(CASE WHEN is_processed = 1 THEN 1 ELSE 0 END) as total_processed,
                SUM(CASE WHEN is_processed = 0 THEN 1 ELSE 0 END) as total_unprocessed
            FROM {$this->table}
            WHERE DATE(created_at) BETWEEN ? AND ?
            GROUP BY DATE(created_at)
            ORDER BY date DESC
        ";

        return $this->db->query($sql, [$startDate, $endDate])->getResultArray();
    }
}
