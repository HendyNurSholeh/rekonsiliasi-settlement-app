# CSS Organization Documentation

## Overview
All CSS styles from blade views have been extracted and organized into separate CSS files for better maintainability and performance.

## Structure
```
public/css/rekon/
├── common.css                  # Shared styles across all rekon modules
├── persiapan/
│   ├── index.css              # Setup page styles
│   ├── step1.css              # Upload step styles
│   ├── step2.css              # Validation step styles
│   └── step3.css              # Process step styles
└── process/
    ├── detail_vs_rekap.css    # Detail vs rekap report styles
    ├── direct_jurnal_rekap.css # Direct jurnal rekap styles
    ├── dispute_resolution.css  # Dispute resolution styles
    ├── indirect_dispute.css   # Indirect dispute styles
    ├── indirect_jurnal_rekap.css # Indirect jurnal rekap styles
    └── konfirmasi_saldo_ca.css   # Konfirmasi saldo CA styles
```

## Benefits
1. **Maintainability**: CSS is separated from HTML/Blade templates
2. **Performance**: CSS files can be cached and minified
3. **Reusability**: Common styles are shared via common.css
4. **Organization**: Styles are grouped by functionality
5. **Scalability**: Easy to add new styles for new features

## Usage
Each view now imports its specific CSS file using:
```blade
@push('styles')
<link rel="stylesheet" href="{{ base_url('css/rekon/path/to/specific.css') }}">
@endpush
```

## Common Styles
The `common.css` file contains frequently used styles:
- Badge variations
- Table styling
- Card styling
- Text utilities
- Form styling
- Button groups
- Modal sizing
- Background colors
- Spacing utilities
- Loading animations

## Implementation Notes
- All CSS files use relative imports for common.css where applicable
- Blade views now only contain minimal inline styles for dynamic content
- CSS classes maintain Bootstrap 4/5 compatibility
- File naming follows the view structure for easy mapping

## Migration Completed
✅ All Persiapan views (index, step1, step2, step3)
✅ All Process views (detail_vs_rekap, direct_jurnal_rekap, dispute_resolution, indirect_dispute, indirect_jurnal_rekap, konfirmasi_saldo_ca)
✅ Common styles extracted and optimized
✅ Import structure established
