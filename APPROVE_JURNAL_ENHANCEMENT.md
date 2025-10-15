# Approve Jurnal Settlement Enhancement

## Overview
Dokumentasi enhancement untuk halaman Approve Jurnal Settlement dengan penambahan validasi net amount dan penggabungan kolom user approve.

---

## Changes Summary

### 1. **Penggabungan Kolom User & Tanggal Approve**
- **Before:** 2 kolom terpisah (`User Approver` dan `Tanggal Approve`)
- **After:** 1 kolom gabungan (`User Approve`)
  - Format: `USERNAME (dd/mm/yyyy HH:mm)`
  - Contoh: `ADMIN (21/10/2025 14:30)`
  - Empty jika belum di-approve

**Benefit:**
- ✅ Lebih ringkas dan clean
- ✅ Informasi tetap lengkap (user + waktu)
- ✅ Menghemat space di tabel

---

### 2. **Penambahan Kolom Net Amount**
- **Kolom Baru:**
  1. `Net Amount Debet` - Dari field `AMOUNT_NET_DB_ECR`
  2. `Net Amount Credit` - Dari field `AMOUNT_NET_KR_ECR`

- **Visual Indicator:**
  - ✅ **Hijau** jika net amount debet = credit (match)
  - ❌ **Merah Bold** jika net amount debet ≠ credit (tidak match)

**Purpose:**
- Validasi bahwa transaksi balanced (debet = credit)
- Visual warning jika ada selisih

---

### 3. **Status Approval Enhancement**

#### **Status Code:**
| Code | Deskripsi | Badge | Keterangan |
|------|-----------|-------|------------|
| `-1` | Tidak Bisa Approve | 🔴 `Net Amount Beda` | Net debet ≠ credit |
| `0` | Belum Approve | ⚪ `Belum Approve` | Initial state |
| `1` | Sudah Disetujui | 🟢 `Disetujui` | Approved |
| `9` | Ditolak | 🟠 `Ditolak` | Rejected |
| `NULL` | Pending | 🟡 `Pending` | Not processed |

#### **Status Logic:**
```php
// Controller logic
if (!$netMatch && ($status === null || $status === '' || $status === '0')) {
    $effectiveStatus = '-1'; // Force status -1 if net doesn't match
} else {
    $effectiveStatus = $status; // Use actual status
}
```

---

### 4. **Approve Button Logic**

#### **Button Disabled When:**
1. ❌ Net amount debet ≠ credit (`NET_MATCH = false`)
2. ❌ Already approved (`STAT_APPROVER = '1'`)
3. ❌ Already rejected (`STAT_APPROVER = '9'`)
4. ❌ Status is -1 (net beda)

#### **Button Enabled When:**
✅ Net amount match AND status is pending/null/0

#### **Visual States:**
```javascript
// Disabled button (grey)
<button class="btn btn-secondary" disabled title="Tidak bisa approve: Net amount tidak sama">
    <i class="fal fa-check-circle"></i> Approve
</button>

// Enabled button (blue)
<button class="btn btn-primary" title="Klik untuk approve">
    <i class="fal fa-check-circle"></i> Approve
</button>
```

---

### 5. **Modal Enhancement**

#### **New Fields in Modal:**
- **Net Amount Debet** (readonly)
- **Net Amount Credit** (readonly)
- **Warning Alert** (conditionally shown)

#### **Warning Alert:**
```html
<!-- Shown only if net doesn't match -->
<div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle"></i> 
    <strong>Perhatian!</strong> Net amount debet dan credit tidak sama. 
    Selisih: Rp 1.000.000. 
    Approval tidak dapat dilakukan.
</div>
```

#### **Approval Buttons Visibility:**
```javascript
// Hide buttons if:
// 1. Net doesn't match
// 2. Already approved/rejected
// 3. Status is -1

if (!settleInfo.NET_MATCH || 
    status === '1' || 
    status === '9' || 
    status === '-1') {
    $('#approvalButtons').hide();
} else {
    $('#approvalButtons').show();
}
```

