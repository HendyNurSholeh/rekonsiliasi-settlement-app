<?php

namespace App\Models;

use CodeIgniter\Model;

class GroupSourceDbModel extends Model
{
    protected $table            = 't_group_source_db';
    protected $primaryKey       = 'ID';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;

    protected $allowedFields = [
        'NAMA_GROUP', 'KEY_GROUP', 'REK_DB', 'NAMA_REK_DB', 
        'TYPE_CORE', 'IS_DIRECT_JURNAL', 'KETERANGAN'
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
        'TYPE_CORE' => 'required|in_list[KON,SYA]',
        'IS_DIRECT_JURNAL' => 'required|in_list[0,1]'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Get all source database groups
     */
    public function getAllGroups()
    {
        return $this->orderBy('NAMA_GROUP', 'ASC')->findAll();
    }

    /**
     * Get groups by core type (KON/SYA)
     */
    public function getByCoreType($typeCore)
    {
        return $this->where('TYPE_CORE', $typeCore)
                    ->orderBy('NAMA_GROUP', 'ASC')
                    ->findAll();
    }

    /**
     * Get Konvensional groups
     */
    public function getKonvensionalGroups()
    {
        return $this->getByCoreType('KON');
    }

    /**
     * Get Syariah groups
     */
    public function getSyariahGroups()
    {
        return $this->getByCoreType('SYA');
    }

    /**
     * Get groups by jurnal type (direct/indirect)
     */
    public function getByJurnalType($isDirectJurnal)
    {
        return $this->where('IS_DIRECT_JURNAL', $isDirectJurnal)
                    ->orderBy('NAMA_GROUP', 'ASC')
                    ->findAll();
    }

    /**
     * Get direct jurnal groups
     */
    public function getDirectJurnalGroups()
    {
        return $this->getByJurnalType(1);
    }

    /**
     * Get indirect jurnal groups
     */
    public function getIndirectJurnalGroups()
    {
        return $this->getByJurnalType(0);
    }

    /**
     * Get group by key
     */
    public function getByKey($keyGroup)
    {
        return $this->where('KEY_GROUP', $keyGroup)->first();
    }

    /**
     * Get group by account number
     */
    public function getByAccountNumber($rekDb)
    {
        return $this->where('REK_DB', $rekDb)->findAll();
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
     * Get groups with account information
     */
    public function getGroupsWithAccount()
    {
        return $this->where('REK_DB IS NOT NULL')
                    ->where('REK_DB !=', '')
                    ->orderBy('NAMA_GROUP', 'ASC')
                    ->findAll();
    }

    /**
     * Get groups without account information
     */
    public function getGroupsWithoutAccount()
    {
        return $this->groupStart()
                    ->where('REK_DB IS NULL')
                    ->orWhere('REK_DB', '')
                    ->groupEnd()
                    ->orderBy('NAMA_GROUP', 'ASC')
                    ->findAll();
    }

    /**
     * Get summary by core type
     */
    public function getSummaryByCoreType()
    {
        return $this->select('
            TYPE_CORE,
            COUNT(*) as total_groups,
            COUNT(CASE WHEN IS_DIRECT_JURNAL = 1 THEN 1 END) as direct_jurnal_count,
            COUNT(CASE WHEN IS_DIRECT_JURNAL = 0 THEN 1 END) as indirect_jurnal_count
        ')
        ->groupBy('TYPE_CORE')
        ->findAll();
    }

    /**
     * Check if group exists by key
     */
    public function groupExists($keyGroup)
    {
        return $this->where('KEY_GROUP', $keyGroup)->countAllResults() > 0;
    }

    /**
     * Get channel groups (CHANNEL KON, CHANNEL SYA)
     */
    public function getChannelGroups()
    {
        return $this->like('NAMA_GROUP', 'CHANNEL')
                    ->orderBy('TYPE_CORE', 'ASC')
                    ->findAll();
    }

    /**
     * Get VA Digital groups
     */
    public function getVADigitalGroups()
    {
        return $this->like('NAMA_GROUP', 'VA DIGITAL')
                    ->orderBy('TYPE_CORE', 'ASC')
                    ->findAll();
    }

    /**
     * Get PPOB groups
     */
    public function getPPOBGroups()
    {
        return $this->like('NAMA_GROUP', 'PPOB')
                    ->orderBy('TYPE_CORE', 'ASC')
                    ->findAll();
    }

    /**
     * Get third-party payment groups
     */
    public function getThirdPartyGroups()
    {
        return $this->whereIn('NAMA_GROUP', [
            'MITRACOMM', 'POS INDONESIA', 'GO-PAY', 'ARTAJASA'
        ])
        ->orderBy('NAMA_GROUP', 'ASC')
        ->findAll();
    }
}
