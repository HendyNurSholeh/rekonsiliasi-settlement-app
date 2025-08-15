@extends('layouts.app')

@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        <i class="fal fa-check-circle"></i> {{ $title }}
        <small>Approval jurnal settlement untuk tanggal {{ date('d/m/Y', strtotime($tanggalRekon)) }}</small>
    </h1>
</div>

<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fal fa-info-circle"></i>
            <strong>Informasi Approval</strong> 
            <br>Halaman ini menampilkan daftar jurnal settlement yang perlu disetujui atau ditolak.
            <br>Klik tombol "Approve" untuk melihat detail jurnal dan melakukan proses approval (jika masih pending).
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4" id="summaryCards">
    <div class="col-md-3">
        <div class="card border-left-primary">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Jurnal</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalJurnal">-</div>
                    </div>
                    <div class="col-auto">
                        <i class="fal fa-file-invoice-dollar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-left-success">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Disetujui</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="approvedJurnal">-</div>
                    </div>
                    <div class="col-auto">
                        <i class="fal fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-left-danger">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Ditolak</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="rejectedJurnal">-</div>
                    </div>
                    <div class="col-auto">
                        <i class="fal fa-times-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-left-warning">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="pendingJurnal">-</div>
                    </div>
                    <div class="col-auto">
                        <i class="fal fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
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
                            <label for="tanggal" class="form-label">Tanggal Settlement</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                   value="{{ $tanggalRekon }}" required>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_status_approve" class="form-label">Status Approval</label>
                            <select class="form-control" id="filter_status_approve" name="status_approve">
                                <option value="">Semua Status</option>
                                <option value="pending" @if(request()->getGet('status_approve') === 'pending') selected @endif>Pending</option>
                                <option value="1" @if(request()->getGet('status_approve') == '1') selected @endif>Disetujui</option>
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
                    <i class="fal fa-table"></i> Data Settlement Products
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="approveJurnalTable">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Tanggal Data</th>
                                <th>Nama Produk</th>
                                <th>Kode Settle</th>
                                <th>Status Approval</th>
                                <th>User Approver</th>
                                <th>Tanggal Approve</th>
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

<!-- Modal Approval -->
<div class="modal fade" id="approvalModal" tabindex="-1" role="dialog" aria-labelledby="approvalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approvalModalLabel">
                    <i class="fal fa-check-circle"></i> Detail Jurnal Settlement
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Settlement Info -->
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0" id="modalTitle">Jurnal Settlement</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Kode Settle</label>
                                    <input type="text" class="form-control" id="modal_kd_settle" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nama Produk</label>
                                    <input type="text" class="form-control" id="modal_nama_produk" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Total Jurnal (KR)</label>
                                    <input type="text" class="form-control" id="modal_total_amount" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Jurnal -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">Detail Jurnal</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-sm" id="detailJurnalTable">
                                <thead>
                                    <tr>
                                        <th>Jenis Settle</th>
                                        <th>ID Partner</th>
                                        <th>Core</th>
                                        <th>Debit Account</th>
                                        <th>Debit Name</th>
                                        <th>Credit Core</th>
                                        <th>Credit Account</th>
                                        <th>Credit Name</th>
                                        <th>Amount</th>
                                        <th>Description</th>
                                        <th>Ref Number</th>
                                    </tr>
                                </thead>
                                <tbody id="detailJurnalBody">
                                    <!-- Data detail akan dimuat via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fal fa-times"></i> Tutup
                </button>
                <div id="approvalButtons">
                    <button type="button" class="btn btn-danger" onclick="processApproval('reject')">
                        <i class="fal fa-times-circle"></i> Tolak
                    </button>
                    <button type="button" class="btn btn-success" onclick="processApproval('approve')">
                        <i class="fal fa-check-circle"></i> Setujui
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ base_url('assets/js/toastr.min.js') }}"></script>
<script>
// Toastr configuration
toastr.options = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": false,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};

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
let approveJurnalTable;
let currentSettleData = null;

$(document).ready(function() {
    // Refresh CSRF token saat page load
    refreshCSRFToken().then(function() {
        console.log('CSRF token refreshed on page load');
        initializeDataTable();
        loadSummary();
    });
    
    // Handle form submit
    $('form').on('submit', function(e) {
        e.preventDefault();
        const tanggal = $('#tanggal').val();
        const statusApprove = $('#filter_status_approve').val();
        
        console.log('Form submit - Tanggal:', tanggal);
        console.log('Form submit - Status Approve:', statusApprove);
        
        if (tanggal && approveJurnalTable) {
            // Update current URL parameters
            const url = new URL(window.location);
            url.searchParams.set('tanggal', tanggal);
            if (statusApprove !== '') {
                url.searchParams.set('status_approve', statusApprove);
            } else {
                url.searchParams.delete('status_approve');
            }
            window.history.pushState({}, '', url);
            
            console.log('Updated URL:', url.toString());
            
            // Reload data tetap di halaman yang sama
            approveJurnalTable.ajax.reload(null, false);
            loadSummary();
        }
    });
});

