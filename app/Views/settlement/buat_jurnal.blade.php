@extends('layouts.app')

@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        <i class="fal fa-file-invoice-dollar"></i> {{ $title }}
        <small>Membuat jurnal settlement untuk tanggal {{ date('d/m/Y', strtotime($tanggalRekon)) }}</small>
    </h1>
</div>

<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fal fa-info-circle"></i>
            <strong>Informasi Settlement</strong> 
            <br>Modul ini berfungsi untuk membuat jurnal transaksi settlement yang kemudian akan diproses di sistem core banking. 
            <br>Produk yang dapat diproses adalah produk yang tidak memiliki dispute atau status settle verifikasinya adalah 1 (dilimpahkan) atau 9 (tidak dilimpahkan).
        
        </div>
    </div>
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
                        <div class="col-md-3">
                            <label for="tanggal" class="form-label">Tanggal Rekonsiliasi</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                   value="{{ $tanggalRekon }}" required>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_file_settle" class="form-label">File Settle</label>
                            <select class="form-control" id="filter_file_settle" name="file_settle">
                                <option value="">Semua File</option>
                                <option value="0" @if(request()->getGet('file_settle') == '0') selected @endif>Default (0)</option>
                                <option value="1" @if(request()->getGet('file_settle') == '1') selected @endif>Pajak (1)</option>
                                <option value="2" @if(request()->getGet('file_settle') == '2') selected @endif>Edu (2)</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
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
                    <i class="fal fa-table"></i> Data Compare Rekap Settlement
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="buatJurnalTable">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Produk</th>
                                <th>File Settle</th>
                                <th>Amount Detail</th>
                                <th>Amount Rekap</th>
                                <th>Selisih</th>
                                <th>Jum TX Dispute</th>
                                <th>Amount TX Dispute</th>
                                <th>Kode Settle</th>
                                <th>Action</th>
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

<!-- Modal Konfirmasi Create Jurnal -->
<div class="modal fade" id="createJurnalModal" tabindex="-1" role="dialog" aria-labelledby="createJurnalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createJurnalModalLabel">
                    <i class="fal fa-plus-circle"></i> Konfirmasi Buat Jurnal Settlement
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fal fa-exclamation-triangle"></i>
                    <strong>Perhatian!</strong> Pastikan data sudah benar sebelum membuat jurnal settlement.
                </div>
                
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">Detail Produk</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Produk</label>
                                    <input type="text" class="form-control" id="modal_nama_produk" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Rekonsiliasi</label>
                                    <input type="text" class="form-control" id="modal_tanggal_rekon" readonly>
                                </div>
                            </div>
                            <div class="col-md-4 mt-2">
                                <div class="form-group">
                                    <label>File Settle</label>
                                    <input type="text" class="form-control" id="modal_file_settle" readonly>
                                </div>
                            </div>
                            <div class="col-md-4 mt-2">
                                <div class="form-group">
                                    <label>Amount Detail</label>
                                    <input type="text" class="form-control" id="modal_amount_detail" readonly>
                                </div>
                            </div>
                            <div class="col-md-4 mt-2">
                                <div class="form-group">
                                    <label>Amount Rekap</label>
                                    <input type="text" class="form-control" id="modal_amount_rekap" readonly>
                                </div>
                            </div>
                            <div class="col-md-4 mt-2">
                                <div class="form-group">
                                    <label>Selisih</label>
                                    <input type="text" class="form-control" id="modal_selisih" readonly>
                                </div>
                            </div>
                            <div class="col-md-4 mt-2">
                                <div class="form-group">
                                    <label>Jum TX Dispute</label>
                                    <input type="text" class="form-control" id="modal_jum_tx_dispute" readonly>
                                </div>
                            </div>
                            <div class="col-md-4 mt-2">
                                <div class="form-group">
                                    <label>Amount TX Dispute</label>
                                    <input type="text" class="form-control" id="modal_amount_tx_dispute" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="fal fa-info-circle"></i>
                            <strong>Syarat Validasi:</strong>
                            <ul class="mb-0 mt-2">
                                <li>SELISIH harus = 0 (Amount Detail - Amount Rekap = 0)</li>
                                <li>JUM_TX_DISPUTE harus = 0 (Tidak ada transaksi yang dispute)</li>
                                <li>AMOUNT_TX_DISPUTE harus = 0 (Total amount dispute = 0)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fal fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-primary" onclick="confirmCreateJurnal()">
                    <i class="fal fa-check"></i> Ya, Buat Jurnal
                </button>
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
            url: '{{ base_url('settlement/buat-jurnal/datatable') }}',
            type: 'GET',
            data: function(d) {
                d.tanggal = $('#tanggal').val() || '{{ $tanggalRekon }}';
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
                const namaProduk = $(this).data('produk');
                openCreateJurnalModal(namaProduk);
            });
        }
    });
}

