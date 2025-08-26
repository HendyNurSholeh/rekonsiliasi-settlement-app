// Super Simple but Robust CSRF Management
let currentCSRF = window.appConfig?.csrfToken || '';

// Global AJAX setup untuk auto-inject CSRF
$.ajaxSetup({
    beforeSend: function(xhr, settings) {
        // Untuk semua POST request, tambahkan CSRF
        if (settings.type === 'POST') {
            // Jika data adalah FormData
            if (settings.data instanceof FormData) {
                settings.data.append('csrf_test_name', currentCSRF);
            } 
            // Jika data adalah string biasa
            else {
                const separator = settings.data ? '&' : '';
                settings.data = (settings.data || '') + separator + 'csrf_test_name=' + encodeURIComponent(currentCSRF);
            }
        }
    }
});

// Global error handler untuk CSRF expired
$(document).ajaxError(function(event, xhr, settings) {
    if (xhr.status === 403 || xhr.status === 419) {
        console.log('CSRF Token expired, refreshing...');
        refreshCSRFToken().then(function() {
            console.log('CSRF refreshed, retrying request...');
            // Retry the request with new token
            if (!settings._retried) {
                settings._retried = true;
                $.ajax(settings);
            }
        });
    }
});

// Function untuk refresh CSRF token
function refreshCSRFToken() {
    return $.get(window.appConfig.baseUrl + 'get-csrf-token').then(function(response) {
        if (response.csrf_token) {
            currentCSRF = response.csrf_token;
            console.log('New CSRF token:', currentCSRF);
        }
    }).catch(function(error) {
        console.error('Failed to refresh CSRF:', error);
        // Fallback: reload page if can't refresh token
        setTimeout(function() {
            if (confirm('Session expired. Reload page?')) {
                location.reload();
            }
        }, 1000);
    });
}

// DataTable instance
let compareTable;

$(document).ready(function() {
    // Refresh CSRF token saat page load untuk memastikan token fresh
    refreshCSRFToken().then(function() {
        console.log('CSRF token refreshed on page load');
        
        // Initialize DataTable dengan AJAX
        initializeDataTable();
        
        // Load initial statistics
        updateStatistics();
    });
    
    // Handle form submit for filters
    $('form').on('submit', function(e) {
        e.preventDefault();
        const tanggal = $('#tanggal').val();
        const filterSelisih = $('#filter_selisih').val();
        
        console.log('Form submit - Tanggal:', tanggal);
        console.log('Form submit - Filter Selisih:', filterSelisih);
        
        if (tanggal) {
            // Update current URL parameters
            const url = new URL(window.location);
            url.searchParams.set('tanggal', tanggal);
            if (filterSelisih !== '') {
                url.searchParams.set('filter_selisih', filterSelisih);
            } else {
                url.searchParams.delete('filter_selisih');
            }
            window.history.pushState({}, '', url);
            
            console.log('Updated URL:', url.toString());
            
            // Update statistics
            updateStatistics();
            
            // Reload DataTable with new filters
            if (compareTable) {
                compareTable.ajax.reload();
            }
        }
    });
    
    // Handle filter selisih change
    $('#filter_selisih').on('change', function() {
        const tanggal = $('#tanggal').val();
        const filterSelisih = $(this).val();
        
        console.log('Filter selisih changed - Tanggal:', tanggal);
        console.log('Filter selisih changed - Filter Selisih:', filterSelisih);
        
        if (tanggal) {
            // Update current URL parameters
            const url = new URL(window.location);
            url.searchParams.set('tanggal', tanggal);
            if (filterSelisih !== '') {
                url.searchParams.set('filter_selisih', filterSelisih);
            } else {
                url.searchParams.delete('filter_selisih');
            }
            window.history.pushState({}, '', url);
            
            console.log('Updated URL from filter change:', url.toString());
            
            // Update statistics
            updateStatistics();
            
            // Reload DataTable with new filters
            if (compareTable) {
                compareTable.ajax.reload();
            }
        }
    });
});

