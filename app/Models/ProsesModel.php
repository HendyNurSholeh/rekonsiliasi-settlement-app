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

    // Validation
    protected $validationRules      = [
        'TGL_REKON' => 'required|valid_date|is_unique[t_proses.TGL_REKON]',
        'STATUS' => 'required|in_list[0,1]'
    ];
    protected $validationMessages   = [
        'TGL_REKON' => [
            'is_unique' => 'Proses rekonsiliasi untuk tanggal ini sudah ada'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Status constants
    const STATUS_PENDING = 0;
    const STATUS_COMPLETED = 1;

    /**
     * Create new reconciliation process
     */
    public function createProcess($tanggalRekon)
    {
        return $this->insert([
            'TGL_REKON' => $tanggalRekon,
            'STATUS' => self::STATUS_PENDING
        ]);
    }

    /**
     * Get process by date
     */
    public function getByDate($tanggalRekon)
    {
        return $this->where('TGL_REKON', $tanggalRekon)->first();
    }

    /**
     * Update process status
     */
    public function updateStatus($id, $status)
    {
        return $this->update($id, ['STATUS' => $status]);
    }

    /**
     * Complete process
     */
    public function completeProcess($id)
    {
        return $this->updateStatus($id, self::STATUS_COMPLETED);
    }

    /**
     * Get pending processes
     */
    public function getPendingProcesses()
    {
        return $this->where('STATUS', self::STATUS_PENDING)
                    ->orderBy('TGL_REKON', 'DESC')
                    ->findAll();
    }

    /**
     * Get completed processes
     */
    public function getCompletedProcesses()
    {
        return $this->where('STATUS', self::STATUS_COMPLETED)
                    ->orderBy('TGL_REKON', 'DESC')
                    ->findAll();
    }

    /**
     * Get all processes with status info
     */
    public function getAllWithStatusInfo()
    {
        return $this->select('
            ID,
            TGL_REKON,
            STATUS,
            CASE 
                WHEN STATUS = 0 THEN "Pending"
                WHEN STATUS = 1 THEN "Completed"
                ELSE "Unknown"
            END as status_label
        ')
        ->orderBy('TGL_REKON', 'DESC')
        ->findAll();
    }

    /**
     * Check if process exists for date
     */
    public function processExistsForDate($tanggalRekon)
    {
        return $this->where('TGL_REKON', $tanggalRekon)->countAllResults() > 0;
    }

    /**
     * Get latest process
     */
    public function getLatestProcess()
    {
        return $this->orderBy('TGL_REKON', 'DESC')->first();
    }

    /**
     * Get processes by date range
     */
    public function getByDateRange($startDate, $endDate)
    {
        return $this->where('TGL_REKON >=', $startDate)
                    ->where('TGL_REKON <=', $endDate)
                    ->orderBy('TGL_REKON', 'DESC')
                    ->findAll();
    }

    /**
     * Get process statistics
     */
    public function getStatistics()
    {
        return $this->select('
            COUNT(*) as total_processes,
            COUNT(CASE WHEN STATUS = 0 THEN 1 END) as pending_count,
            COUNT(CASE WHEN STATUS = 1 THEN 1 END) as completed_count,
            MAX(TGL_REKON) as latest_process_date,
            MIN(TGL_REKON) as earliest_process_date
        ')
        ->first();
    }

    /**
     * Delete old processes (older than specified days)
     */
    public function deleteOldProcesses($daysOld = 90)
    {
        $cutoffDate = date('Y-m-d', strtotime("-{$daysOld} days"));
        return $this->where('TGL_REKON <', $cutoffDate)->delete();
    }

    /**
     * Get process with reconciliation summary
     */
    public function getProcessWithSummary($id)
    {
        $process = $this->find($id);
        if (!$process) {
            return null;
        }

        // You can add additional queries here to get summary data
        // from related tables for this specific reconciliation date
        
        return $process;
    }

    /**
     * Mark process as failed (you might want to add a status for this)
     */
    public function markAsFailed($id)
    {
        // If you add a FAILED status (e.g., STATUS = 2), you can use this method
        // For now, we'll keep it as pending
        return $this->updateStatus($id, self::STATUS_PENDING);
    }
}
