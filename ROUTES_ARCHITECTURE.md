# Routes Architecture Documentation

## 📁 Struktur Routes Modular

Aplikasi ini menggunakan struktur routes yang dipisahkan berdasarkan modul untuk memudahkan kolaborasi tim dan maintenance.

```
app/Config/Routes/
├── core.php              # Core application routes
├── rekonsiliasi.php      # Rekonsiliasi settlement routes  
├── settlement.php        # Settlement & transfer dana routes
├── user_management.php   # User management routes
└── rekon_bifast.php      # Rekon BiFast routes (terpisah untuk kolaborasi)
```

## 🎯 Pembagian Tanggung Jawab

### 📄 core.php
**Tim Core/Infrastructure**
- Authentication (Login/Logout)
- Dashboard  
- Profile Management
- CSRF Token Management
- Common utility routes

### 📄 rekonsiliasi.php
**Tim Rekonsiliasi**
- Setup & Persiapan (Setup, Step1, Step2, Step3)
- Process Rekonsiliasi 
- Laporan Transaksi Detail
- Detail vs Rekap
- Direct/Indirect Jurnal
- Dispute Management

### 📄 settlement.php  
**Tim Settlement**
- Buat Jurnal Settlement
- Approve Jurnal Settlement
- Transfer CA to Escrow (via AKSEL Gateway)
- Transfer Escrow to Biller PL
- AKSEL Gateway Callback

### 📄 user_management.php
**Tim User Management**
- Unit Kerja Management
- Role & Permission Management
- User CRUD Operations
- Log Viewer (Error & Activity)

### 📄 rekon_bifast.php
**Developer BiFast (Tim Eksternal)**
- Rekonsiliasi BiFast
- Monitoring & Laporan BiFast
- API BiFast
- Konfigurasi BiFast

## 🚀 Cara Kerja Tim

### Workflow Kolaborasi:

```bash
# Developer A (Core Team)
git checkout -b feature/settlement-improvement
# Edit hanya: app/Config/Routes/settlement.php
git add app/Config/Routes/settlement.php
git commit -m "Add new settlement features"

# Developer B (BiFast Team)  
git checkout -b feature/bifast-integration
# Edit hanya: app/Config/Routes/rekon_bifast.php
git add app/Config/Routes/rekon_bifast.php
git commit -m "Add BiFast integration"

# Merge tanpa conflict! 🎉
```

### Keuntungan:

✅ **Zero Conflict**: Setiap tim bekerja pada file berbeda  
✅ **Clear Ownership**: Jelas siapa yang bertanggung jawab  
✅ **Easy Maintenance**: Mudah debug dan maintain per modul  
✅ **Scalable**: Mudah menambah modul baru  
✅ **Parallel Development**: Tim bisa bekerja paralel tanpa ganggu  

## 📋 Konvensi Penamaan

### Route Names:
- **Core**: `login`, `dashboard`, `user.profile`
- **Rekonsiliasi**: `rekon.*` (rekon.index, rekon.step1, dll)
- **Settlement**: `settlement.*` (settlement.buat-jurnal, dll)
- **User Management**: `user.index`, `role.index`, `permission.index`
- **BiFast**: `rekon-bifast.*` (rekon-bifast.rekap, dll)

### URL Patterns:
- **Core**: `/login`, `/dashboard`, `/profile`
- **Rekonsiliasi**: `/rekon/*`
- **Settlement**: `/settlement/*` 
- **User Management**: `/user`, `/role`, `/permission`, `/log/*`
- **BiFast**: `/rekon-bifast/*`

## 🔧 Menambahkan Routes Baru

### 1. Edit file routes yang sesuai:
```php
// Contoh di settlement.php
$routes->post('settlement/new-feature', 'SettlementController::newFeature', ['as' => 'settlement.new-feature']);
```

### 2. Commit hanya file yang diubah:
```bash
git add app/Config/Routes/settlement.php
git commit -m "Add new settlement feature route"
```

### 3. Tidak perlu edit Routes.php utama!

## ⚠️ Important Notes

1. **Jangan edit `app/Config/Routes.php` utama** kecuali untuk perubahan struktur besar
2. **Gunakan namespace yang konsisten** di setiap file routes
3. **Tambahkan komentar yang jelas** untuk routes baru
4. **Test routes** setelah menambahkan fitur baru
5. **Koordinasi dengan tim** jika ada perubahan yang mempengaruhi modul lain

## 🆘 Troubleshooting

### Route tidak ditemukan?
1. Cek namespace controller di file routes
2. Pastikan controller file ada di lokasi yang benar
3. Clear route cache: `php spark route:clear`

### Conflict saat merge?
1. Pastikan hanya edit file routes yang sesuai tanggung jawab
2. Jika terpaksa edit file yang sama, koordinasi dengan tim
3. Gunakan Git merge tools untuk resolve conflict

---

**Happy Coding! 🚀**
