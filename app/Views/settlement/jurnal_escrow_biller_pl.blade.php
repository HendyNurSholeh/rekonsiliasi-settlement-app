@extends('layouts.app')

@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        <i class="fal fa-exchange-alt"></i> {{ $title }}
        <small>Jurnal Escrow to Biller PL untuk tanggal {{ date('d/m/Y', strtotime($tanggalData)) }}</small>
    </h1>
</div>

<!-- Filter Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fal fa-filter"></i> Filter Data
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ current_url() }}">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="tanggal" class="form-label">Tanggal Data</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                   value="{{ $tanggalData }}" required>
                        </div>
                        <div class="col-md-8 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fal fa-search"></i> Tampilkan Data
                            </button>
                            <button type="button" class="btn btn-secondary ml-2" onclick="resetFilters()">
                                <i class="fal fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fal fa-table"></i> Data Jurnal Escrow to Biller PL
                </h5>
            </div>
            <div class="card-body">
                <div>
                    <table class="table table-striped table-hover table-sm" id="jurnalEscrowBillerPlTable">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Kode Settle</th>
                                <th>Nama Produk</th>
                                <th>Status KR Escrow</th>
                                <th>No. Ref</th>
                                <th>Debit Account</th>
                                <th>Debit Name</th>
                                <th>Credit Account</th>
                                <th>Credit Name</th>
                                <th>Amount</th>
                                <th>Response Code</th>
                                <th>Core Ref</th>
                                <th>Core DateTime</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data akan dimuat via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// CSRF Management
let currentCSRF = '{{ csrf_token() }}';

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
    return $.get('{{ base_url('get-csrf-token') }}').then(function(response) {
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
let jurnalEscrowBillerPlTable;

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
        
        console.log('Form submit - Tanggal:', tanggal);
        
        if (tanggal && jurnalEscrowBillerPlTable) {
            // Update current URL parameters
            const url = new URL(window.location);
            url.searchParams.set('tanggal', tanggal);
            window.history.pushState({}, '', url);
            
            console.log('Updated URL:', url.toString());
            
            // Reload data tetap di halaman yang sama
            jurnalEscrowBillerPlTable.ajax.reload(null, false);
        }
    });
});

