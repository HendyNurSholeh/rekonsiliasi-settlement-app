<?php

namespace App\Models;

use CodeIgniter\Model;

class GroupSettlementModel extends Model
{
    protected $table            = 't_group_settlement';
    protected $primaryKey       = 'ID';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;

    protected $allowedFields = [
        'NAMA_GROUP', 'KEY_GROUP', 'JENIS_DATA', 'KETERANGAN'
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = '';
    protected $updatedField  = '';
    protected $deletedField  = '';

    // Validation
    protected $validationRules      = [
        'NAMA_GROUP' => 'required|max_length[100]',
        'KEY_GROUP' => 'required|max_length[100]',
        'JENIS_DATA' => 'required|max_length[100]'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Get all settlement groups
     */
    public function getAllGroups()
    {
        return $this->orderBy('NAMA_GROUP', 'ASC')->findAll();
    }

    /**
     * Get groups by type (DETAIL/REKAP)
     */
    public function getByType($jenisData)
    {
        return $this->where('JENIS_DATA', $jenisData)
                    ->orderBy('NAMA_GROUP', 'ASC')
                    ->findAll();
    }

    /**
     * Get group by key
     */
    public function getByKey($keyGroup)
    {
        return $this->where('KEY_GROUP', $keyGroup)->first();
    }

    /**
     * Get groups by category (A/B)
     */
    public function getByCategory($keterangan)
    {
        return $this->where('KETERANGAN', $keterangan)
                    ->orderBy('NAMA_GROUP', 'ASC')
                    ->findAll();
    }

    /**
     * Get unique group names
     */
    public function getUniqueGroupNames()
    {
        return $this->select('NAMA_GROUP')
                    ->distinct()
                    ->orderBy('NAMA_GROUP', 'ASC')
                    ->findAll();
    }

    /**
     * Search groups by name
     */
    public function searchByName($name)
    {
        return $this->like('NAMA_GROUP', $name)
                    ->orderBy('NAMA_GROUP', 'ASC')
                    ->findAll();
    }

    /**
     * Get detail and rekap pairs for a group
     */
    public function getGroupPairs($namaGroup)
    {
        return $this->where('NAMA_GROUP', $namaGroup)
                    ->orderBy('JENIS_DATA', 'ASC')
                    ->findAll();
    }

    /**
     * Get SAMSAT related groups
     */
    public function getSamsatGroups()
    {
        return $this->like('NAMA_GROUP', 'SAMSAT')
                    ->orLike('KEY_GROUP', '76033')
                    ->orderBy('JENIS_DATA', 'ASC')
                    ->findAll();
    }

    /**
     * Get PAJAK related groups
     */
    public function getPajakGroups()
    {
        return $this->like('NAMA_GROUP', 'PAJAK')
                    ->orderBy('NAMA_GROUP', 'ASC')
                    ->findAll();
    }

    /**
     * Get PBB related groups
     */
    public function getPBBGroups()
    {
        return $this->like('NAMA_GROUP', 'PBB')
                    ->orderBy('NAMA_GROUP', 'ASC')
                    ->findAll();
    }

    /**
     * Get PDAM related groups
     */
    public function getPDAMGroups()
    {
        return $this->like('NAMA_GROUP', 'PDAM')
                    ->orderBy('NAMA_GROUP', 'ASC')
                    ->findAll();
    }

    /**
     * Get EDU related groups
     */
    public function getEDUGroups()
    {
        return $this->like('NAMA_GROUP', 'EDU')
                    ->orderBy('NAMA_GROUP', 'ASC')
                    ->findAll();
    }

    /**
     * Get BPHTB related groups
     */
    public function getBPHTBGroups()
    {
        return $this->like('NAMA_GROUP', 'BPHTB')
                    ->orderBy('NAMA_GROUP', 'ASC')
                    ->findAll();
    }

    /**
     * Get groups summary by category
     */
    public function getGroupsSummary()
    {
        return $this->select('
            KETERANGAN as kategori,
            COUNT(*) as total_groups,
            COUNT(CASE WHEN JENIS_DATA = "DETAIL" THEN 1 END) as detail_count,
            COUNT(CASE WHEN JENIS_DATA = "REKAP" THEN 1 END) as rekap_count
        ')
        ->groupBy('KETERANGAN')
        ->findAll();
    }

    /**
     * Check if group exists by key
     */
    public function groupExists($keyGroup)
    {
        return $this->where('KEY_GROUP', $keyGroup)->countAllResults() > 0;
    }
}
