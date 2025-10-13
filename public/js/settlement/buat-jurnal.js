// CSRF Management
let currentCSRF = window.appConfig?.csrfToken || '';

$.ajaxSetup({
    beforeSend: function(xhr, settings) {
        if (settings.type === 'POST') {
            if (settings.data instanceof FormData) {
                settings.data.append('csrf_test_name', currentCSRF);
            } else {
                const separator = settings.data ? '&' : '';
                settings.data = (settings.data || '') + separator + 'csrf_test_name=' + encodeURIComponent(currentCSRF);
            }
        }
    }
});

$(document).ajaxError(function(event, xhr, settings) {
    if (xhr.status === 403 || xhr.status === 419) {
        console.log('CSRF Token expired, refreshing...');
        refreshCSRFToken().then(function() {
            console.log('CSRF refreshed, retrying request...');
            if (!settings._retried) {
                settings._retried = true;
                $.ajax(settings);
            }
        });
    }
});

function refreshCSRFToken() {
    return $.get(window.appConfig.baseUrl + "get-csrf-token").then(function(response) {
        if (response.csrf_token) {
            currentCSRF = response.csrf_token;
            console.log('New CSRF token:', currentCSRF);
        }
    }).catch(function(error) {
        console.error('Failed to refresh CSRF:', error);
        setTimeout(function() {
            if (confirm('Session expired. Reload page?')) {
                location.reload();
            }
        }, 1000);
    });
}

// DataTable instance
let buatJurnalTable;
let currentProductData = null;

$(document).ready(function() {
    // Refresh CSRF token saat page load
    refreshCSRFToken().then(function() {
        console.log('CSRF token refreshed on page load');
        initializeDataTable();
    });
    
    // Handle form submit
    $('form').on('submit', function(e) {
        e.preventDefault();
        const tanggal = $('#tanggal').val();
        const fileSettle = $('#filter_file_settle').val();
        
        console.log('Form submit - Tanggal:', tanggal);
        console.log('Form submit - File Settle:', fileSettle);
        
        if (tanggal && buatJurnalTable) {
            // Update current URL parameters
            const url = new URL(window.location);
            url.searchParams.set('tanggal', tanggal);
            if (fileSettle !== '') {
                url.searchParams.set('file_settle', fileSettle);
            } else {
                url.searchParams.delete('file_settle');
            }
            window.history.pushState({}, '', url);
            
            console.log('Updated URL:', url.toString());
            buatJurnalTable.ajax.reload();
        }
    });
});

