# Permission Naming Convention - Log Menu

## 📋 Overview
Dokumentasi ini menjelaskan standar penamaan permission untuk menu Log agar konsisten dan mudah dipahami.

## ✅ Naming Convention

### Pattern: `view log [nama]`

**Format**: `view log` + `[nama submenu]`

### Contoh Implementasi:

| Submenu | Permission Name | Variable Name | Description |
|---------|----------------|---------------|-------------|
| **Activity** | `view log activity` | `$activity` | Log aktivitas user |
| **Error** | `view log error` | `$error` | Log error sistem |
| **Akselgate** | `view log akselgate` | `$log_akselgate` | Log transaksi Akselgate API |

---

## 🔧 Implementation

### 1. Permission Check (app.blade.php)

```php
// Log permissions
$activity = in_array('view activity', $permissions);
$error = in_array('view error', $permissions);
$log_akselgate = in_array('view log akselgate', $permissions ?? []) ?? true;

// Show Log menu if at least one submenu is accessible
$show_log = $activity || $error || $log_akselgate;
```

### 2. Menu Display Logic

```php
@if ($show_log)
    <li class="@if ($route == 'log/activity' || $route == 'log/error' || $route == 'log/akselgate') active open @endif">
        <a href="javascript:void(0);" title="Log" data-filter-tags="log">
            <i class="fal fa-shield-alt"></i>
            <span class="nav-link-text text-left">Log</span>
        </a>
        <ul>
            @if ($activity)
                <li class="@if ($route == 'log/activity') active open @endif">
                    <a href="{{ site_url('log/activity') }}">
                        <span class="nav-link-text text-left">Activity</span>
                    </a>
                </li>
            @endif
            @if ($error)
                <li class="@if ($route == 'log/error') active open @endif">
                    <a href="{{ site_url('log/error') }}">
                        <span class="nav-link-text text-left">Error</span>
                    </a>
                </li>
            @endif
            @if ($log_akselgate)
                <li class="@if ($route == 'log/akselgate') active open @endif">
                    <a href="{{ site_url('log/akselgate') }}">
                        <span class="nav-link-text text-left">Akselgate</span>
                    </a>
                </li>
            @endif
        </ul>
    </li>
@endif
```

---

## 📊 Permission Comparison

### ❌ Before (Inconsistent):
```php
$activity = in_array('view activity', $permissions);           // ✅ OK
$error = in_array('view error', $permissions);                 // ✅ OK
$akselgate_log = in_array('view akselgate log', $permissions); // ❌ Inconsistent
```

**Issues**:
- `view akselgate log` tidak mengikuti pola yang sama
- Variable name `$akselgate_log` tidak konsisten dengan `$activity` dan `$error`

### ✅ After (Consistent):
```php
$activity = in_array('view activity', $permissions);          // ✅ Consistent
$error = in_array('view error', $permissions);                // ✅ Consistent
$log_akselgate = in_array('view log akselgate', $permissions); // ✅ Consistent
```

**Benefits**:
- Semua permission mengikuti pola `view [menu] [submenu]`
- Variable name lebih deskriptif (`$log_akselgate`)
- Mudah untuk menambahkan submenu baru di masa depan

---

## 🎯 Design Principles

### 1. **Consistency**
Semua permission di menu yang sama mengikuti pola yang sama:
- Menu: `view [parent_menu]`
- Submenu: `view [parent_menu] [submenu_name]`

### 2. **Clarity**
Permission name harus jelas menunjukkan hierarki menu:
```
Log (parent)
├── Activity   → view log activity
├── Error      → view log error
└── Akselgate  → view log akselgate
```

### 3. **Scalability**
Mudah menambahkan submenu baru:
```php
// Add new log type
$log_database = in_array('view log database', $permissions ?? []) ?? true;
$log_api = in_array('view log api', $permissions ?? []) ?? true;

// Update menu visibility
$show_log = $activity || $error || $log_akselgate || $log_database || $log_api;
```

---

## 📝 Future Additions

Jika ingin menambahkan submenu log baru, ikuti pattern ini:

### Example: Log Database

**Step 1**: Add permission check
```php
$log_database = in_array('view log database', $permissions ?? []) ?? true;
```

**Step 2**: Update menu visibility
```php
$show_log = $activity || $error || $log_akselgate || $log_database;
```

**Step 3**: Add menu item
```php
@if ($log_database)
    <li class="@if ($route == 'log/database') active open @endif">
        <a href="{{ site_url('log/database') }}">
            <span class="nav-link-text text-left">Database</span>
        </a>
    </li>
@endif
```

---

## 🔍 Verification Checklist

Saat menambahkan permission baru, pastikan:

- [ ] Permission name mengikuti pattern `view log [nama]`
- [ ] Variable name deskriptif dan konsisten
- [ ] Permission ditambahkan ke database (table permissions)
- [ ] Permission di-assign ke role yang sesuai
- [ ] Menu display logic updated (`$show_log`)
- [ ] Active state handling di sidebar updated
- [ ] Route protection dengan middleware/filter

---

## 📚 Related Menus

Pattern yang sama digunakan di menu lain:

### Settlement Menu
```php
$settlement_buat_jurnal = in_array('view settlement buat jurnal', $permissions);
$settlement_approve_jurnal = in_array('view settlement approve jurnal', $permissions);
$settlement_jurnal_ca_escrow = in_array('view settlement jurnal ca escrow', $permissions);
```

### Rekonsiliasi Menu
```php
$rekon_pilih_tanggal = in_array('view rekon pilih tanggal', $permissions);
$rekon_review_data = in_array('view rekon review data', $permissions);
$rekon_detail_vs_rekap = in_array('view rekon detail vs rekap', $permissions);
```

---

## 📋 Database Schema

### Table: `permissions`

**Example Records**:
```sql
INSERT INTO permissions (name, display_name, description) VALUES
('view log activity', 'View Log Activity', 'Akses untuk melihat log aktivitas user'),
('view log error', 'View Log Error', 'Akses untuk melihat log error sistem'),
('view log akselgate', 'View Log Akselgate', 'Akses untuk melihat log transaksi Akselgate API');
```

---

## 🎨 Variable Naming Best Practices

### Pattern:
- Parent menu: `$show_[menu_name]`
- Submenu: `$[menu_name]_[submenu_name]` atau `$[submenu_name]`

### Examples:

**Good** ✅
```php
$log_activity = in_array('view log activity', $permissions);
$log_error = in_array('view log error', $permissions);
$log_akselgate = in_array('view log akselgate', $permissions);
$show_log = $log_activity || $log_error || $log_akselgate;
```

**Alternative (Also Good)** ✅
```php
$activity = in_array('view log activity', $permissions);
$error = in_array('view log error', $permissions);
$akselgate = in_array('view log akselgate', $permissions);
$show_log = $activity || $error || $akselgate;
```

**Not Good** ❌
```php
$aksgate_log = in_array('view akselgate log', $permissions); // Typo in variable
$logAkselgate = in_array('view log akselgate', $permissions); // camelCase not consistent
$AKSELGATE_LOG = in_array('view log akselgate', $permissions); // SCREAMING_CASE not appropriate
```

---

**Last Updated**: October 8, 2025  
**Version**: 1.0.0  
**Status**: ✅ Implemented & Standardized