---

## File Changes

### 1. **Controller: `ApproveJurnalController.php`**

#### **Modified Method: `datatable()`**

**Query Changes:**
```php
// OLD:
SELECT id, KD_SETTLE, NAMA_PRODUK, TGL_DATA, TOT_JURNAL_KR_ECR, 
       STAT_APPROVER, USER_APPROVER, TGL_APPROVER

// NEW:
SELECT id, KD_SETTLE, NAMA_PRODUK, TGL_DATA, TOT_JURNAL_KR_ECR, 
       AMOUNT_NET_DB_ECR, AMOUNT_NET_KR_ECR,
       STAT_APPROVER, USER_APPROVER, TGL_APPROVER
```

**Data Processing:**
```php
// Calculate net match
$netDebet = floatval($row['AMOUNT_NET_DB_ECR'] ?? 0);
$netCredit = floatval($row['AMOUNT_NET_KR_ECR'] ?? 0);
$netMatch = (abs($netDebet - $netCredit) < 0.01); // Floating point tolerance

// Determine effective status
if (!$netMatch && ($status === null || $status === '' || $status === '0')) {
    $effectiveStatus = '-1';
} else {
    $effectiveStatus = $status;
}

// Combine user and date
$approvalInfo = '';
if (!empty($userApprover) && !empty($tglApprover)) {
    $approvalInfo = $userApprover . ' (' . date('d/m/Y H:i', strtotime($tglApprover)) . ')';
}

// Response data
return [
    'AMOUNT_NET_DB_ECR' => $netDebet,
    'AMOUNT_NET_KR_ECR' => $netCredit,
    'NET_MATCH' => $netMatch,
    'STAT_APPROVER' => $effectiveStatus,
    'APPROVAL_INFO' => $approvalInfo
];
```

#### **Modified Method: `getDetailJurnal()`**

**Query Changes:**
```php
// OLD:
SELECT NAMA_PRODUK, TGL_DATA, TOT_JURNAL_KR_ECR

// NEW:
SELECT NAMA_PRODUK, TGL_DATA, TOT_JURNAL_KR_ECR, 
       AMOUNT_NET_DB_ECR, AMOUNT_NET_KR_ECR
```

**Additional Data:**
```php
// Calculate net match
$netDebet = floatval($settleInfo['AMOUNT_NET_DB_ECR'] ?? 0);
$netCredit = floatval($settleInfo['AMOUNT_NET_KR_ECR'] ?? 0);
$netMatch = (abs($netDebet - $netCredit) < 0.01);

$settleInfo['NET_MATCH'] = $netMatch;
$settleInfo['NET_DIFF'] = $netDebet - $netCredit;
```

---

### 2. **View: `_data_table.blade.php`**

**Table Header Changes:**
```html
<!-- OLD -->
<th>User Approver</th>
<th>Tanggal Approve</th>

<!-- NEW -->
<th>Net Amount Debet</th>
<th>Net Amount Credit</th>
<th>Status Approval</th>
<th>User Approve</th> <!-- Combined column -->
```

---

### 3. **JavaScript: `approve-jurnal.js`**

#### **DataTable Columns Changes:**

**Column 4 - Net Amount Debet:**
```javascript
{
    data: 'AMOUNT_NET_DB_ECR',
    render: function(data, type, row) {
        const formatted = formatCurrency(data);
        if (!row.NET_MATCH) {
            return '<span class="text-danger font-weight-bold">' + formatted + '</span>';
        }
        return '<span class="text-success">' + formatted + '</span>';
    }
}
```

**Column 5 - Net Amount Credit:**
```javascript
{
    data: 'AMOUNT_NET_KR_ECR',
    render: function(data, type, row) {
        const formatted = formatCurrency(data);
        if (!row.NET_MATCH) {
            return '<span class="text-danger font-weight-bold">' + formatted + '</span>';
        }
        return '<span class="text-success">' + formatted + '</span>';
    }
}
```