function openCreateJurnalModal(namaProduk) {
    if (!namaProduk) {
        showAlert('error', 'Nama produk tidak ditemukan');
        return;
    }

    const tanggalRekon = $('#tanggal').val() || '{{ $tanggalRekon }}';
    
    // Validate settlement data first
    refreshCSRFToken().then(function() {
        $.ajax({
            url: '{{ base_url('settlement/buat-jurnal/validate') }}',
            type: 'POST',
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
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function(xhr) {
                if (xhr.status === 403) {
                    showAlert('error', 'Session expired. Please try again.');
                } else {
                    showAlert('error', 'Terjadi kesalahan saat validasi data');
                }
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
    
    refreshCSRFToken().then(function() {
        $.ajax({
            url: '{{ base_url('settlement/buat-jurnal/create') }}',
            type: 'POST',
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
                        buatJurnalTable.ajax.reload();
                    }
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function(xhr) {
                if (xhr.status === 403) {
                    showAlert('error', 'Session expired. Please try again.');
                } else {
                    showAlert('error', 'Terjadi kesalahan saat membuat jurnal');
                }
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
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="{{ base_url('css/settlement/settlement.css') }}">
<style>
.btn-create-jurnal {
    transition: all 0.3s ease;
}

.btn-create-jurnal:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.text-success {
    font-weight: 600;
}

.text-danger {
    font-weight: 600;
}

.text-primary {
    font-weight: 600;
}

.text-info {
    font-weight: 600;
}

.badge {
    font-size: 0.85em;
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.modal-header .close {
    color: white;
    opacity: 0.8;
}

.modal-header .close:hover {
    opacity: 1;
}

/* Table styling for better readability */
#buatJurnalTable {
    font-size: 0.9em;
}

#buatJurnalTable th {
    font-size: 0.85em;
    white-space: nowrap;
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}

#buatJurnalTable td {
    vertical-align: middle;
}

#buatJurnalTable .text-right {
    text-align: right !important;
}

#buatJurnalTable .text-center {
    text-align: center !important;
}

/* Code styling for KD_SETTLE */
code.text-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    padding: 2px 6px;
    border-radius: 3px;
}

/* Amount styling */
.amount-cell {
    font-family: 'Courier New', monospace;
    font-weight: bold;
}

/* Modal form group styling */
.modal .form-group label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 5px;
}

.modal .form-control[readonly] {
    background-color: #f8f9fa;
    border-color: #ced4da;
}

/* Responsive table handling */
@media (max-width: 1200px) {
    #buatJurnalTable {
        font-size: 0.8em;
    }
    
    #buatJurnalTable th,
    #buatJurnalTable td {
        padding: 0.5rem 0.3rem;
    }
}

@media (max-width: 768px) {
    .table-responsive {
        border: none;
    }
    
    #buatJurnalTable th:not(:first-child):not(:last-child),
    #buatJurnalTable td:not(:first-child):not(:last-child) {
        display: none;
    }
    
    #buatJurnalTable th:first-child,
    #buatJurnalTable td:first-child {
        width: 30%;
    }
    
    #buatJurnalTable th:last-child,
    #buatJurnalTable td:last-child {
        width: 70%;
    }
}
</style>
@endpush
