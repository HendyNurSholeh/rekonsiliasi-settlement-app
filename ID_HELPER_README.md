# ID Helper Documentation

## Overview
Helper untuk encrypt/decrypt ID dalam URL agar lebih aman dan tidak mudah ditebak.

## Features
- ✅ URL-Safe encoding (tidak ada karakter bermasalah)
- ✅ Salt validation untuk keamanan
- ✅ Error handling yang robust
- ✅ Helper functions yang mudah digunakan

## Functions

### 1. `encryptId($id)`
Encrypt ID menjadi string yang aman untuk URL.

```php
$id = 123;
$encrypted = encryptId($id);
// Output: MTIzfFRTSS1VTkRFUkxZSU5HLURF...
```

### 2. `decryptId($encrypted)`
Decrypt string kembali ke ID asli.

```php
$encrypted = "MTIzfFRTSS1VTkRFUkxZSU5HLURF...";
$id = decryptId($encrypted);
// Output: 123 (atau null jika invalid)
```

### 3. `generateSecureUrl($baseUrl, $id)`
Generate URL lengkap dengan encrypted ID.

```php
$url = generateSecureUrl('https://example.com/transaction', 123);
// Output: https://example.com/transaction/MTIzfFRTSS1VTkRFUkxZSU5HLURF...
```

### 4. `createTransactionUrl($underlyingId)`
Helper khusus untuk URL transaction.

```php
$url = createTransactionUrl(123);
// Output: http://yoursite.com/transaction/MTIzfFRTSS1VTkRFUkxZSU5HLURF...
```

### 5. `validateEncryptedId($encrypted)`
Validasi apakah string adalah encrypted ID yang valid.

```php
$isValid = validateEncryptedId($encrypted);
// Output: true/false
```

## Usage in Controllers

### UnderlyingController.php
```php
// Generate secure transaction URL
$transactionUrl = createTransactionUrl($item['ID']);
```

### TransactionController.php
```php
public function index($encryptedId)
{
    // Decrypt ID
    $id = decryptId($encryptedId);
    
    if (!$id) {
        throw new \CodeIgniter\Exceptions\PageNotFoundException('Invalid URL');
    }
    
    // Continue with normal logic...
}
```

## Routes Configuration
```php
// Accept encrypted ID (not just numbers)
$routes->get('/transaction/([A-Za-z0-9\-_]+)', 'User\TransactionController::index/$1');
```

## Security Notes
- Salt: `TSI-UNDERLYING-DEVISA-2024` (dapat diganti sesuai kebutuhan)
- Format: `ID|SALT|RANDOM_HASH` untuk mencegah predictable patterns
- URL-Safe: Menggunakan `-` dan `_` sebagai pengganti `+` dan `/`
- Validation: Multiple layers untuk memastikan data valid

## Example URLs
- **Sebelum:** `/transaction/123`
- **Sesudah:** `/transaction/MTIzfFRTSS1VTkRFUkxZSU5HLURF...`

## Error Handling
- Function akan return `null` jika input invalid
- Log error untuk debugging (jika diperlukan)
- Graceful fallback untuk security
