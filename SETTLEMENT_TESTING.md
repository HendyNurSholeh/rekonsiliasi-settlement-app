# SETTLEMENT MODULE - TESTING GUIDE

## TESTING CHECKLIST

### 1. BUAT JURNAL SETTLEMENT

#### 1.1 Akses Halaman
- [ ] Buka `http://localhost:8080/settlement/buat-jurnal`
- [ ] Pastikan menu "Settlement" > "Buat Jurnal Settlement" aktif
- [ ] Pastikan halaman load dengan benar

#### 1.2 Filter Testing
- [ ] Test filter tanggal - pilih tanggal yang berbeda
- [ ] Test filter file settle (Default/Pajak/Edu)
- [ ] Test kombinasi filter
- [ ] Test reset filter
- [ ] Pastikan URL parameters ter-update

#### 1.3 DataTable Testing
- [ ] Pastikan data ter-load dari procedure `p_compare_rekap`
- [ ] Test pagination (jika data > 10 rows)
- [ ] Test sorting untuk setiap kolom
- [ ] Test search functionality
- [ ] Pastikan number formatting untuk Selisih

#### 1.4 Create Jurnal Testing
- [ ] Pastikan tombol "Create Jurnal" hanya muncul jika KD_SETTLE = NULL
- [ ] Test validasi: SELISIH harus = 0
- [ ] Test validasi: JUM_TX_DISPUTE harus = 0
- [ ] Test modal konfirmasi
- [ ] Test proses create jurnal
- [ ] Pastikan table refresh setelah create
- [ ] Pastikan tombol hilang setelah jurnal dibuat

#### 1.5 Expected Data Structure dari p_compare_rekap
```sql
CALL p_compare_rekap('2025-07-21');
-- Expected columns:
-- NAMA_PRODUK, FILE_SETTLE, SELISIH, JUM_TX_DISPUTE, KD_SETTLE
```

### 2. APPROVE JURNAL SETTLEMENT

#### 2.1 Akses Halaman
- [ ] Buka `http://localhost:8080/settlement/approve-jurnal`
- [ ] Pastikan menu "Settlement" > "Approve Jurnal Settlement" aktif
- [ ] Pastikan halaman load dengan benar

#### 2.2 Summary Cards Testing
- [ ] Pastikan 4 summary cards ter-load: Total, Approved, Rejected, Pending
- [ ] Test refresh summary dengan tombol "Refresh Summary"
- [ ] Pastikan angka sesuai dengan data di table

#### 2.3 Filter Testing
- [ ] Test filter tanggal settlement
- [ ] Test filter status approval (Semua/Pending/Disetujui/Ditolak)
- [ ] Test kombinasi filter
- [ ] Test reset filter

#### 2.4 DataTable Testing
- [ ] Pastikan data ter-load dari table `t_settle_produk`
- [ ] Test pagination dan sorting
- [ ] Test search functionality
- [ ] Pastikan currency formatting untuk Total Amount
- [ ] Pastikan badge status dengan warna yang benar

#### 2.5 Approval Modal Testing
- [ ] Test tombol "Approve" untuk jurnal dengan status pending
- [ ] Test tombol "Lihat Detail" untuk jurnal yang sudah diproses
- [ ] Pastikan modal menampilkan data dari `tamp_settle_message`
- [ ] Test detail table dengan data yang benar
- [ ] Test tombol "Setujui" dan "Tolak"
- [ ] Pastikan konfirmasi sebelum approve/reject

#### 2.6 Expected Data Structure
```sql
-- Table t_settle_produk
SELECT id, KD_SETTLE, NAMA_PRODUK, TGL_SETTLE, TOTAL_AMOUNT, 
       STATUS_APPROVE, USER_APPROVE, TGL_APPROVE
FROM t_settle_produk 
WHERE DATE(TGL_SETTLE) = '2025-07-21';

-- Detail jurnal
SELECT JENIS_SETTLE, IDPARTNER, CORE, DEBIT_ACCOUNT, DEBIT_NAME, 
       CREDIT_CORE, CREDIT_ACCOUNT, CREDIT_NAME, AMOUNT, DESCRIPTION, REF_NUMBER
FROM tamp_settle_message 
WHERE KD_SETTLE = 'SAMPLE_CODE';
```

## MANUAL TESTING SCENARIOS

### Scenario 1: Create New Settlement Journal
1. Pilih tanggal rekonsiliasi yang memiliki data
2. Filter produk dengan SELISIH = 0 dan JUM_TX_DISPUTE = 0
3. Klik "Create Jurnal" pada produk yang eligible
4. Konfirmasi di modal
5. Verify jurnal berhasil dibuat
6. Check bahwa KD_SETTLE sudah ter-generate