**Column 6 - Status (Enhanced):**
```javascript
{
    data: 'STAT_APPROVER',
    render: function(data) {
        if (data === '-1') {
            return '<span class="badge badge-danger">Net Amount Beda</span>';
        } else if (data === '1') {
            return '<span class="badge badge-success">Disetujui</span>';
        } else if (data === '9') {
            return '<span class="badge badge-warning">Ditolak</span>';
        } else if (data === '0') {
            return '<span class="badge badge-secondary">Belum Approve</span>';
        } else {
            return '<span class="badge text-white" style="background-color: #f9911b;">Pending</span>';
        }
    }
}
```

**Column 7 - Approval Info (Combined):**
```javascript
{
    data: 'APPROVAL_INFO',
    render: function(data) {
        return data || '-';
    }
}
```

**Column 8 - Action Button:**
```javascript
{
    data: null,
    render: function(data, type, row) {
        const isDisabled = !row.NET_MATCH || 
                          row.STAT_APPROVER === '1' || 
                          row.STAT_APPROVER === '9' || 
                          row.STAT_APPROVER === '-1';
        
        const btnClass = isDisabled ? 'btn-secondary' : 'btn-primary';
        const disabledAttr = isDisabled ? 'disabled' : '';
        
        const title = !row.NET_MATCH ? 
                     'Tidak bisa approve: Net amount tidak sama' : 
                     row.STAT_APPROVER === '1' ? 'Sudah disetujui' :
                     row.STAT_APPROVER === '9' ? 'Sudah ditolak' : 
                     'Klik untuk approve';
        
        return '<button type="button" class="btn btn-sm ' + btnClass + ' btn-view-detail" ' +
               'data-kd-settle="' + row.KD_SETTLE + '" ' +
               'data-net-match="' + row.NET_MATCH + '" ' +
               disabledAttr + ' title="' + title + '">' +
               '<i class="fal fa-check-circle"></i> Approve</button>';
    }
}
```

#### **Modal Function Enhancement:**

**`openApprovalModal()` Changes:**
```javascript
// Early exit if net doesn't match
if (netMatch === false || netMatch === 'false') {
    toastr["error"]('Tidak bisa approve: Net amount tidak sama');
    return;
}

// Display net amounts
$('#modal_net_debet').val(formatCurrency(settleInfo.AMOUNT_NET_DB_ECR || 0));
$('#modal_net_credit').val(formatCurrency(settleInfo.AMOUNT_NET_KR_ECR || 0));

// Show warning if net doesn't match
if (!settleInfo.NET_MATCH) {
    const diff = Math.abs(settleInfo.NET_DIFF || 0);
    $('#netWarning').html(
        '<div class="alert alert-danger">' +
        '<i class="fas fa-exclamation-triangle"></i> ' +
        '<strong>Perhatian!</strong> Net amount debet dan credit tidak sama. ' +
        'Selisih: ' + formatCurrency(diff) + '. ' +
        'Approval tidak dapat dilakukan.' +
        '</div>'
    ).show();
} else {
    $('#netWarning').hide();
}

// Hide approval buttons if net doesn't match or already processed
if (!settleInfo.NET_MATCH || 
    status === '1' || 
    status === '9' || 
    status === '-1') {
    $('#approvalButtons').hide();
} else {
    $('#approvalButtons').show();
}
```

---

### 4. **View: `_modal.blade.php`**

**New Fields:**
```html
<div class="col-md-2">
    <div class="form-group">
        <label>Net Amount Debet</label>
        <input type="text" class="form-control" id="modal_net_debet" readonly>
    </div>
</div>
<div class="col-md-2">
    <div class="form-group">
        <label>Net Amount Credit</label>
        <input type="text" class="form-control" id="modal_net_credit" readonly>
    </div>
</div>
```

**Warning Container:**
```html
<div class="row">
    <div class="col-12">
        <div id="netWarning" style="display: none;"></div>
    </div>
</div>
```

