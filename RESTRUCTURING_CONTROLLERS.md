# Restructuring Controller Documentation

## Struktur Baru Controller

Dengan memisahkan controller berdasarkan modul/halaman, struktur menjadi lebih bersih dan mudah di-maintain:

```
app/Controllers/
├── CommonController.php                # Fungsi umum untuk seluruh aplikasi (CSRF, health check)
└── Rekon/
    ├── CommonController.php            # Fungsi umum khusus untuk modul Rekon
    └── Process/
        ├── DetailVsRekapController.php      # Laporan Detail vs Rekap
        ├── DirectJurnalController.php       # Direct Jurnal (Rekap + Dispute)
        └── IndirectJurnalController.php     # Indirect Jurnal (Rekap + Dispute + Konfirmasi Saldo CA)
```

## Detail Controller dan Method

### 1. DetailVsRekapController
**Namespace**: `App\Controllers\Rekon\Process\DetailVsRekapController`
**Methods**:
- `index()` - Halaman utama detail vs rekap
- `datatable()` - AJAX endpoint untuk DataTable

### 2. DirectJurnalController  
**Namespace**: `App\Controllers\Rekon\Process\DirectJurnalController`
**Methods**:
- `rekap()` - Halaman rekap tx direct jurnal
- `dispute()` - Halaman penyelesaian dispute direct jurnal  
- `getDisputeDetail()` - Get detail dispute untuk modal
- `updateDispute()` - Update data dispute
- `disputeDataTable()` - AJAX endpoint untuk DataTable dispute

### 3. IndirectJurnalController
**Namespace**: `App\Controllers\Rekon\Process\IndirectJurnalController`
**Methods**:
- `rekap()` - Halaman rekap tx indirect jurnal
- `rekapDataTable()` - AJAX endpoint untuk DataTable rekap
- `konfirmasiSetoran()` - Konfirmasi setoran
- `dispute()` - Halaman penyelesaian dispute indirect jurnal
- `disputeDataTable()` - AJAX endpoint untuk DataTable dispute  
- `getIndirectDisputeDetail()` - Get detail dispute untuk modal
- `updateIndirectDispute()` - Update data dispute indirect
- `konfirmasiSaldoCA()` - Halaman konfirmasi saldo CA

### 4. CommonController (Root Level)
**Namespace**: `App\Controllers\CommonController`
**Methods**:
- `getCsrfToken()` - Get CSRF token untuk AJAX (dapat digunakan di seluruh aplikasi)
- `healthCheck()` - Health check endpoint

### 5. CommonController (Rekon Level)
**Namespace**: `App\Controllers\Rekon\CommonController`
**Methods**:
- `getCsrfToken()` - Get CSRF token khusus untuk modul Rekon (jika diperlukan fungsi khusus)

## Perubahan yang Diperlukan

### 1. Update Routes (app/Config/Routes.php)

```php
// Detail vs Rekap
$routes->get('rekon/process/detail-vs-rekap', 'Rekon\Process\DetailVsRekapController::index');
$routes->get('rekon/process/detail-vs-rekap/datatable', 'Rekon\Process\DetailVsRekapController::datatable');

// Direct Jurnal  
$routes->get('rekon/process/direct-jurnal-rekap', 'Rekon\Process\DirectJurnalController::rekap');
$routes->get('rekon/process/penyelesaian-dispute', 'Rekon\Process\DirectJurnalController::dispute');
$routes->get('rekon/process/dispute/detail', 'Rekon\Process\DirectJurnalController::getDisputeDetail');
$routes->post('rekon/process/dispute/update', 'Rekon\Process\DirectJurnalController::updateDispute');
$routes->get('rekon/process/dispute/datatable', 'Rekon\Process\DirectJurnalController::disputeDataTable');

// Indirect Jurnal
$routes->get('rekon/process/indirect-jurnal-rekap', 'Rekon\Process\IndirectJurnalController::rekap');
$routes->get('rekon/process/indirect-jurnal-rekap/datatable', 'Rekon\Process\IndirectJurnalController::rekapDataTable');
$routes->post('rekon/process/konfirmasi-setoran', 'Rekon\Process\IndirectJurnalController::konfirmasiSetoran');
$routes->get('rekon/process/indirect-dispute', 'Rekon\Process\IndirectJurnalController::dispute');
$routes->get('rekon/process/indirect-dispute/datatable', 'Rekon\Process\IndirectJurnalController::disputeDataTable');
$routes->get('rekon/process/indirect-dispute/detail', 'Rekon\Process\IndirectJurnalController::getIndirectDisputeDetail');
$routes->post('rekon/process/indirect-dispute/update', 'Rekon\Process\IndirectJurnalController::updateIndirectDispute');
$routes->get('rekon/process/konfirmasi-saldo-ca', 'Rekon\Process\IndirectJurnalController::konfirmasiSaldoCA');

// Common (Application Wide)
$routes->get('common/csrf-token', 'CommonController::getCsrfToken');
$routes->get('common/health-check', 'CommonController::healthCheck');

// Common (Rekon Specific) 
$routes->get('rekon/common/csrf-token', 'Rekon\CommonController::getCsrfToken');
```

### 2. Update URL di View Files

Perlu update URL di file-file view berikut:
- `detail_vs_rekap.blade.php` 
- `dispute_resolution.blade.php`
- `indirect_dispute.blade.php`
- `indirect_jurnal_rekap.blade.php`

### 3. Update Menu di Layout (app.blade.php)

Menu sudah sesuai, tidak perlu perubahan karena menggunakan site_url() yang akan mengikuti routes baru.

## Manfaat Restructuring

### 1. **Separation of Concerns**
- Setiap controller fokus pada satu modul/halaman
- Mudah untuk menambah fitur baru tanpa mengubah controller lain
- Lebih mudah debugging karena scope yang lebih kecil

### 2. **Better Maintainability**  
- Code lebih mudah dibaca dan dipahami
- Perubahan di satu modul tidak mempengaruhi modul lain
- Testing lebih mudah karena isolated

### 3. **Scalability**
- Mudah menambah controller baru untuk fitur baru
- Dapat menambahkan middleware khusus per controller
- Performance lebih baik karena tidak load method yang tidak diperlukan

### 4. **Team Development**
- Developer dapat bekerja pada modul berbeda tanpa conflict
- Code review lebih fokus dan mudah
- Lebih mudah assign task per modul

## Next Steps

1. ✅ Buat struktur controller baru
2. ⏳ Update routes di `app/Config/Routes.php`
3. ⏳ Update URL di view files
4. ⏳ Test semua fitur masih berfungsi
5. ⏳ Hapus controller lama setelah semua berfungsi
6. ⏳ Update dokumentasi dan guide untuk tim

## File yang Dapat Dihapus Setelah Migration

Setelah semua berfungsi dengan baik:
- `app/Controllers/Rekon/RekonProcessController.php` (controller lama)
