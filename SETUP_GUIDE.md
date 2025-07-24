# Setup Rekonsiliasi Settlement App

## 🚀 Quick Setup

### 1. Database Setup
Jalankan file `database_setup.sql` di database MySQL:
```sql
-- Buka database_setup.sql dan jalankan di phpMyAdmin atau MySQL client
```

### 2. Stored Procedures yang Diperlukan
- `p_process_persiapan(tanggal, is_reset)` - Persiapan proses rekonsiliasi  
- `p_proses_dataupload(tanggal)` - Proses upload dan validasi data

### 3. Flow Aplikasi (Sederhana)
1. **Pilih Tanggal** → Buat proses baru atau reset yang sudah ada
2. **Upload Files** → Upload 4 jenis file settlement
3. **Validasi** → Sistem validasi otomatis
4. **Proses** → Panggil stored procedure untuk proses data

### 4. File Penting
- `app/Controllers/Rekon/RekonProcess.php` - Controller utama
- `app/Models/ProsesModel.php` - Model database  
- `app/Views/rekon/process/index.blade.php` - Halaman utama
- `app/Views/rekon/process/step1.blade.php` - Upload files

### 5. URL Routes
- `/rekon/process` - Halaman utama buat proses
- `/rekon/process/step1?tanggal=2025-07-24` - Upload files

### 6. Fitur Reset
Jika tanggal sudah ada proses:
1. Klik "Reset & Lanjutkan" 
2. Konfirmasi dengan dialog JavaScript
3. Panggil `p_process_persiapan(tanggal, 1)` untuk reset

### 7. Upload Files
4 jenis file yang bisa diupload:
- Agregator Detail (wajib)
- Settlement Education (wajib)  
- Settlement Pajak (wajib)
- M-Gate Payment Gateway (opsional)

---
**Note**: Kode sudah disederhanakan sesuai instruksi. Fokus pada fungsi utama saja.
