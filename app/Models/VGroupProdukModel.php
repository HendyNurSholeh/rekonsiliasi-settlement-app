<?php

namespace App\Models;

use CodeIgniter\Model;

class VGroupProdukModel extends Model
{
    protected $table      = 'v_group_produk';
    protected $primaryKey = '';

    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false; // View tidak perlu proteksi field
    protected $allowedFields    = [];

    // Dates
    protected $useTimestamps = false;

    /**
     * Get data from v_cek_group_produk view (jika view sudah ada)
     * Fallback ke query manual jika view belum ada
     */
    public function getGroupProdukData()
    {
        try {
            // Coba gunakan view v_cek_group_produk dulu
            $sql = "SELECT * FROM v_cek_group_produk ORDER BY SOURCE, PRODUK";
            return $this->db->query($sql)->getResultArray();
        } catch (\Exception $e) {
            // Fallback ke query manual jika view belum ada
            log_message('warning', 'View v_cek_group_produk not found, using manual query: ' . $e->getMessage());
            
            $sql = "SELECT 'DETAIL' AS SOURCE, a.PRODUK AS PRODUK, B.NAMA_GROUP AS NAMA_GROUP 
                    FROM (
                        SELECT DISTINCT PRODUK 
                        FROM tamp_agn_detail
                    ) A 
                    LEFT JOIN t_group_settlement B ON (a.PRODUK = B.KEY_GROUP AND B.JENIS_DATA = 'DETAIL')
                    
                    UNION
                    
                    SELECT 'REKAP' AS SOURCE, 
                           CONCAT(a.KODE_PRODUK, ' - ', a.NAMA_PRODUK) AS PRODUK, 
                           B.NAMA_GROUP AS NAMA_GROUP 
                    FROM (
                        SELECT DISTINCT KODE_PRODUK, NAMA_PRODUK 
                        FROM tamp_agn_settle_pajak
                    ) A 
                    LEFT JOIN t_group_settlement B ON (a.KODE_PRODUK = B.KEY_GROUP AND B.JENIS_DATA = 'REKAP')
                    
                    ORDER BY SOURCE, PRODUK";
            
            return $this->db->query($sql)->getResultArray();
        }
    }

    /**
     * Get produk yang NAMA_GROUP nya null (tidak ada mapping)
     */
    public function getProdukWithoutMapping()
    {
        $sql = "SELECT 'DETAIL' AS SOURCE, a.PRODUK AS PRODUK, B.NAMA_GROUP AS NAMA_GROUP 
                FROM (
                    SELECT DISTINCT PRODUK 
                    FROM tamp_agn_detail
                ) A 
                LEFT JOIN t_group_settlement B ON (a.PRODUK = B.KEY_GROUP AND B.JENIS_DATA = 'DETAIL')
                WHERE B.NAMA_GROUP IS NULL
                
                UNION
                
                SELECT 'REKAP' AS SOURCE, 
                       CONCAT(a.KODE_PRODUK, ' - ', a.NAMA_PRODUK) AS PRODUK, 
                       B.NAMA_GROUP AS NAMA_GROUP 
                FROM (
                    SELECT DISTINCT KODE_PRODUK, NAMA_PRODUK 
                    FROM tamp_agn_settle_pajak
                ) A 
                LEFT JOIN t_group_settlement B ON (a.KODE_PRODUK = B.KEY_GROUP AND B.JENIS_DATA = 'REKAP')
                WHERE B.NAMA_GROUP IS NULL
                
                ORDER BY SOURCE, PRODUK";
        
        return $this->db->query($sql)->getResultArray();
    }

    /**
     * Get statistics mapping
     */
    public function getMappingStatistics()
    {
        $allData = $this->getGroupProdukData();
        $unmappedData = $this->getProdukWithoutMapping();
        
        return [
            'total_produk' => count($allData),
            'mapped_produk' => count($allData) - count($unmappedData),
            'unmapped_produk' => count($unmappedData),
            'mapping_percentage' => count($allData) > 0 ? round(((count($allData) - count($unmappedData)) / count($allData)) * 100, 2) : 0
        ];
    }

    /**
     * Get detailed mapping information for display
     */
    public function getDetailedMappingInfo()
    {
        $allData = $this->getGroupProdukData();
        $unmappedData = $this->getProdukWithoutMapping();
        $stats = $this->getMappingStatistics();
        
        // Kelompokkan data berdasarkan source
        $detailData = array_filter($allData, function($item) {
            return $item['SOURCE'] === 'DETAIL';
        });
        
        $rekapData = array_filter($allData, function($item) {
            return $item['SOURCE'] === 'REKAP';
        });
        
        return [
            'statistics' => $stats,
            'all_data' => $allData,
            'unmapped_data' => $unmappedData,
            'detail_data' => array_values($detailData),
            'rekap_data' => array_values($rekapData),
            'detail_count' => count($detailData),
            'rekap_count' => count($rekapData),
            'detail_unmapped' => array_filter($unmappedData, function($item) {
                return $item['SOURCE'] === 'DETAIL';
            }),
            'rekap_unmapped' => array_filter($unmappedData, function($item) {
                return $item['SOURCE'] === 'REKAP';
            })
        ];
    }
}
