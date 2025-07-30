<?php

namespace App\Models;

use CodeIgniter\Model;

class VGroupProdukModel extends Model
{
    protected $table      = 'v_cek_group_produk';
    protected $primaryKey = '';

    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false; // View tidak perlu proteksi field
    protected $allowedFields    = [];

    // Dates
    protected $useTimestamps = false;

    /**
     * Get all data from v_cek_group_produk view
     * Menggunakan view yang sudah dibuat senior
     * Yang belum mapping akan muncul paling atas
     */
    public function getGroupProdukData()
    {
        $sql = "SELECT * FROM v_cek_group_produk 
                ORDER BY 
                    CASE WHEN NAMA_GROUP IS NULL OR NAMA_GROUP = '' THEN 0 ELSE 1 END";
        return $this->db->query($sql)->getResultArray();
    }

    /**
     * Get mapping statistics berdasarkan view v_cek_group_produk
     */
    public function getMappingStatistics()
    {
        // Query untuk mendapatkan statistik langsung dari view
        $totalSql = "SELECT COUNT(*) as total FROM v_cek_group_produk";
        $mappedSql = "SELECT COUNT(*) as mapped FROM v_cek_group_produk WHERE NAMA_GROUP IS NOT NULL AND NAMA_GROUP != ''";
        $unmappedSql = "SELECT COUNT(*) as unmapped FROM v_cek_group_produk WHERE NAMA_GROUP IS NULL OR NAMA_GROUP = ''";
        
        $total = $this->db->query($totalSql)->getRow()->total ?? 0;
        $mapped = $this->db->query($mappedSql)->getRow()->mapped ?? 0;
        $unmapped = $this->db->query($unmappedSql)->getRow()->unmapped ?? 0;
        
        $percentage = $total > 0 ? round(($mapped / $total) * 100, 2) : 0;
        
        return [
            'total_products' => (int)$total,
            'mapped_products' => (int)$mapped,
            'unmapped_products' => (int)$unmapped,
            'mapping_percentage' => (float)$percentage
        ];
    }

    /**
     * Check if all products are mapped
     */
    public function isAllProductsMapped()
    {
        $unmappedCount = $this->db->query("SELECT COUNT(*) as count FROM v_cek_group_produk WHERE NAMA_GROUP IS NULL OR NAMA_GROUP = ''")->getRow()->count ?? 0;
        return $unmappedCount == 0;
    }

    /**
     * Get validation status untuk Step 2
     */
    public function getValidationStatus()
    {
        $stats = $this->getMappingStatistics();
        $isAllMapped = $this->isAllProductsMapped();
        
        return [
            'is_valid' => $isAllMapped,
            'statistics' => $stats,
            'validation_message' => $isAllMapped ? 
                'Semua produk telah termapping. Siap untuk proses rekonsiliasi.' : 
                'Terdapat ' . $stats['unmapped_products'] . ' produk yang belum termapping.',
            'can_proceed' => $isAllMapped
        ];
    }
}
