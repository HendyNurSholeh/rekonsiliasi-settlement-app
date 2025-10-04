<?php

namespace App\Models\Settlement;

use CodeIgniter\Model;

class JurnalCaEscrowProcessLog extends Model
{
    protected $table = 't_jurnal_ca_escrow_process_log';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'kd_settle',
        'request_id',
        'status_code_res',
        'is_success',
        'total_transaksi',
        'api_response',
        'sent_by',
        'sent_at'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    /**
     * Cek apakah kd_settle sudah pernah diproses
     */
    public function isProcessed($kdSettle)
    {
        return $this->where('kd_settle', $kdSettle)
            ->where('is_success', 1)
            ->countAllResults() > 0;
    }
    
    /**
     * Get data proses terakhir untuk kd_settle
     */
    public function getLastProcess($kdSettle)
    {
        return $this->where('kd_settle', $kdSettle)
            ->orderBy('id', 'DESC')
            ->first();
    }
    
    /**
     * Create new process log
     */
    public function createLog($data)
    {
        return $this->insert($data);
    }
}
