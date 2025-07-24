# CSRF Token Management Update

## Problem
CSRF token kadang expired atau tidak valid saat melakukan AJAX request, menyebabkan aplikasi error.

## Solution Implemented

### 1. Dynamic CSRF Token Management
- CSRF token diambil dari hidden input `#txt_csrfname` yang sudah ada di `app.blade.php`
- Token di-update secara otomatis setelah setiap response AJAX
- Token di-refresh secara berkala (setiap 10 menit) untuk mencegah expiration

### 2. New Controller Method
**File:** `app/Controllers/Rekon/RekonProcess.php`
- `getCSRFToken()` - Endpoint khusus untuk mendapatkan fresh CSRF token
- `checkDate()` - Updated untuk mengembalikan fresh CSRF token di response

### 3. Enhanced JavaScript Functions
**File:** `app/Views/rekon/process/index.blade.php`

#### Key Functions:
- `updateCSRFToken()` - Mendapatkan fresh token dari server
- `updateCSRFTokenInDOM()` - Update semua elemen DOM yang menggunakan CSRF
- `updateFormCSRFToken()` - Update token di form sebelum submit

#### Auto-Management Features:
- Token di-update saat page load
- Token di-refresh setiap 10 menit
- Token di-update sebelum setiap AJAX POST request
- Token di-update setelah response sukses

### 4. Error Handling
- Deteksi error 403/419 (CSRF mismatch)
- Tampilkan tombol refresh jika session expired
- Fallback mechanism untuk token update

### 5. New Route
**File:** `app/Config/Routes.php`
```php
$routes->get('process/getCSRFToken', 'RekonProcess::getCSRFToken', ['as' => 'rekon.process.getCSRFToken']);
```

## Key Features

### Automatic Token Refresh
```javascript
// Update token every 10 minutes
setInterval(updateCSRFToken, 600000);
```

### Global AJAX Token Injection
```javascript
$(document).ajaxSend(function(event, jqxhr, settings) {
    // Automatically add CSRF to all POST requests
});
```

### Response Token Update
```javascript
// Update token from response
if (response.csrf_token && response.csrf_name) {
    updateCSRFTokenInDOM(response.csrf_name, response.csrf_token);
}
```

### Error Recovery
```javascript
// Handle CSRF errors gracefully
if (xhr.status === 403 || xhr.status === 419) {
    // Show refresh option
}
```

## Benefits

1. **Reliability**: CSRF token selalu fresh dan valid
2. **User Experience**: Tidak ada error mendadak karena expired token
3. **Automatic**: Minimal manual intervention required
4. **Robust**: Multiple fallback mechanisms
5. **Compatible**: Menggunakan infrastruktur yang sudah ada

## Implementation Notes

### Server-side Response Format
```json
{
    "success": true,
    "data": "...",
    "csrf_token": "new_hash_value",
    "csrf_name": "csrf_token_name"
}
```

### DOM Elements Updated
- `#txt_csrfname` - Main CSRF hidden input
- Form CSRF inputs
- Meta tags (if exist)
- Any token-related inputs

### Browser Console Logging
Token updates akan terlihat di console untuk debugging:
```
CSRF token updated: csrf_token_name new_hash_value
```

## Usage in Other Parts

Konsep ini dapat diterapkan di bagian lain aplikasi:

```javascript
// Include di layout utama untuk global usage
function globalUpdateCSRF() {
    // Same logic as implemented
}

// Atau buat sebagai jQuery plugin
$.fn.csrfRefresh = function() {
    // Plugin implementation
};
```

## Security Considerations

1. **Token Rotation**: Token berubah setelah setiap request
2. **Timeout Prevention**: Auto-refresh mencegah session timeout
3. **Error Handling**: Graceful degradation saat token invalid
4. **Logging**: Console logging untuk debugging (production bisa di-disable)

## Monitoring

Untuk monitoring, bisa ditambahkan:
- Counter untuk failed CSRF attempts
- Alert jika terlalu banyak token refresh
- Log untuk audit CSRF token usage

---

**Status:** ✅ Implemented and Tested  
**Version:** 1.1  
**Date:** July 24, 2025