function initializeDataTable() {
    approveJurnalTable = $('#approveJurnalTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ base_url('settlement/approve-jurnal/datatable') }}',
            type: 'GET',
            data: function(d) {
                d.tanggal = $('#tanggal').val() || '{{ $tanggalRekon }}';
                d.status_approve = $('#filter_status_approve').val();
                console.log('DataTable request data:', d);
                return d;
            },
            error: function(xhr, error, thrown) {
                console.error('DataTable AJAX Error:', error, thrown, xhr.responseText);
                if (xhr.status === 403 || xhr.status === 419) {
                    console.log('CSRF error in DataTable, refreshing token...');
                    refreshCSRFToken().then(function() {
                        console.log('CSRF refreshed, reloading DataTable...');
                        approveJurnalTable.ajax.reload();
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
                data: 'TGL_DATA', 
                name: 'TGL_DATA',
                render: function(data, type, row) {
                    if (data) {
                        return new Date(data).toLocaleDateString('id-ID');
                    }
                    return '';
                }
            },
            { data: 'NAMA_PRODUK', name: 'NAMA_PRODUK' },
            { 
                data: 'KD_SETTLE', 
                name: 'KD_SETTLE',
                render: function(data, type, row) {
                    return '<code>' + (data || '') + '</code>';
                }
            },
            { 
                data: 'STAT_APPROVER', 
                name: 'STAT_APPROVER',
                render: function(data, type, row) {
                    if (data === '1') {
                        return '<span class="badge badge-success">Disetujui</span>';
                    } else if (data === '0') {
                        return '<span class="badge badge-danger">Ditolak</span>';
                    } else {
                        return '<span class="badge text-white" style="background-color: #f9911b;">Pending</span>';
                    }
                }
            },
            { data: 'USER_APPROVER', name: 'USER_APPROVER' },
            { 
                data: 'TGL_APPROVER', 
                name: 'TGL_APPROVER',
                render: function(data, type, row) {
                    if (data) {
                        return new Date(data).toLocaleDateString('id-ID') + ' ' + new Date(data).toLocaleTimeString('id-ID');
                    }
                    return '';
                }
            },
            { 
                data: 'KD_SETTLE', 
                name: 'action',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return '<button type="button" class="btn btn-sm btn-primary btn-view-detail" ' +
                           'data-kd-settle="' + (data || '') + '">' +
                           '<i class="fal fa-check-circle"></i> Approve</button>';
                }
            }
        ],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[1, 'desc']], // TGL_DATA column (index 1)
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
        drawCallback: function(settings) {
            $('.btn-view-detail').off('click').on('click', function() {
                const kdSettle = $(this).data('kd-settle');
                openApprovalModal(kdSettle);
            });
        }
    });
}

function openApprovalModal(kdSettle) {
    if (!kdSettle) {
        toastr["error"]('Kode settle tidak ditemukan');
        return;
    }

    refreshCSRFToken().then(function() {
        $.ajax({
            url: '{{ base_url('settlement/approve-jurnal/detail') }}',
            type: 'POST',
            data: { kd_settle: kdSettle },
            dataType: 'json',
            success: function(response) {
                if (response.csrf_token) {
                    currentCSRF = response.csrf_token;
                }
                
                if (response.success) {
                    currentSettleData = {
                        kd_settle: kdSettle,
                        settle_info: response.settle_info
                    };
                    
                    // Fill modal header and basic info
                    const settleInfo = response.settle_info;
                    const tglSettle = new Date(settleInfo.TGL_DATA).toLocaleDateString('id-ID');
                    $('#modalTitle').text(`Jurnal Settlement tanggal ${tglSettle} untuk produk ${settleInfo.NAMA_PRODUK}`);
                    
                    $('#modal_kd_settle').val(kdSettle);
                    $('#modal_nama_produk').val(settleInfo.NAMA_PRODUK);
                    $('#modal_total_amount').val(formatCurrency(settleInfo.TOT_JURNAL_KR_ECR));
                    
                    // Fill detail table
                    const detailBody = $('#detailJurnalBody');
                    detailBody.empty();
                    
                    if (response.detail_data && response.detail_data.length > 0) {
                        response.detail_data.forEach(function(detail) {
                            const row = `
                                <tr>
                                    <td>${detail.JENIS_SETTLE || ''}</td>
                                    <td>${detail.IDPARTNER || ''}</td>
                                    <td>${detail.CORE || ''}</td>
                                    <td>${detail.DEBIT_ACCOUNT || ''}</td>
                                    <td>${detail.DEBIT_NAME || ''}</td>
                                    <td>${detail.CREDIT_CORE || ''}</td>
                                    <td>${detail.CREDIT_ACCOUNT || ''}</td>
                                    <td>${detail.CREDIT_NAME || ''}</td>
                                    <td class="text-right">${formatCurrency(detail.AMOUNT)}</td>
                                    <td>${detail.DESCRIPTION || ''}</td>
                                    <td>${detail.REF_NUMBER || ''}</td>
                                </tr>
                            `;
                            detailBody.append(row);
                        });
                    } else {
                        detailBody.append('<tr><td colspan="11" class="text-center">Tidak ada detail jurnal</td></tr>');
                    }
                    
                    // Check if the settlement has been approved/rejected
                    // Get current row data from DataTable to check status
                    let currentRowData = null;
                    if (approveJurnalTable) {
                        const tableData = approveJurnalTable.rows().data();
                        for (let i = 0; i < tableData.length; i++) {
                            if (tableData[i].KD_SETTLE === kdSettle) {
                                currentRowData = tableData[i];
                                break;
                            }
                        }
                    }
                    
                    // Show/hide approval buttons based on status
                    if (currentRowData && (currentRowData.STAT_APPROVER === '1' || currentRowData.STAT_APPROVER === '0')) {
                        $('#approvalButtons').hide(); // Hide approve/reject buttons if already processed
                    } else {
                        $('#approvalButtons').show(); // Show approve/reject buttons if still pending
                    }
                    
                    $('#approvalModal').modal('show');
                } else {
                    toastr["error"](response.message);
                }
            },
            error: function(xhr) {
                if (xhr.status === 403) {
                    toastr["error"]('Session expired. Please try again.');
                } else {
                    toastr["error"]('Terjadi kesalahan saat mengambil detail jurnal');
                }
            }
        });
    });
}