### Scenario 2: Approve Settlement Journal
1. Buka halaman Approve Jurnal
2. Filter jurnal dengan status pending
3. Klik "Approve" pada salah satu jurnal
4. Review detail di modal
5. Klik "Setujui"
6. Verify status berubah menjadi "Disetujui"
7. Check summary cards ter-update

### Scenario 3: Reject Settlement Journal
1. Pilih jurnal dengan status pending
2. Klik "Approve" untuk lihat detail
3. Klik "Tolak"
4. Konfirmasi rejection
5. Verify status berubah menjadi "Ditolak"

## BROWSER TESTING

### Desktop Testing
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Edge (latest)
- [ ] Safari (if available)

### Mobile Testing
- [ ] Chrome Mobile
- [ ] Safari Mobile
- [ ] Responsive design check

### Tablet Testing
- [ ] iPad view
- [ ] Android tablet view

## PERFORMANCE TESTING

### DataTable Performance
- [ ] Test dengan 100+ records
- [ ] Test dengan 1000+ records
- [ ] Check loading time
- [ ] Check memory usage

### AJAX Performance
- [ ] Monitor network requests
- [ ] Check response times
- [ ] Verify CSRF token management

## SECURITY TESTING

### CSRF Testing
- [ ] Test dengan expired CSRF token
- [ ] Test automatic token refresh
- [ ] Test manual token manipulation

### Input Validation
- [ ] Test SQL injection attempts
- [ ] Test XSS attempts
- [ ] Test invalid date formats
- [ ] Test negative amounts

### Authorization Testing
- [ ] Test dengan user yang tidak memiliki permission
- [ ] Test session timeout
- [ ] Test concurrent sessions

## ERROR HANDLING TESTING

### Database Errors
- [ ] Test dengan database connection error
- [ ] Test dengan missing procedures
- [ ] Test dengan corrupt data

### Network Errors
- [ ] Test dengan slow connection
- [ ] Test dengan intermittent connection
- [ ] Test dengan offline mode

### User Error Scenarios
- [ ] Test dengan invalid dates
- [ ] Test dengan invalid selections
- [ ] Test dengan empty forms

## ACCESSIBILITY TESTING

### Keyboard Navigation
- [ ] Test tab navigation
- [ ] Test Enter key submissions
- [ ] Test Escape key for modals

### Screen Reader
- [ ] Test dengan screen reader software
- [ ] Check aria labels
- [ ] Check semantic HTML

## INTEGRATION TESTING

### Database Integration
- [ ] Test procedure calls
- [ ] Test transaction handling
- [ ] Test data consistency

### Session Integration
- [ ] Test user session management
- [ ] Test permission checks
- [ ] Test logout scenarios

## DEPLOYMENT TESTING

### Pre-deployment
- [ ] Check all files uploaded
- [ ] Check database migrations
- [ ] Check permission configurations

### Post-deployment
- [ ] Test pada production environment
- [ ] Check error logs
- [ ] Monitor performance metrics

## BUG REPORTING TEMPLATE

```
**Bug Title**: [Brief description]

**Environment**: 
- Browser: [Chrome/Firefox/etc]
- OS: [Windows/Mac/Linux]
- URL: [Full URL where bug occurred]

**Steps to Reproduce**:
1. Step 1
2. Step 2
3. Step 3

**Expected Result**: 
[What should happen]

**Actual Result**: 
[What actually happened]

**Screenshots**: 
[Attach screenshots if applicable]

**Additional Notes**: 
[Any other relevant information]
```

## ACCEPTANCE CRITERIA

### Must Have ✅
- [ ] Dapat membuat jurnal settlement dengan validasi
- [ ] Dapat approve/reject jurnal settlement
- [ ] DataTable berfungsi dengan pagination dan search
- [ ] Filter berfungsi untuk semua kombinasi
- [ ] Modal approval menampilkan detail jurnal
- [ ] Summary statistics akurat
- [ ] Responsive design

### Should Have ✅
- [ ] CSRF protection berfungsi
- [ ] Error handling yang graceful
- [ ] Loading states untuk better UX
- [ ] Success/error notifications
- [ ] Proper validation messages

### Nice to Have 🎯
- [ ] Smooth animations
- [ ] Custom styling sesuai design
- [ ] Advanced filtering options
- [ ] Export functionality
- [ ] Real-time updates
