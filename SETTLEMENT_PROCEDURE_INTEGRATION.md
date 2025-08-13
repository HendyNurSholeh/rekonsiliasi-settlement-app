# Settlement Procedure Integration Guide

## Overview
Dokumentasi ini menjelaskan integrasi procedure `p_compare_rekap` dengan modul Settlement untuk menampilkan data yang komprehensif dalam pembuatan jurnal settlement.

## Procedure p_compare_rekap

### Input Parameter
- `tanggal_rekon` (DATE): Tanggal rekonsiliasi yang akan diproses

### Output Columns
| Column Name | Description | Type | Example |
|-------------|-------------|------|---------|
| `NAMA_GROUP` | Nama grup produk/produk | VARCHAR | "E-MONEY", "QRIS" |
| `FILE_SETTLE` | Jenis file settle (0=Default, 1=Pajak, 2=Edu) | INT | 0, 1, 2 |
| `AMOUNT_DETAIL` | Total nominal dari detail transaksi terverifikasi | VARCHAR(formatted) | "1,234,567" |
| `AMOUNT_REKAP` | Total nominal dari file rekap settlement | VARCHAR(formatted) | "1,234,567" |
| `SELISIH` | Selisih antara Amount Detail dan Amount Rekap | VARCHAR(formatted) | "0", "123" |
| `JUM_TX_DISPURE` | Jumlah transaksi yang berstatus dispute | VARCHAR(formatted) | "0", "5" |
| `AMOUNT_TX_DISPURE` | Total nominal transaksi dispute | VARCHAR(formatted) | "0", "50,000" |
| `KD_SETTLE` | Kode settlement (jika sudah dibuat) | VARCHAR | "STL001", NULL |

## Business Logic Implementation

### Data Sources
1. **Detail Transaksi**: `t_agn_detail` 
   - Filter: `v_SETTLE_VERIFIKASI = 1` AND `v_TGL_FILE_REKON = tanggal_rekon`
   - Group by: `v_GROUP_PRODUK`

2. **Rekap Pajak**: `t_agn_settle_pajak`
   - Join dengan: `t_group_settlement` (KEY_GROUP = KODE_PRODUK)
   - Filter: `JENIS_DATA = 'REKAP'` AND `v_TGL_FILE_REKON = tanggal_rekon`

3. **Rekap Edu**: `t_agn_settle_edu`
   - Join dengan: `t_group_settlement` (KEY_GROUP = KODE_PRODUK)
   - Filter: `JENIS_DATA = 'REKAP'` AND `v_TGL_FILE_REKON = tanggal_rekon`

4. **Dispute Data**: `t_agn_detail`
   - Filter: `STATUS = 0` AND `v_TGL_FILE_REKON = tanggal_rekon`
   - Group by: `v_GROUP_PRODUK`

5. **Settlement Status**: `t_settle_produk`
   - Filter: `TGL_DATA = tanggal_rekon`

### Validation Rules
Untuk dapat membuat jurnal settlement, semua kondisi berikut harus terpenuhi:
1. `SELISIH = 0` (Amount Detail = Amount Rekap)
2. `JUM_TX_DISPURE = 0` (Tidak ada transaksi dispute)
3. `AMOUNT_TX_DISPURE = 0` (Total amount dispute = 0)
4. `KD_SETTLE` is NULL atau empty (Belum ada jurnal)

## Controller Implementation

### BuatJurnalController.php Updates

#### 1. DataTable Method
```php
// Mapping kolom dari procedure ke format view
$formattedData[] = [
    'NAMA_PRODUK' => $row['NAMA_GROUP'] ?? '',           // Mapping nama kolom
    'FILE_SETTLE' => $row['FILE_SETTLE'] ?? '0',
    'AMOUNT_DETAIL' => $row['AMOUNT_DETAIL'] ?? '0',     // Kolom baru
    'AMOUNT_REKAP' => $row['AMOUNT_REKAP'] ?? '0',       // Kolom baru
    'SELISIH' => $row['SELISIH'] ?? '0',
    'JUM_TX_DISPUTE' => $row['JUM_TX_DISPURE'] ?? '0',   // Mapping nama kolom
    'AMOUNT_TX_DISPUTE' => $row['AMOUNT_TX_DISPURE'] ?? '0', // Kolom baru
    'KD_SETTLE' => $row['KD_SETTLE'] ?? '',
    'CAN_CREATE' => (empty($row['KD_SETTLE']) && $selisih == 0 && $jumTxDispute == 0 && $amountTxDispute == 0) ? 1 : 0
];
```

#### 2. Enhanced Validation
```php
// Validasi yang lebih komprehensif
$selisih = intval(str_replace(',', '', $productData['SELISIH'] ?? '0'));
$jumTxDispute = intval($productData['JUM_TX_DISPURE'] ?? 0);
$amountTxDispute = intval(str_replace(',', '', $productData['AMOUNT_TX_DISPURE'] ?? '0'));

if ($selisih !== 0 || $jumTxDispute !== 0 || $amountTxDispute !== 0) {
    // Return validation errors
}
```