function initializeDataTable() {
    compareTable = $('#compareTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.appConfig.baseUrl + "rekon/process/detail-vs-rekap/datatable",
            type: 'GET',
            data: function(d) {
                // Add current filters
                d.tanggal = $('#tanggal').val() || window.appConfig.tanggalData;
                d.filter_selisih = $('#filter_selisih').val();
                console.log('DataTable request data:', d);
                console.log('Filter Selisih:', d.filter_selisih);
                return d;
            },
            error: function(xhr, error, thrown) {
                console.error('DataTable AJAX Error:', error, thrown, xhr.responseText);
                if (xhr.status === 403 || xhr.status === 419) {
                    console.log('CSRF error in DataTable, refreshing token...');
                    refreshCSRFToken().then(function() {
                        console.log('CSRF refreshed, reloading DataTable...');
                        compareTable.ajax.reload();
                    });
                }
            }
        },
        columns: [
            { 
                data: null,
                name: 'no',
                orderable: false,
                searchable: false,
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { data: 'NAMA_GROUP', name: 'NAMA_GROUP' },
            { 
                data: 'FILE_SETTLE', 
                name: 'FILE_SETTLE',
                render: function(data, type, row) {
                    const fileSettle = parseInt(data || 0);
                    if (fileSettle === 0) {
                        return '<span class="badge badge-primary">Detail</span>';
                    } else {
                        return '<span class="badge badge-info">Rekap</span>';
                    }
                }
            },
            { 
                data: 'AMOUNT_DETAIL', 
                name: 'AMOUNT_DETAIL',
                className: 'text-end',
                render: function(data, type, row) {
                    const amount = parseFloat(String(data || 0).replace(/,/g, ''));
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
                }
            },
            { 
                data: 'AMOUNT_REKAP', 
                name: 'AMOUNT_REKAP',
                className: 'text-end',
                render: function(data, type, row) {
                    const amount = parseFloat(String(data || 0).replace(/,/g, ''));
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
                }
            },
            { 
                data: 'SELISIH', 
                name: 'SELISIH',
                className: 'text-end',
                render: function(data, type, row) {
                    const amount = parseFloat(String(data || 0).replace(/,/g, ''));
                    const isNonZero = amount !== 0;
                    const className = isNonZero ? 'text-danger fw-bold' : 'text-success';
                    return '<span class="' + className + '">Rp ' + new Intl.NumberFormat('id-ID').format(amount) + '</span>';
                }
            }
        ],
        pageLength: 25,
        lengthMenu: [[25, 50, 100, 200], [25, 50, 100, 200]],
        order: [[1, 'asc']],
        language: {
            processing: "Memuat data...",
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            },
            emptyTable: "Tidak ada data yang tersedia",
            zeroRecords: "Tidak ditemukan data yang sesuai"
        },
        responsive: true,
        searching: false,
        dom: '<"row"<"col-sm-12">>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-5"i><"col-sm-7"p>>'
    });
}

function formatNumber(num) {
    // Convert string to number first, removing any existing commas
    const cleanNum = parseFloat(String(num).replace(/,/g, '')) || 0;
    return new Intl.NumberFormat('id-ID').format(cleanNum);
}

// Function to update statistics via AJAX
function updateStatistics() {
    const tanggal = $('#tanggal').val();
    const filterSelisih = $('#filter_selisih').val();
    
    console.log('Updating statistics for tanggal:', tanggal, 'filter:', filterSelisih);
    
    // Show loading spinners
    $('#stat-total').html('<i class="fal fa-spinner fa-spin"></i>');
    $('#stat-ada-selisih').html('<i class="fal fa-spinner fa-spin"></i>');
    $('#stat-tidak-ada-selisih').html('<i class="fal fa-spinner fa-spin"></i>');
    $('#stat-akurasi').html('<i class="fal fa-spinner fa-spin"></i>');
    
    // Make AJAX request to get statistics
    $.ajax({
        url: window.appConfig.baseUrl + "rekon/process/detail-vs-rekap/statistics",
        type: 'GET',
        data: {
            tanggal: tanggal,
            filter_selisih: filterSelisih
        },
        success: function(response) {
            console.log('Statistics response:', response);
            
            if (response.success) {
                const stats = response.data;
                
                // Update statistics display
                $('#stat-total').text(stats.total);
                $('#stat-ada-selisih').text(stats.ada_selisih);
                $('#stat-tidak-ada-selisih').text(stats.tidak_ada_selisih);
                $('#stat-akurasi').text(stats.akurasi + '%');
            } else {
                console.error('Statistics error:', response.message);
                // Show error state
                $('#stat-total').text('0');
                $('#stat-ada-selisih').text('0');
                $('#stat-tidak-ada-selisih').text('0');
                $('#stat-akurasi').text('0%');
            }
        },
        error: function(xhr, status, error) {
            console.error('Statistics AJAX error:', error, xhr.responseText);
            
            // Show error state
            $('#stat-total').text('0');
            $('#stat-ada-selisih').text('0');
            $('#stat-tidak-ada-selisih').text('0');
            $('#stat-akurasi').text('0%');
            
            // Handle CSRF errors
            if (xhr.status === 403 || xhr.status === 419) {
                console.log('CSRF error in statistics, refreshing token...');
                refreshCSRFToken().then(function() {
                    console.log('CSRF refreshed, retrying statistics...');
                    updateStatistics(); // Retry
                });
            }
        }
    });
}

function resetFilters() {
    $('#tanggal').val(window.appConfig.tanggalData);
    $('#filter_selisih').val('');
    
    // Update URL params
    const url = new URL(window.location);
    url.searchParams.set('tanggal', window.appConfig.tanggalData);
    url.searchParams.delete('filter_selisih');
    window.history.pushState({}, '', url);
    
    // Update statistics
    updateStatistics();
    
    // Reload DataTable if exists
    if (typeof compareTable !== 'undefined' && compareTable) {
        compareTable.ajax.reload();
    }
}

function showAlert(type, message) {
    let alertClass = 'alert-info';
    let icon = 'fa-info-circle';
    
    switch(type) {
        case 'success':
            alertClass = 'alert-success';
            icon = 'fa-check-circle';
            break;
        case 'error':
            alertClass = 'alert-danger';
            icon = 'fa-exclamation-circle';
            break;
        case 'warning':
            alertClass = 'alert-warning';
            icon = 'fa-exclamation-triangle';
            break;
    }
    
    let alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fal ${icon}"></i> ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;
    
    $('.subheader').after(alertHtml);
    
    // Auto hide success alerts
    if (type === 'success') {
        setTimeout(function() {
            $('.alert-success').fadeOut();
        }, 3000);
    }
}