function initializeDataTable() {
    buatJurnalTable = $('#buatJurnalTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.appConfig.baseUrl + 'settlement/buat-jurnal/datatable',
            type: 'GET',
            data: function(d) {
                d.tanggal = $('#tanggal').val() || window.appConfig.tanggalRekon;
                d.file_settle = $('#filter_file_settle').val();
                console.log('DataTable request data:', d);
                return d;
            },
            error: function(xhr, error, thrown) {
                console.error('DataTable AJAX Error:', error, thrown, xhr.responseText);
                if (xhr.status === 403 || xhr.status === 419) {
                    console.log('CSRF error in DataTable, refreshing token...');
                    refreshCSRFToken().then(function() {
                        console.log('CSRF refreshed, reloading DataTable...');
                        buatJurnalTable.ajax.reload();
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
            { data: 'NAMA_PRODUK', name: 'NAMA_PRODUK' },
            { 
                data: 'FILE_SETTLE', 
                name: 'FILE_SETTLE',
                render: function(data, type, row) {
                    const fileSettle = parseInt(data || 0);
                    switch(fileSettle) {
                        case 0: return '<span class="badge badge-secondary">Default (0)</span>';
                        case 1: return '<span class="badge text-white" style="background-color: #f9911b;">Pajak (1)</span>';
                        case 2: return '<span class="badge badge-info">Edu (2)</span>';
                        default: return '<span class="badge badge-light">' + data + '</span>';
                    }
                }
            },
            { 
                data: 'AMOUNT_DETAIL', 
                name: 'AMOUNT_DETAIL',
                className: 'text-right',
                render: function(data, type, row) {
                    if (data && data !== '0') {
                        return '<span class="text-primary font-weight-bold">' + data + '</span>';
                    } else {
                        return '<span class="text-muted">0</span>';
                    }
                }
            },
            { 
                data: 'AMOUNT_REKAP', 
                name: 'AMOUNT_REKAP',
                className: 'text-right',
                render: function(data, type, row) {
                    if (data && data !== '0') {
                        return '<span class="text-info font-weight-bold">' + data + '</span>';
                    } else {
                        return '<span class="text-muted">0</span>';
                    }
                }
            },
            { 
                data: 'SELISIH', 
                name: 'SELISIH',
                className: 'text-right',
                render: function(data, type, row) {
                    const selisih = parseFloat(String(data || '0').replace(/,/g, ''));
                    const className = selisih === 0 ? 'text-success' : 'text-danger';
                    const displayValue = data && data !== '0' ? data : '0';
                    return '<span class="' + className + ' font-weight-bold">' + displayValue + '</span>';
                }
            },
            { 
                data: 'JUM_TX_DISPUTE', 
                name: 'JUM_TX_DISPUTE',
                className: 'text-center',
                render: function(data, type, row) {
                    const jumTx = parseInt(data || 0);
                    const className = jumTx === 0 ? 'text-success' : 'text-danger';
                    return '<span class="' + className + ' font-weight-bold">' + jumTx + '</span>';
                }
            },
            { 
                data: 'AMOUNT_TX_DISPUTE', 
                name: 'AMOUNT_TX_DISPUTE',
                className: 'text-right',
                render: function(data, type, row) {
                    const amount = parseFloat(String(data || '0').replace(/,/g, ''));
                    const className = amount === 0 ? 'text-success' : 'text-danger';
                    const displayValue = data && data !== '0' ? data : '0';
                    return '<span class="' + className + ' font-weight-bold">' + displayValue + '</span>';
                }
            },
            { 
                data: 'KD_SETTLE', 
                name: 'KD_SETTLE',
                render: function(data, type, row) {
                    if (data && data.trim() !== '') {
                        return '<code class="text-success">' + data + '</code>';
                    } else {
                        return '<span class="text-muted">Belum dibuat</span>';
                    }
                }
            },
            { 
                data: 'CAN_CREATE', 
                name: 'action',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    if (data === 1) {
                        return '<button type="button" class="btn btn-sm btn-primary btn-create-jurnal" ' +
                               'data-produk="' + (row.NAMA_PRODUK || '') + '">' +
                               '<i class="fal fa-plus-circle"></i> Create Jurnal</button>';
                    } else {
                        const reason = row.KD_SETTLE ? 'Sudah dibuat' : 'Tidak memenuhi syarat';
                        const badgeClass = row.KD_SETTLE ? 'badge-success' : 'text-white" style="background-color: #f9911b;';
                        return '<span class="badge ' + badgeClass + '">' + reason + '</span>';
                    }
                }
            }
        ],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
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
        scrollX: true,
        autoWidth: false,
        columnDefs: [
            { width: "5%", targets: 0 },    // No
            { width: "15%", targets: 1 },   // Nama Produk  
            { width: "8%", targets: 2 },    // File Settle
            { width: "12%", targets: 3 },   // Amount Detail
            { width: "12%", targets: 4 },   // Amount Rekap
            { width: "10%", targets: 5 },   // Selisih
            { width: "8%", targets: 6 },    // Jum TX Dispute
            { width: "12%", targets: 7 },   // Amount TX Dispute
            { width: "10%", targets: 8 },   // Kode Settle
            { width: "8%", targets: 9 }     // Action
        ],
        dom: '<"row"<"col-sm-12">>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-5"i><"col-sm-7"p>>',
        drawCallback: function(settings) {
            $('.btn-create-jurnal').off('click').on('click', function() {
                const $btn = $(this);
                const namaProduk = $btn.data('produk');
                
                // Disable button immediately
                $btn.prop('disabled', true);
                
                openCreateJurnalModal(namaProduk, $btn);
            });
        }
    });
}

function openCreateJurnalModal(namaProduk, $btn) {
    if (!namaProduk) {
        showAlert('error', 'Nama produk tidak ditemukan');
        // Re-enable button on error
        if ($btn) $btn.prop('disabled', false);
        return;
    }

    const tanggalRekon = $('#tanggal').val() || window.appConfig.tanggalRekon;

    // Validate settlement data first
    refreshCSRFToken().then(function() {
        $.ajax({
            url: window.appConfig.baseUrl + "settlement/buat-jurnal/validate",
            type: "POST",
            data: { 
                nama_produk: namaProduk,
                tanggal_rekon: tanggalRekon
            },
            dataType: 'json',
            success: function(response) {
                if (response.csrf_token) {
                    currentCSRF = response.csrf_token;
                }
                
                if (response.success) {
                    currentProductData = response.data;
                    
                    // Fill modal fields
                    $('#modal_nama_produk').val(namaProduk);
                    $('#modal_tanggal_rekon').val(tanggalRekon);
                    $('#modal_file_settle').val(getFileSettleText(response.data.FILE_SETTLE));
                    $('#modal_amount_detail').val(response.data.AMOUNT_DETAIL || '0');
                    $('#modal_amount_rekap').val(response.data.AMOUNT_REKAP || '0');
                    $('#modal_selisih').val(response.data.SELISIH || '0');
                    $('#modal_jum_tx_dispute').val(response.data.JUM_TX_DISPUTE || response.data.JUM_TX_DISPURE || '0');
                    $('#modal_amount_tx_dispute').val(response.data.AMOUNT_TX_DISPUTE || response.data.AMOUNT_TX_DISPURE || '0');
                    
                    $('#createJurnalModal').modal('show');
                    
                    // Re-enable button when modal opens
                    if ($btn) $btn.prop('disabled', false);
                } else {
                    showAlert('error', response.message);
                    // Re-enable button on error
                    if ($btn) $btn.prop('disabled', false);
                }
            },
            error: function(xhr) {
                if (xhr.status === 403) {
                    // showAlert('error', 'Session expired. Please try again.');
                } else {
                    showAlert('error', 'Terjadi kesalahan saat validasi data');
                }
                // Re-enable button on error
                if ($btn) $btn.prop('disabled', false);
            }
        });
    });
}

function confirmCreateJurnal() {
    if (!currentProductData) {
        showAlert('error', 'Data produk tidak ditemukan');
        return;
    }

    const namaProduk = $('#modal_nama_produk').val();
    const tanggalRekon = $('#modal_tanggal_rekon').val();
    const $confirmBtn = $('#confirmCreateJurnalBtn');
    
    // Disable button immediately
    $confirmBtn.prop('disabled', true).html('<i class="fal fa-spinner fa-spin"></i> Membuat Jurnal...');
    
    refreshCSRFToken().then(function() {
        $.ajax({
            url: window.appConfig.baseUrl + "settlement/buat-jurnal/create",
            type: "POST",
            data: { 
                nama_produk: namaProduk,
                tanggal_rekon: tanggalRekon
            },
            dataType: 'json',
            success: function(response) {
                if (response.csrf_token) {
                    currentCSRF = response.csrf_token;
                }
                
                if (response.success) {
                    showAlert('success', response.message);
                    $('#createJurnalModal').modal('hide');
                    if (buatJurnalTable) {
                        buatJurnalTable.ajax.reload(null, false); // false = tetap di halaman yang sama
                    }
                } else {
                    showAlert('error', response.message);
                }
                
                // Re-enable button
                $confirmBtn.prop('disabled', false).html('<i class="fal fa-check"></i> Ya, Buat Jurnal');
            },
            error: function(xhr) {
                if (xhr.status === 403) {
                    // showAlert('error', 'Session expired. Please try again.');
                } else {
                    let errorMsg = 'Terjadi kesalahan saat membuat jurnal';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    showAlert('error', errorMsg);
                }
                
                // Re-enable button on error
                $confirmBtn.prop('disabled', false).html('<i class="fal fa-check"></i> Ya, Buat Jurnal');
            }
        });
    });
}

function getFileSettleText(fileSettle) {
    const fs = parseInt(fileSettle || 0);
    switch(fs) {
        case 0: return 'Default (0)';
        case 1: return 'Pajak (1)';
        case 2: return 'Edu (2)';
        default: return fileSettle;
    }
}

function formatNumber(num) {
    const cleanNum = parseFloat(String(num).replace(/,/g, '')) || 0;
    return new Intl.NumberFormat('id-ID').format(cleanNum);
}

function showAlert(type, message) {
    switch(type) {
        case 'success':
            toastr["success"](message);
            break;
        case 'error':
            toastr["error"](message);
            break;
        case 'warning':
            toastr["warning"](message);
            break;
        case 'info':
        default:
            toastr["info"](message);
            break;
    }
}

function resetFilters() {
    const url = new URL(window.location);
    url.searchParams.delete('tanggal');
    url.searchParams.delete('file_settle');
    window.location.href = url.pathname + url.search;
}