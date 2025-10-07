# JurnalCaEscrowController - Optimization Plan

## 🎯 Optimasi yang Direkomendasikan

### ✅ **1. Extract Parameter Parsing** (High Priority)
**Problem:** Repetitive parameter handling di `datatable()`
```php
// Sebelum (Repetitive):
$tanggalData = $this->request->getGet('tanggal') ?? $this->request->getPost('tanggal') ?? $this->prosesModel->getDefaultDate();
$draw = $this->request->getGet('draw') ?? $this->request->getPost('draw') ?? 1;
$start = $this->request->getGet('start') ?? $this->request->getPost('start') ?? 0;
```

**Solution:** Extract ke helper method
```php
private function getRequestParam(string $key, $default = null)
{
    return $this->request->getGet($key) ?? $this->request->getPost($key) ?? $default;
}

// Usage:
$tanggalData = $this->getRequestParam('tanggal', $this->prosesModel->getDefaultDate());
$draw = $this->getRequestParam('draw', 1);
$start = $this->getRequestParam('start', 0);
```

---

### ✅ **2. Extract Search Logic** (High Priority)
**Problem:** Search logic embedded di `datatable()` (25+ lines)

**Solution:** Extract ke private method
```php
private function filterDataBySearch(array $data, string $searchValue): array
{
    if (empty($searchValue)) {
        return $data;
    }
    
    $searchLower = strtolower($searchValue);
    
    return array_values(array_filter($data, function($row) use ($searchLower) {
        return $this->matchesSearch($row, $searchLower);
    }));
}

private function matchesSearch(array $row, string $searchLower): bool
{
    // Parent match
    $parentMatch = str_contains(strtolower($row['r_KD_SETTLE'] ?? ''), $searchLower) ||
                   str_contains(strtolower($row['r_NAMA_PRODUK'] ?? ''), $searchLower);
    
    if ($parentMatch) return true;
    
    // Child match
    return $this->hasMatchingChild($row['child_rows'] ?? [], $searchLower);
}
```

---

### ✅ **3. Extract Sorting Logic** (Medium Priority)
**Problem:** Sorting logic di `datatable()` (15+ lines)

**Solution:** Extract ke private method
```php
private function applySorting(array &$data, int $columnIndex, string $direction): void
{
    $sortColumns = [
        1 => 'r_KD_SETTLE',
        2 => 'r_NAMA_PRODUK',
        3 => 'r_AMOUNT_ESCROW',
        4 => 'r_TOTAL_JURNAL',
        5 => 'r_JURNAL_PENDING',
        6 => 'r_JURNAL_SUKSES'
    ];
    
    if (!isset($sortColumns[$columnIndex])) return;
    
    $sortKey = $sortColumns[$columnIndex];
    usort($data, fn($a, $b) => $this->compareValues($a[$sortKey] ?? '', $b[$sortKey] ?? '', $direction));
}
```

---

### ✅ **4. Extract Row Formatting** (High Priority)
**Problem:** Parent/child formatting di `datatable()` (50+ lines)

**Solution:** Extract ke private methods
```php
private function formatParentRow(array $parentRow, array $processStatus): array
{
    return [
        ...array_map(fn($k) => $parentRow[$k] ?? ($k === 'r_AMOUNT_ESCROW' ? '0' : ''), [
            'r_KD_SETTLE', 'r_NAMA_PRODUK', 'r_AMOUNT_ESCROW', 
            'r_TOTAL_JURNAL', 'r_JURNAL_PENDING', 'r_JURNAL_SUKSES'
        ]),
        'child_count' => count($parentRow['child_rows']),
        'is_parent' => true,
        'has_children' => !empty($parentRow['child_rows']),
        ...$this->extractProcessStatus($processStatus),
        ...$this->getEmptyChildFields()
    ];
}

private function formatChildRow(array $childRow, string $parentKdSettle): array
{
    return [
        ...$this->getEmptyParentFields(),
        'is_parent' => false,
        'has_children' => false,
        'parent_kd_settle' => $parentKdSettle,
        ...$this->extractChildFields($childRow)
    ];
}
```

---

### ✅ **5. Use Array Spread Operator** (Low Priority)
**Problem:** Repetitive array assignments