## View Implementation

### Enhanced Table Structure
```html
<th>No</th>
<th>Nama Produk</th>
<th>File Settle</th>
<th>Amount Detail</th>      <!-- NEW -->
<th>Amount Rekap</th>       <!-- NEW -->
<th>Selisih</th>
<th>Jum TX Dispute</th>
<th>Amount TX Dispute</th>  <!-- NEW -->
<th>Kode Settle</th>
<th>Action</th>
```

### DataTable Column Configuration
```javascript
columns: [
    { data: null, render: function(data, type, row, meta) { return meta.row + 1; }},
    { data: 'NAMA_PRODUK' },
    { data: 'FILE_SETTLE', render: function(data) { /* Badge rendering */ }},
    { data: 'AMOUNT_DETAIL', className: 'text-right', render: function(data) { /* Amount formatting */ }},
    { data: 'AMOUNT_REKAP', className: 'text-right', render: function(data) { /* Amount formatting */ }},
    { data: 'SELISIH', className: 'text-right', render: function(data) { /* Selisih with color coding */ }},
    { data: 'JUM_TX_DISPUTE', className: 'text-center', render: function(data) { /* Count with color coding */ }},
    { data: 'AMOUNT_TX_DISPUTE', className: 'text-right', render: function(data) { /* Amount with color coding */ }},
    { data: 'KD_SETTLE', render: function(data) { /* Code formatting */ }},
    { data: 'CAN_CREATE', render: function(data) { /* Action button */ }}
]
```

### Enhanced Modal
Modal sekarang menampilkan informasi yang lebih lengkap:
- Amount Detail
- Amount Rekap  
- Amount TX Dispute
- Validasi yang lebih komprehensif

## CSS Enhancements

### Responsive Design
```css
@media (max-width: 1200px) {
    #buatJurnalTable { font-size: 0.8em; }
}

@media (max-width: 768px) {
    /* Hide middle columns on mobile, keep first and last */
    #buatJurnalTable th:not(:first-child):not(:last-child),
    #buatJurnalTable td:not(:first-child):not(:last-child) {
        display: none;
    }
}
```

### Amount Formatting
```css
.amount-cell {
    font-family: 'Courier New', monospace;
    font-weight: bold;
}

code.text-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
```

## Testing Scenarios

### 1. Data Display Test
- Pastikan semua kolom dari procedure ditampilkan dengan benar
- Verifikasi format amount (dengan pemisah ribuan)
- Cek color coding untuk status (hijau=OK, merah=error)

### 2. Filter Test
- Test filter tanggal
- Test filter file settle (0, 1, 2)
- Verifikasi kombinasi filter

### 3. Validation Test
- Test produk dengan SELISIH != 0
- Test produk dengan JUM_TX_DISPUTE > 0
- Test produk dengan AMOUNT_TX_DISPUTE > 0
- Test produk yang sudah memiliki KD_SETTLE

### 4. Action Button Test
- Button "Create Jurnal" hanya muncul untuk produk yang valid
- Modal menampilkan data lengkap dan akurat
- Validasi real-time sebelum create jurnal

## Production Deployment

### Prerequisites
1. Pastikan procedure `p_compare_rekap` sudah ada di database
2. Verifikasi struktur tabel:
   - `t_agn_detail`
   - `t_agn_settle_pajak`
   - `t_agn_settle_edu`
   - `t_group_settlement`
   - `t_settle_produk`

### Deployment Steps
1. Deploy controller updates
2. Deploy view updates
3. Deploy CSS updates
4. Test dengan data sample
5. Verify all business rules

## Troubleshooting

### Common Issues
1. **Column name mismatch**: Pastikan mapping `NAMA_GROUP` → `NAMA_PRODUK`
2. **Number formatting**: Handle comma-separated values dari procedure
3. **NULL values**: Provide default values untuk semua kolom
4. **Responsive issues**: Test di berbagai ukuran layar

### Debug Tips
```php
// Log procedure results untuk debugging
log_message('info', 'Procedure result: ' . json_encode($allData));

// Validate data format
if (!isset($row['NAMA_GROUP'])) {
    log_message('error', 'NAMA_GROUP not found in procedure result');
}
```

## Summary

Integrasi procedure `p_compare_rekap` berhasil memberikan tampilan data yang komprehensif untuk modul Settlement dengan fitur:

1. **Enhanced Data Display**: 10 kolom informatif
2. **Comprehensive Validation**: 3 kriteria validasi
3. **Responsive Design**: Mobile-friendly interface
4. **Real-time Feedback**: Color-coded status indicators
5. **Professional UI**: Clean, modern interface dengan tooltip dan modal

Implementasi ini memastikan user mendapat informasi lengkap untuk membuat keputusan yang tepat dalam proses settlement jurnal.