function initializeDataTable() {
    jurnalEscrowBillerPlTable = $('#jurnalEscrowBillerPlTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ base_url('settlement/jurnal-escrow-biller-pl/datatable') }}',
            type: 'GET',
            data: function(d) {
                d.tanggal = $('#tanggal').val() || '{{ $tanggalData }}';
                console.log('DataTable request data:', d);
                return d;
            },
            error: function(xhr, error, thrown) {
                console.error('DataTable AJAX Error:', error, thrown, xhr.responseText);
                if (xhr.status === 403 || xhr.status === 419) {
                    console.log('CSRF error in DataTable, refreshing token...');
                    refreshCSRFToken().then(function() {
                        console.log('CSRF refreshed, reloading DataTable...');
                        jurnalEscrowBillerPlTable.ajax.reload();
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
            { 
                data: 'r_KD_SETTLE', 
                name: 'r_KD_SETTLE',
                render: function(data, type, row) {
                    return '<code>' + (data || '') + '</code>';
                }
            },
            { 
                data: 'r_NAMA_PRODUK', 
                name: 'r_NAMA_PRODUK'
            },
            { 
                data: 'd_STATUS_KR_ESCROW', 
                name: 'd_STATUS_KR_ESCROW',
                render: function(data, type, row) {
                    if (data === 'Sukses') {
                        return '<span class="badge badge-success">' + data + '</span>';
                    } else if (data === 'Belum Proses') {
                        return '<span class="badge text-white" style="background-color: #f9911b;">' + data + '</span>';
                    } else if (data === 'Sukses Sebagian') {
                        return '<span class="badge badge-info">' + data + '</span>';
                    }
                    return '<span class="badge badge-secondary">' + (data || '-') + '</span>';
                }
            },
            { 
                data: 'd_NO_REF', 
                name: 'd_NO_REF'
            },
            { 
                data: 'd_DEBIT_ACCOUNT', 
                name: 'd_DEBIT_ACCOUNT'
            },
            { 
                data: 'd_DEBIT_NAME', 
                name: 'd_DEBIT_NAME'
            },
            { 
                data: 'd_CREDIT_ACCOUNT', 
                name: 'd_CREDIT_ACCOUNT'
            },
            { 
                data: 'd_CREDIT_NAME', 
                name: 'd_CREDIT_NAME'
            },
            { 
                data: 'd_AMOUNT', 
                name: 'd_AMOUNT',
                render: function(data, type, row) {
                    return formatCurrency(data);
                }
            },
            { 
                data: 'd_CODE_RES', 
                name: 'd_CODE_RES',
                render: function(data, type, row) {
                    if (data && data.startsWith('00')) {
                        return '<span class="badge badge-success">' + data + '</span>';
                    } else if (data) {
                        return '<span class="badge badge-danger">' + data + '</span>';
                    }
                    return '<span class="badge badge-secondary">Belum Diproses</span>';
                }
            },
            { 
                data: 'd_CORE_REF', 
                name: 'd_CORE_REF',
                render: function(data, type, row) {
                    return data || '<span class="badge badge-secondary">Belum Diproses</span>';
                }
            },
            { 
                data: 'd_CORE_DATETIME', 
                name: 'd_CORE_DATETIME',
                render: function(data, type, row) {
                    if (data) {
                        return new Date(data).toLocaleString('id-ID');
                    }
                    return '<span class="badge badge-secondary">Belum Diproses</span>';
                }
            },
            { 
                data: null,
                name: 'actions',
                orderable: false,
                searchable: false,
                render: function(data, type, full, meta) {
                    // Cek status untuk menentukan tombol yang ditampilkan
                    if (full.d_CODE_RES && full.d_CODE_RES.startsWith('00')) {
                        return "<div class='text-center'><span class='badge badge-success'><i class='fal fa-check'></i> Sudah Diproses</span></div>";
                    } else if (full.d_CODE_RES && !full.d_CODE_RES.startsWith('00')) {
                        return "<div class='text-center'>" +
                               "<button class='btn btn-sm btn-outline-warning' onclick='prosesJurnalEscrowBiller(" + JSON.stringify(full) + ", " + meta.row + ")' id='btn-proses-eb-" + meta.row + "'>" +
                               "<i class='fal fa-redo'></i> Proses Ulang" +
                               "</button></div>";
                    } else {
                        return "<div class='text-center'>" +
                               "<button class='btn btn-sm btn-outline-primary' onclick='prosesJurnalEscrowBiller(" + JSON.stringify(full) + ", " + meta.row + ")' id='btn-proses-eb-" + meta.row + "'>" +
                               "<i class='fal fa-play'></i> Proses Jurnal" +
                               "</button></div>";
                    }
                }
            }
        ],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[1, 'asc']], // KD_SETTLE column (index 1)
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
             '<"row"<"col-sm-5"i><"col-sm-7"p>>',
    });
}

function formatCurrency(amount) {
    const num = parseFloat(String(amount || 0).replace(/,/g, ''));
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
}

function resetFilters() {
    const url = new URL(window.location);
    url.searchParams.delete('tanggal');
    window.location.href = url.pathname + url.search;
}

// Action Functions untuk Escrow Biller PL dengan Security Best Practices
function prosesJurnalEscrowBiller(rowData, rowIndex) {
    console.log('Proses Jurnal Escrow Biller:', rowData);
    
    const btnId = '#btn-proses-eb-' + rowIndex;
    const $btn = $(btnId);
    
    // Validasi data
    if (!rowData.r_KD_SETTLE || !rowData.d_NO_REF) {
        showAlertEscrowBiller('error', 'Data tidak lengkap untuk diproses!');
        return;
    }
    
    // Cek apakah sudah diproses sukses
    if (rowData.d_CODE_RES && rowData.d_CODE_RES.startsWith('00')) {
        showAlertEscrowBiller('warning', 'Jurnal sudah berhasil diproses sebelumnya!');
        return;
    }
    
    // Konfirmasi dengan detail informasi
    const isReprocess = rowData.d_CODE_RES && !rowData.d_CODE_RES.startsWith('00');
    const confirmMessage = isReprocess 
        ? `Apakah Anda yakin ingin memproses ULANG jurnal Escrow to Biller PL?\n\nKode Settle: ${rowData.r_KD_SETTLE}\nProduk: ${rowData.r_NAMA_PRODUK}\nAmount: ${formatCurrency(rowData.d_AMOUNT)}\n\nTransaksi ini akan mengirim dana ke rekening bank!`
        : `Apakah Anda yakin ingin memproses jurnal Escrow to Biller PL?\n\nKode Settle: ${rowData.r_KD_SETTLE}\nProduk: ${rowData.r_NAMA_PRODUK}\nAmount: ${formatCurrency(rowData.d_AMOUNT)}\n\nTransaksi ini akan mengirim dana ke rekening bank!`;
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    // Disable button dan semua interaksi
    $btn.prop('disabled', true);
    disableAllActionsEscrowBiller();
    
    // Show loading state
    $btn.html('<i class="fal fa-spinner fa-spin"></i> Memproses...');
    
    // Show progress modal
    showProgressModalEscrowBiller(rowData);
    
    // Prevent browser close/refresh
    setBeforeUnloadWarning(true);
    
    // AJAX call untuk proses jurnal
    $.ajax({
        url: '{{ base_url('settlement/jurnal-escrow-biller-pl/proses') }}',
        type: 'POST',
        timeout: 120000, // 2 menit timeout
        data: {
            csrf_test_name: currentCSRF,
            kd_settle: rowData.r_KD_SETTLE,
            no_ref: rowData.d_NO_REF,
            amount: rowData.d_AMOUNT,
            debit_account: rowData.d_DEBIT_ACCOUNT,
            credit_account: rowData.d_CREDIT_ACCOUNT,
            status_kr_escrow: rowData.d_STATUS_KR_ESCROW,
            is_reprocess: isReprocess ? 1 : 0
        },
        success: function(response) {
            hideProgressModalEscrowBiller();
            setBeforeUnloadWarning(false);
            
            if (response.success) {
                showAlertEscrowBiller('success', 'Jurnal Escrow to Biller PL berhasil diproses!\nCore Ref: ' + (response.core_ref || '-'));
                
                // Reload table untuk update status
                setTimeout(function() {
                    jurnalEscrowBillerPlTable.ajax.reload(null, false);
                }, 1500);
            } else {
                showAlertEscrowBiller('error', 'Gagal memproses jurnal: ' + (response.message || 'Unknown error'));
                
                // Reset button state
                resetButtonStateEscrowBiller($btn, isReprocess);
            }
            
            enableAllActionsEscrowBiller();
        },
        error: function(xhr, status, error) {
            hideProgressModalEscrowBiller();
            setBeforeUnloadWarning(false);
            enableAllActionsEscrowBiller();
            
            let errorMessage = 'Terjadi kesalahan saat memproses jurnal';
            
            if (status === 'timeout') {
                errorMessage = 'Timeout! Transaksi mungkin masih berjalan. Silakan cek status transaksi.';
            } else if (xhr.status === 403 || xhr.status === 419) {
                errorMessage = 'Session expired. Silakan refresh halaman dan coba lagi.';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            showAlertEscrowBiller('error', errorMessage);
            
            // Reset button state
            resetButtonStateEscrowBiller($btn, isReprocess);
            
            console.error('Proses Jurnal Escrow Biller Error:', {
                status: xhr.status,
                statusText: xhr.statusText,
                response: xhr.responseText,
                error: error
            });
        }
    });
}

function disableAllActionsEscrowBiller() {
    // Disable semua tombol proses di halaman
    $('button[id^="btn-proses-eb-"]').prop('disabled', true);
    
    // Disable form filter
    $('#tanggal').prop('disabled', true);
    $('button[type="submit"]').prop('disabled', true);
    
    // Disable table interactions
    $('.dataTables_length select').prop('disabled', true);
    $('.dataTables_paginate .paginate_button').addClass('disabled');
}

function enableAllActionsEscrowBiller() {
    // Enable kembali semua tombol dan form
    $('button[id^="btn-proses-eb-"]').prop('disabled', false);
    $('#tanggal').prop('disabled', false);
    $('button[type="submit"]').prop('disabled', false);
    $('.dataTables_length select').prop('disabled', false);
    $('.dataTables_paginate .paginate_button').removeClass('disabled');
}

function resetButtonStateEscrowBiller($btn, isReprocess) {
    if (isReprocess) {
        $btn.html('<i class="fal fa-redo"></i> Proses Ulang');
    } else {
        $btn.html('<i class="fal fa-play"></i> Proses Jurnal');
    }
    $btn.prop('disabled', false);
}

function showProgressModalEscrowBiller(rowData) {
    const modalContent = `
        <div class="modal fade" id="progressModalEscrowBiller" tabindex="-1" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fal fa-cog fa-spin"></i> Memproses Transaksi Escrow to Biller PL
                        </h5>
                    </div>
                    <div class="modal-body text-center">
                        <div class="mb-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                        <h6>Sedang memproses jurnal:</h6>
                        <div class="alert alert-info">
                            <strong>Kode Settle:</strong> ${rowData.r_KD_SETTLE}<br>
                            <strong>Amount:</strong> ${formatCurrency(rowData.d_AMOUNT)}<br>
                            <strong>Produk:</strong> ${rowData.r_NAMA_PRODUK}<br>
                            <strong>Status KR Escrow:</strong> ${rowData.d_STATUS_KR_ESCROW || '-'}
                        </div>
                        <div class="alert alert-warning">
                            <i class="fal fa-exclamation-triangle"></i>
                            <strong>PENTING:</strong><br>
                            Jangan tutup atau refresh browser!<br>
                            Transaksi sedang berlangsung...
                        </div>
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('body').append(modalContent);
    $('#progressModalEscrowBiller').modal('show');
}

function hideProgressModalEscrowBiller() {
    $('#progressModalEscrowBiller').modal('hide');
    setTimeout(function() {
        $('#progressModalEscrowBiller').remove();
    }, 500);
}

function showAlertEscrowBiller(type, message) {
    // Implementasi alert yang lebih baik
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger', 
        'warning': 'alert-warning',
        'info': 'alert-info'
    };
    
    const alertHtml = `
        <div class="alert ${alertClass[type]} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <strong>${type.charAt(0).toUpperCase() + type.slice(1)}!</strong> ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `;
    
    $('body').append(alertHtml);
    
    // Auto hide after 5 seconds
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
}

function setBeforeUnloadWarning(enable) {
    if (enable) {
        window.onbeforeunload = function() {
            return "Transaksi sedang berlangsung! Jika Anda menutup halaman ini, transaksi mungkin gagal.";
        };
    } else {
        window.onbeforeunload = null;
    }
}
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="{{ base_url('css/settlement/settlement.css') }}">

@endpush