function processApproval(action) {
    if (!currentSettleData) {
        toastr["error"]('Data settlement tidak ditemukan');
        return;
    }

    const actionText = action === 'approve' ? 'menyetujui' : 'menolak';
    const tanggalRekon = $('#tanggal').val() || '{{ $tanggalRekon }}';
    
    refreshCSRFToken().then(function() {
        $.ajax({
            url: '{{ base_url('settlement/approve-jurnal/process') }}',
            type: 'POST',
            data: { 
                kd_settle: currentSettleData.kd_settle,
                tanggal_rekon: tanggalRekon,
                action: action
            },
            dataType: 'json',
            success: function(response) {
                if (response.csrf_token) {
                    currentCSRF = response.csrf_token;
                }
                
                if (response.success) {
                    toastr["success"](response.message);
                    $('#approvalModal').modal('hide');
                    if (approveJurnalTable) {
                        // Reload data tetap di halaman yang sama
                        approveJurnalTable.ajax.reload(null, false);
                    }
                    loadSummary();
                } else {
                    toastr["error"](response.message);
                }
            },
            error: function(xhr) {
                if (xhr.status === 403) {
                    toastr["error"]('Session expired. Please try again.');
                } else {
                    toastr["error"](`Terjadi kesalahan saat ${actionText} jurnal`);
                }
            }
        });
    });
}

function loadSummary() {
    const tanggal = $('#tanggal').val() || '{{ $tanggalRekon }}';
    
    $.ajax({
        url: '{{ base_url('settlement/approve-jurnal/summary') }}',
        type: 'GET',
        data: { tanggal: tanggal },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.summary) {
                const summary = response.summary;
                $('#totalJurnal').text(summary.total_jurnal || 0);
                $('#approvedJurnal').text(summary.approved || 0);
                $('#rejectedJurnal').text(summary.rejected || 0);
                $('#pendingJurnal').text(summary.pending || 0);
            }
        },
        error: function(xhr) {
            console.error('Error loading summary:', xhr);
        }
    });
}

function formatCurrency(amount) {
    const num = parseFloat(String(amount || 0).replace(/,/g, ''));
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
}

function resetFilters() {
    const url = new URL(window.location);
    url.searchParams.delete('tanggal');
    url.searchParams.delete('status_approve');
    window.location.href = url.pathname + url.search;
}
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="{{ base_url('assets/css/toastr.min.css') }}">
<link rel="stylesheet" href="{{ base_url('css/settlement/settlement.css') }}">
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.text-xs {
    font-size: 0.75rem;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.text-gray-300 {
    color: #dddfeb !important;
}

.btn-view-detail {
    transition: all 0.3s ease;
}

.btn-view-detail:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
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

#detailJurnalTable {
    font-size: 0.875rem;
}

#detailJurnalTable th {
    background-color: #f8f9fc;
    border-top: 1px solid #e3e6f0;
    font-weight: 600;
}

.text-right {
    text-align: right;
}

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    border: 1px solid #e3e6f0;
}
</style>
@endpush