---

## Database Requirements

### **Required Fields in `t_settle_produk`:**
```sql
ALTER TABLE t_settle_produk 
ADD COLUMN AMOUNT_NET_DB_ECR DECIMAL(18,2) DEFAULT 0 COMMENT 'Net amount debet dari ECR',
ADD COLUMN AMOUNT_NET_KR_ECR DECIMAL(18,2) DEFAULT 0 COMMENT 'Net amount credit dari ECR';

-- Update status untuk yang net tidak match
UPDATE t_settle_produk 
SET STAT_APPROVER = '-1'
WHERE (STAT_APPROVER IS NULL OR STAT_APPROVER = '' OR STAT_APPROVER = '0')
  AND ABS(AMOUNT_NET_DB_ECR - AMOUNT_NET_KR_ECR) > 0.01;
```

### **Status Field Values:**
```sql
-- Update STAT_APPROVER field type if needed
ALTER TABLE t_settle_produk 
MODIFY COLUMN STAT_APPROVER VARCHAR(2) COMMENT '-1:net beda, 0:belum approve, 1:sudah approve, 9:reject';
```

---

## Testing Checklist

### **1. Display Testing:**
- [ ] Net Amount Debet column shows correct values
- [ ] Net Amount Credit column shows correct values
- [ ] Net amount colors (green/red) display correctly
- [ ] User Approve column shows combined format: `USER (dd/mm/yyyy HH:mm)`
- [ ] Status badge shows correct color and text for each status code

### **2. Button State Testing:**
- [ ] Button disabled when net amount doesn't match
- [ ] Button disabled when already approved (status = 1)
- [ ] Button disabled when already rejected (status = 9)
- [ ] Button disabled when status = -1
- [ ] Button enabled when net match AND status is pending
- [ ] Button tooltip shows correct message

### **3. Modal Testing:**
- [ ] Net Amount Debet displays in modal
- [ ] Net Amount Credit displays in modal
- [ ] Warning alert shows when net doesn't match
- [ ] Warning alert shows correct difference amount
- [ ] Approval buttons hidden when net doesn't match
- [ ] Approval buttons hidden when already processed
- [ ] Approval buttons shown when eligible for approval

### **4. Functional Testing:**
- [ ] Cannot open modal if net doesn't match (with error toast)
- [ ] Can view detail but cannot approve if net doesn't match
- [ ] Approve process works normally when net matches
- [ ] Reject process works normally
- [ ] Status updates correctly after approval/rejection
- [ ] DataTable refreshes after approval/rejection

### **5. Edge Cases:**
- [ ] Floating point comparison (0.01 tolerance) works correctly
- [ ] NULL values handled properly
- [ ] Empty strings handled properly
- [ ] Very small differences (<0.01) treated as match
- [ ] Very large amounts display correctly with formatting

---

## User Flow Examples

### **Scenario 1: Net Amount Match - Happy Path**
1. User opens page → sees data with **green** net amounts
2. Status shows **"Pending"** (orange badge)
3. Approve button is **enabled** (blue)
4. Click Approve → modal opens
5. Net amounts shown, **no warning alert**
6. Approval buttons visible
7. Click "Setujui" → success
8. Status changes to **"Disetujui"** (green badge)
9. User Approve column shows: `ADMIN (21/10/2025 14:30)`
10. Button becomes disabled (grey)

---

### **Scenario 2: Net Amount Mismatch - Blocked**
1. User opens page → sees data with **red bold** net amounts
2. Status shows **"Net Amount Beda"** (red badge)
3. Approve button is **disabled** (grey)
4. Click Approve → **error toast** shows
5. Modal **does not open**
6. User cannot proceed with approval

---

### **Scenario 3: View Detail with Net Mismatch**
1. User manually accesses modal (if implemented)
2. Modal opens with net amounts displayed
3. **Red alert warning** shown:
   ```
   ⚠ Perhatian! Net amount debet dan credit tidak sama. 
   Selisih: Rp 1.000.000. 
   Approval tidak dapat dilakukan.
   ```
