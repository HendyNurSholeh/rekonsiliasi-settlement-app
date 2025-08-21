# Routes BiFast - Documentation

## 📁 File Location
```
app/Config/Routes/rekon_bifast.php
```

## 🎯 Untuk Developer BiFast

File ini khusus untuk pengembangan fitur **Rekonsiliasi BiFast**. Anda dapat menambahkan routes baru tanpa mengganggu routes utama aplikasi.

## 🔧 Controller yang Diperlukan

Pastikan Anda membuat controller berikut:
```
app/Controllers/RekonBifast/RekonBifastController.php
```

## 📝 Routes yang Tersedia

### Main Routes
- `GET /rekon-bifast/rekap` - Halaman utama rekap BiFast
- `POST /rekon-bifast/upload` - Upload file BiFast
- `GET /rekon-bifast/monitoring` - Monitoring proses
- `GET /rekon-bifast/laporan` - Halaman laporan
- `POST /rekon-bifast/proses` - Proses rekonsiliasi

### DataTable Routes
- `GET /rekon-bifast/datatable` - DataTable data
- `POST /rekon-bifast/datatable` - DataTable dengan filter

### API Routes
- `GET /rekon-bifast/api/status` - Status API
- `POST /rekon-bifast/api/validate` - Validasi data
- `POST /rekon-bifast/api/export` - Export data
- `GET /rekon-bifast/api/dashboard-stats` - Statistik dashboard

### Report Routes
- `GET /rekon-bifast/report/daily` - Laporan harian
- `GET /rekon-bifast/report/summary` - Laporan summary
- `POST /rekon-bifast/report/export-excel` - Export Excel
- `POST /rekon-bifast/report/export-pdf` - Export PDF

### Configuration Routes
- `GET /rekon-bifast/config` - Halaman konfigurasi
- `POST /rekon-bifast/config/update` - Update konfigurasi

## 🚀 Cara Menambahkan Routes Baru

Buka file `app/Config/Routes/rekon_bifast.php` dan tambahkan di dalam group yang sesuai:

```php
// Contoh menambahkan route baru
$routes->get('dashboard', 'RekonBifastController::dashboard', ['as' => 'rekon-bifast.dashboard']);
$routes->post('import-bulk', 'RekonBifastController::importBulk', ['as' => 'rekon-bifast.import-bulk']);
```

## 🔄 Git Workflow

Ketika mengerjakan fitur BiFast:
1. Edit hanya file `app/Config/Routes/rekon_bifast.php`
2. Tidak perlu menyentuh file `app/Config/Routes.php` utama
3. Mengurangi conflict saat merge

```bash
# Commit hanya file BiFast
git add app/Config/Routes/rekon_bifast.php
git add app/Controllers/RekonBifast/
git add app/Views/rekon_bifast/
git commit -m "Add BiFast feature routes and controllers"
```

## ⚠️ Notes

- Namespace controller: `App\Controllers\RekonBifast`
- Route prefix: `/rekon-bifast/`
- Named routes prefix: `rekon-bifast.`
- File akan otomatis di-load oleh `app/Config/Routes.php`
