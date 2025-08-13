# Update Laporan Transaksi Detail - Data Source t_agn_detail

## Overview
Dokumentasi ini menjelaskan perubahan pada `LaporanTransaksiDetailController` untuk mengambil data dari tabel `t_agn_detail` dengan filter `v_TGL_FILE_REKON` sesuai instruksi senior.

## Perubahan yang Dilakukan

### 1. Update Data Source
**Sebelum:**
```sql
FROM v_cek_biller_dispute_direct 
WHERE v_TGL_FILE_REKON = ?
```

**Sesudah:**
```sql
FROM t_agn_detail 
WHERE v_TGL_FILE_REKON = ?
```

### 2. Update Column Mapping

**Perubahan Kolom:**
| View/Alias | Tabel t_agn_detail | Keterangan |
|------------|-------------------|------------|
| `RP_BILLER_TAG` | `v_SETTLE_RP_TAG` | Nilai tagihan settlement |
| `v_ID` | `AGN_REF` | Primary key/ID reference |

**Column Mapping Updated:**
```php
$columns = [
    0 => 'AGN_REF',           // Changed from 'v_ID'
    1 => 'IDPARTNER',
    2 => 'TERMINALID', 
    3 => 'v_GROUP_PRODUK',
    4 => 'IDPEL',
    5 => 'v_SETTLE_RP_TAG',   // Changed from 'RP_BILLER_TAG'
    6 => 'STATUS',
    7 => 'v_STAT_CORE_AGR',
    8 => 'v_SETTLE_VERIFIKASI',
    9 => 'AGN_REF'            // Changed from 'v_ID'
];
```

### 3. Update Query Structure

**Base Query:**
```sql
SELECT IDPARTNER, TERMINALID, v_GROUP_PRODUK AS PRODUK, IDPEL, 
       v_SETTLE_RP_TAG AS RP_BILLER_TAG, STATUS AS STATUS_BILLER, 
       v_STAT_CORE_AGR AS STATUS_CORE, v_SETTLE_VERIFIKASI, 
       AGN_REF AS v_ID
FROM t_agn_detail 
WHERE v_TGL_FILE_REKON = ?
```

**Count Query:**
```sql
SELECT COUNT(*) as total 
FROM t_agn_detail 
WHERE v_TGL_FILE_REKON = ?
```

### 4. Update Search Functionality
Search condition diupdate untuk menggunakan `v_SETTLE_RP_TAG`:
```php
if (!empty($searchValue)) {
    $searchConditions[] = "(
        IDPARTNER LIKE ? OR 
        TERMINALID LIKE ? OR 
        v_GROUP_PRODUK LIKE ? OR 
        IDPEL LIKE ? OR 
        CAST(v_SETTLE_RP_TAG AS CHAR) LIKE ? OR    // Updated
        CAST(v_SETTLE_VERIFIKASI AS CHAR) LIKE ?
    )";
}
```

### 5. Update Detail Query
**getDisputeDetail() method:**
```sql
-- Before
SELECT * FROM v_cek_biller_dispute_direct WHERE v_ID = ?

-- After  
SELECT * FROM t_agn_detail WHERE AGN_REF = ?
```

### 6. Update Default Ordering
```php
// Before
$baseQuery .= " ORDER BY v_ID ASC";

// After
$baseQuery .= " ORDER BY AGN_REF ASC";
```

## Filter yang Tetap Tersedia

1. **Tanggal Rekonsiliasi** (`v_TGL_FILE_REKON`) - **PRIMARY FILTER**
2. **Status Biller** (`STATUS`)
3. **Status Core** (`v_STAT_CORE_AGR`) 
4. **Settle Verifikasi** (`v_SETTLE_VERIFIKASI`)
5. **ID Pelanggan** (`IDPEL`) - dengan LIKE search

## Struktur Data yang Dikembalikan

DataTable akan mengembalikan data dengan struktur:
```php
[
    'IDPARTNER' => string,
    'TERMINALID' => string, 
    'PRODUK' => string,           // alias dari v_GROUP_PRODUK
    'IDPEL' => string,
    'RP_BILLER_TAG' => string,    // alias dari v_SETTLE_RP_TAG
    'STATUS_BILLER' => string,    // alias dari STATUS
    'STATUS_CORE' => string,      // alias dari v_STAT_CORE_AGR
    'v_SETTLE_VERIFIKASI' => string,
    'v_ID' => string              // alias dari AGN_REF
]
```

## Keuntungan Perubahan

1. **Direct Table Access**: Mengakses data langsung dari tabel transaksi utama
2. **Performance**: Potensial lebih cepat karena tidak melalui view
3. **Data Consistency**: Data lebih real-time dari sumber utama
4. **Flexibility**: Lebih mudah untuk menambah kolom atau filter tambahan

## Testing Checklist

### Functional Testing
- [ ] Filter tanggal berfungsi dengan benar
- [ ] Filter status biller berfungsi
- [ ] Filter status core berfungsi  
- [ ] Filter settle verifikasi berfungsi
- [ ] Filter ID pelanggan berfungsi
- [ ] Search global berfungsi di semua kolom
- [ ] Pagination berfungsi dengan jumlah data yang benar
- [ ] Sorting berfungsi di semua kolom
- [ ] Detail modal menampilkan data yang benar

### Data Validation Testing
- [ ] Pastikan data yang ditampilkan sesuai dengan filter tanggal
- [ ] Verifikasi kolom `v_SETTLE_RP_TAG` menampilkan nilai yang benar
- [ ] Cek mapping `AGN_REF` sebagai ID unik
- [ ] Validasi semua status filter memberikan hasil yang akurat

### Performance Testing
- [ ] Test dengan volume data besar
- [ ] Verify query execution time
- [ ] Check memory usage untuk pagination
- [ ] Test concurrent access

## Troubleshooting

### Potential Issues
1. **Column Not Found**: Pastikan semua kolom ada di tabel `t_agn_detail`
2. **Data Type Mismatch**: Verifikasi tipe data kolom sesuai dengan filter
3. **Performance**: Monitor query execution time untuk dataset besar

### Debug Commands
```sql
-- Cek struktur tabel
DESCRIBE t_agn_detail;

-- Test query dasar
SELECT COUNT(*) FROM t_agn_detail WHERE v_TGL_FILE_REKON = '2025-08-13';

-- Test query dengan filter
SELECT IDPARTNER, TERMINALID, v_GROUP_PRODUK, IDPEL, v_SETTLE_RP_TAG 
FROM t_agn_detail 
WHERE v_TGL_FILE_REKON = '2025-08-13' 
LIMIT 10;
```

## Implementation Notes

- **Backward Compatibility**: Interface tetap sama, hanya sumber data yang berubah
- **Error Handling**: Error handling tetap sama dengan sebelumnya
- **CSRF Protection**: Token CSRF tetap digunakan untuk keamanan
- **Logging**: Query logging tetap aktif untuk debugging

## Next Steps

1. Test implementasi dengan data development
2. Verify dengan senior untuk memastikan hasil sesuai ekspektasi  
3. Deploy ke staging untuk UAT
4. Monitor performance di production

---

**Catatan**: Perubahan ini mengubah sumber data dari view `v_cek_biller_dispute_direct` ke tabel langsung `t_agn_detail` dengan filter utama `v_TGL_FILE_REKON` sesuai instruksi senior.