**Solution:** Use `...` operator
```php
// Sebelum:
$formattedParent = [
    'r_KD_SETTLE' => $parentRow['r_KD_SETTLE'] ?? '',
    'r_NAMA_PRODUK' => $parentRow['r_NAMA_PRODUK'] ?? '',
    'r_AMOUNT_ESCROW' => $parentRow['r_AMOUNT_ESCROW'] ?? '0',
    // ... 15 more lines
];

// Sesudah:
$formattedParent = [
    ...$this->extractParentFields($parentRow),
    ...$this->extractProcessStatus($processStatus),
    ...$this->getEmptyChildFields()
];
```

---

### ✅ **6. Extract Constants** (Low Priority)
**Problem:** Magic numbers/strings

**Solution:** Define class constants
```php
private const DEFAULT_PAGE_LENGTH = 15;
private const SORTABLE_COLUMNS = [
    1 => 'r_KD_SETTLE',
    2 => 'r_NAMA_PRODUK',
    3 => 'r_AMOUNT_ESCROW',
    4 => 'r_TOTAL_JURNAL',
    5 => 'r_JURNAL_PENDING',
    6 => 'r_JURNAL_SUKSES'
];
```

---

### ✅ **7. Simplify getErrorMessagesForKdSettle** (Low Priority)
**Problem:** Could use array_column

**Solution:** Use array_column for mapping
```php
$errorMap = array_column($results, 'response_message', 'kd_settle');
```

---

### ✅ **8. Extract JSON Response Builder** (Medium Priority)
**Problem:** Repetitive JSON response building

**Solution:** Create helper methods
```php
private function jsonSuccess(array $data): ResponseInterface
{
    return $this->response->setJSON([...$data, 'csrf_token' => csrf_hash()]);
}

private function jsonError(string $message, int $statusCode = 500): ResponseInterface
{
    return $this->response->setJSON([
        'success' => false,
        'message' => $message,
        'csrf_token' => csrf_hash()
    ])->setStatusCode($statusCode);
}
```

---

### ⚠️ **9. Move Search to Service** (Optional - Long term)
**Problem:** Search logic di controller

**Solution:** Create JurnalCaEscrowService
```php
class JurnalCaEscrowService
{
    public function getDataTable(array $params): array
    {
        // All datatable logic here
    }
    
    public function searchData(array $data, string $search): array
    {
        // Search logic
    }
    
    public function sortData(array $data, int $column, string $dir): array
    {
        // Sort logic
    }
}
```

---

## 📊 Impact Analysis

| Optimasi | Priority | Lines Saved | Readability | Testability |
|----------|----------|-------------|-------------|-------------|
| Extract Parameters | ⭐⭐⭐ | ~10 lines | ⬆️⬆️⬆️ | ⬆️⬆️ |
| Extract Search | ⭐⭐⭐ | ~30 lines | ⬆️⬆️⬆️ | ⬆️⬆️⬆️ |
| Extract Sorting | ⭐⭐ | ~15 lines | ⬆️⬆️ | ⬆️⬆️ |
| Extract Formatting | ⭐⭐⭐ | ~50 lines | ⬆️⬆️⬆️⬆️ | ⬆️⬆️⬆️ |
| Array Spread | ⭐ | ~20 lines | ⬆️ | ⬆️ |
| Constants | ⭐ | ~5 lines | ⬆️⬆️ | ⬆️ |
| JSON Helpers | ⭐⭐ | ~15 lines | ⬆️⬆️ | ⬆️⬆️ |
| Move to Service | ⭐ | ~100 lines | ⬆️⬆️⬆️⬆️⬆️ | ⬆️⬆️⬆️⬆️⬆️ |

**Total Potential:** ~145 lines reduction, Huge readability improvement

---

## 🚀 Implementation Order (Recommended)

1. ✅ Extract Parameters (5 min)
2. ✅ Extract Formatting (15 min)
3. ✅ Extract Search (10 min)
4. ✅ Extract Sorting (10 min)
5. ✅ Constants (5 min)
6. ✅ JSON Helpers (5 min)
7. ⏭️ Move to Service (Optional, 30+ min)

**Total Time:** ~50 minutes for major improvements

---

## ✨ Expected Result

### Before:
- `datatable()`: ~180 lines
- Complexity: Very High
- Readability: ⭐⭐
- Testability: ⭐⭐

### After:
- `datatable()`: ~80 lines
- Complexity: Medium
- Readability: ⭐⭐⭐⭐⭐
- Testability: ⭐⭐⭐⭐

---

## 📝 Notes

- **Don't over-optimize**: Keep balance between clean code and over-engineering
- **Test after each change**: Ensure functionality tidak berubah
- **Consider long-term**: Service layer untuk scalability
- **Keep it simple**: Jangan sacrifice readability untuk "clever" code