4. Approval buttons **hidden**
5. User can only view details, cannot approve

---

### **Scenario 4: Already Approved**
1. User opens page
2. Status shows **"Disetujui"** (green badge)
3. User Approve shows: `ADMIN (21/10/2025 14:30)`
4. Approve button is **disabled** (grey)
5. Tooltip: "Sudah disetujui"
6. Cannot reprocess

---

## Benefits Summary

### **1. Data Integrity** ✅
- Prevents approval of unbalanced transactions
- Automatic detection of debet ≠ credit
- Visual indicators for quick identification

### **2. User Experience** ✅
- Clear visual feedback (colors, badges)
- Informative error messages
- Disabled buttons prevent invalid actions
- Combined user/date column saves space

### **3. Audit Trail** ✅
- Complete approval info (user + timestamp)
- Status history maintained
- Cannot override net mismatch approval

### **4. Business Logic** ✅
- Enforces accounting principle (debet = credit)
- Prevents data corruption
- Clear status progression

---

## Migration Guide

### **Step 1: Database Migration**
```sql
-- Add new columns
ALTER TABLE t_settle_produk 
ADD COLUMN IF NOT EXISTS AMOUNT_NET_DB_ECR DECIMAL(18,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS AMOUNT_NET_KR_ECR DECIMAL(18,2) DEFAULT 0;

-- Update status field
ALTER TABLE t_settle_produk 
MODIFY COLUMN STAT_APPROVER VARCHAR(2);

-- Backfill data if needed
UPDATE t_settle_produk 
SET AMOUNT_NET_DB_ECR = (SELECT SUM(AMOUNT) FROM tamp_settle_message WHERE ...),
    AMOUNT_NET_KR_ECR = (SELECT SUM(AMOUNT) FROM tamp_settle_message WHERE ...);
```

### **Step 2: Deploy Code**
1. Backup existing files
2. Deploy controller changes
3. Deploy view changes
4. Deploy JavaScript changes
5. Clear browser cache

### **Step 3: Testing**
1. Test with matched net amounts
2. Test with mismatched net amounts
3. Test approval flow
4. Test rejection flow
5. Verify status updates

### **Step 4: User Training**
1. Inform users about new columns
2. Explain status codes
3. Train on new approval logic
4. Document troubleshooting steps

---

## Troubleshooting

### **Issue: Button not disabled when net doesn't match**
**Solution:**
- Check `NET_MATCH` field in response
- Verify floating point comparison (0.01 tolerance)
- Check JavaScript button render logic

### **Issue: Warning alert not showing in modal**
**Solution:**
- Verify `NET_MATCH` and `NET_DIFF` in settle_info
- Check `#netWarning` element exists
- Verify JavaScript show/hide logic

### **Issue: Status not updating to -1**
**Solution:**
- Check controller logic for status determination
- Verify database query includes net amount fields
- Ensure comparison logic is correct

### **Issue: Approval Info not formatted correctly**
**Solution:**
- Verify `USER_APPROVER` and `TGL_APPROVER` not null
- Check date formatting in controller
- Verify JavaScript doesn't override format

---

## Conclusion

Enhancement ini menambahkan:
✅ **Validasi Net Amount** - Debet harus sama dengan Credit  
✅ **Visual Indicators** - Color coding untuk quick identification  
✅ **Business Logic Enforcement** - Cannot approve unbalanced transactions  
✅ **Better UX** - Combined columns, clear status, disabled buttons  
✅ **Data Integrity** - Prevents accounting errors  

**Status Codes:** `-1` (net beda) | `0` (belum) | `1` (approved) | `9` (reject)

---

**Document Version:** 1.0  
**Last Updated:** 2025-10-15  
**Author:** GitHub Copilot  
**Feature:** Approve Jurnal Enhancement  
