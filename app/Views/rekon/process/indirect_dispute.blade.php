@extends('layouts.app')

@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        <i class="fal fa-exclamation-triangle"></i> {{ $title }}
        <small>Penyelesaian dispute indirect jurnal untuk tanggal {{ date('d/m/Y', strtotime($tanggalRekon)) }}</small>
    </h1>
</div>

<div class="row">
    <div class="col-12">
        <div class="alert alert-warning">
            <i class="fal fa-exclamation-triangle"></i>
            <strong>Penyelesaian Dispute</strong> 
            <br>Menampilkan data transaksi indirect jurnal yang memerlukan penyelesaian dispute.
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
                            <label for="filterPartner" class="form-label">Partner</label>
                            <select class="form-control" id="filterPartner" name="partner">
                                <option value="">Semua Partner</option>
                                <!-- Options akan diisi via AJAX atau dari controller -->
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterProduk" class="form-label">Group Produk</label>
                            <select class="form-control" id="filterProduk" name="produk">
                                <option value="">Semua Produk</option>
                                <!-- Options akan diisi via AJAX atau dari controller -->
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fal fa-search"></i> Tampilkan Data
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="resetFilters()">
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
                    <i class="fal fa-table"></i> Data Dispute Indirect Jurnal
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="disputeTable">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>ID</th>
                                <th>Tanggal Rekon</th>
                                <th>Partner</th>
                                <th>Terminal ID</th>
                                <th>Group Produk</th>
                                <th>IDPEL</th>
                                <th>RP Biller Tag</th>
                                <th>Status Biller</th>
                                <th>Stat Core AGR</th>
                                <th>Settle RP Tag</th>
                                <th>Aksi</th>
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

<!-- Modal Detail Dispute -->
<div class="modal fade" id="modalDetailDispute" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fal fa-eye"></i> Detail Dispute
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="detailContent">
                    <!-- Content akan diisi via AJAX -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Update Dispute -->
<div class="modal fade" id="modalUpdateDispute" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fal fa-edit"></i> Update Dispute
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formUpdateDispute">
                <div class="modal-body">
                    <input type="hidden" id="updateDisputeId" name="dispute_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="updatePartner">Partner</label>
                                <input type="text" class="form-control" id="updatePartner" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="updateTerminalID">Terminal ID</label>
                                <input type="text" class="form-control" id="updateTerminalID" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="updateTanggalRekon">Tanggal Rekon</label>
                                <input type="text" class="form-control" id="updateTanggalRekon" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="updateGroupProduk">Group Produk</label>
                                <input type="text" class="form-control" id="updateGroupProduk" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="updateIDPEL">IDPEL</label>
                                <input type="text" class="form-control" id="updateIDPEL" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="updateAmount">RP Biller Tag</label>
                                <input type="text" class="form-control" id="updateAmount" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="updateStatus">Status Dispute <span class="text-danger">*</span></label>
                        <select class="form-control" id="updateStatus" name="status" required>
                            <option value="">Pilih Status</option>
                            <option value="PENDING">Pending</option>
                            <option value="IN_PROGRESS">In Progress</option>
                            <option value="RESOLVED">Resolved</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="updateKeterangan">Keterangan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="updateKeterangan" name="keterangan" rows="4" 
                                  placeholder="Masukkan keterangan penyelesaian dispute..." required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="updateTindakLanjut">Tindak Lanjut</label>
                        <textarea class="form-control" id="updateTindakLanjut" name="tindak_lanjut" rows="3" 
                                  placeholder="Tindak lanjut yang diperlukan (opsional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fal fa-save"></i> Update Dispute
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Super Simple but Robust CSRF Management
let currentCSRF = '{{ csrf_token() }}';

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
    return $.get('{{ base_url('rekon/process/get-csrf-token') }}').then(function(response) {
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
let disputeTable;

$(document).ready(function() {
    // Refresh CSRF token saat page load untuk memastikan token fresh
    refreshCSRFToken().then(function() {
        console.log('CSRF token refreshed on page load');
        
        // Initialize DataTable dengan AJAX
        initializeDataTable();
    });
    
    // Handle form submit for filters
    $('form').on('submit', function(e) {
        e.preventDefault();
        
        console.log('Form submit - Reloading DataTable with filters');
        
        if (disputeTable) {
            // Update current URL parameters
            const formData = new FormData(this);
            const url = new URL(window.location);
            
            for (let [key, value] of formData.entries()) {
                if (value) {
                    url.searchParams.set(key, value);
                } else {
                    url.searchParams.delete(key);
                }
            }
            
            window.history.pushState({}, '', url);
            console.log('Updated URL:', url.toString());
            
            // Reload DataTable with new filters
            disputeTable.ajax.reload();
        }
    });
    
    // Handle form submit for update dispute
    $('#formUpdateDispute').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const disputeId = $('#updateDisputeId').val();
        
        console.log('Submitting update dispute for ID:', disputeId);
        
        $.ajax({
            url: '{{ base_url('rekon/process/indirect-dispute/update') }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Update response:', response);
                
                if (response.success) {
                    showAlert('success', response.message);
                    $('#modalUpdateDispute').modal('hide');
                    disputeTable.ajax.reload();
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function(xhr) {
                console.error('Update error:', xhr.responseText);
                showAlert('error', 'Terjadi kesalahan saat memproses update dispute');
            }
        });
    });
});

function initializeDataTable() {
    disputeTable = $('#disputeTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ base_url('rekon/process/indirect-dispute/datatable') }}',
            type: 'GET',
            data: function(d) {
                // Add current filters
                d.tanggal = $('#tanggal').val() || '{{ $tanggalRekon }}';
                d.partner = $('#filterPartner').val();
                d.produk = $('#filterProduk').val();
                
                console.log('DataTable request data:', d);
                return d;
            },
            error: function(xhr, error, thrown) {
                console.error('DataTable AJAX Error:', error, thrown, xhr.responseText);
                if (xhr.status === 403 || xhr.status === 419) {
                    console.log('CSRF error in DataTable, refreshing token...');
                    refreshCSRFToken().then(function() {
                        console.log('CSRF refreshed, reloading DataTable...');
                        disputeTable.ajax.reload();
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
            { data: 'v_ID', name: 'v_ID' },
            { 
                data: 'v_TGL_FILE_REKON', 
                name: 'v_TGL_FILE_REKON',
                render: function(data, type, row) {
                    if (data) {
                        const date = new Date(data);
                        return date.toLocaleDateString('id-ID');
                    }
                    return '-';
                }
            },
            { data: 'IDPARTNER', name: 'IDPARTNER' },
            { data: 'TERMINALID', name: 'TERMINALID' },
            { data: 'v_GROUP_PRODUK', name: 'v_GROUP_PRODUK' },
            { data: 'IDPEL', name: 'IDPEL' },
            { 
                data: 'RP_BILLER_TAG', 
                name: 'RP_BILLER_TAG',
                className: 'text-end',
                render: function(data, type, row) {
                    const amount = parseFloat(String(data || 0).replace(/,/g, ''));
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
                }
            },
            { 
                data: 'STATUS', 
                name: 'STATUS',
                className: 'text-center',
                render: function(data, type, row) {
                    let badgeClass = 'badge-danger';
                    let statusText = data == 1 ? 'SUKSES' : 'GAGAL';
                    
                    if (data == 1) {
                        badgeClass = 'badge-success';
                    }
                    
                    return '<span class="badge ' + badgeClass + '">' + statusText + '</span>';
                }
            },
            { 
                data: 'v_STAT_CORE_AGR', 
                name: 'v_STAT_CORE_AGR',
                className: 'text-center',
                render: function(data, type, row) {
                    let badgeClass = 'badge-warning';
                    let statusText = data == 1 ? 'AGREE' : 'NOT AGREE';
                    
                    if (data == 1) {
                        badgeClass = 'badge-info';
                    }
                    
                    return '<span class="badge ' + badgeClass + '">' + statusText + '</span>';
                }
            },
            { 
                data: 'v_SETTLE_RP_TAG', 
                name: 'v_SETTLE_RP_TAG',
                className: 'text-end',
                render: function(data, type, row) {
                    if (data !== null && data !== undefined) {
                        const amount = parseFloat(String(data).replace(/,/g, ''));
                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
                    }
                    return '<span class="text-muted">-</span>';
                }
            },
            { 
                data: null,
                name: 'aksi',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    return `
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-info" onclick="showDetailDispute('${row.v_ID}')">
                                <i class="fal fa-eye"></i> Detail
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" onclick="showUpdateDispute('${row.v_ID}')">
                                <i class="fal fa-edit"></i> Update
                            </button>
                        </div>
                    `;
                }
            }
        ],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[2, 'desc']],
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
            emptyTable: "Tidak ada data dispute yang tersedia",
            zeroRecords: "Tidak ditemukan data dispute yang sesuai"
        },
        responsive: true,
        searching: false,
        dom: '<"row"<"col-sm-12">>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-5"i><"col-sm-7"p>>'
    });
}

function showDetailDispute(disputeId) {
    console.log('Showing detail for dispute ID:', disputeId);
    
    // Show loading in modal
    $('#detailContent').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Memuat detail...</div>');
    $('#modalDetailDispute').modal('show');
    
    // Load detail via AJAX
    $.ajax({
        url: '{{ base_url('rekon/process/indirect-dispute/detail') }}',
        type: 'GET',
        data: { id: disputeId },
        success: function(response) {
            console.log('Detail response:', response);
            
            if (response.success && response.data) {
                const data = response.data;
                let detailHtml = `
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID:</strong></td>
                                    <td>${data.v_ID || '-'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Partner:</strong></td>
                                    <td>${data.IDPARTNER || '-'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Terminal ID:</strong></td>
                                    <td>${data.TERMINALID || '-'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Rekon:</strong></td>
                                    <td>${data.v_TGL_FILE_REKON ? new Date(data.v_TGL_FILE_REKON).toLocaleDateString('id-ID') : '-'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Group Produk:</strong></td>
                                    <td>${data.v_GROUP_PRODUK || '-'}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>IDPEL:</strong></td>
                                    <td>${data.IDPEL || '-'}</td>
                                </tr>
                                <tr>
                                    <td><strong>RP Biller Tag:</strong></td>
                                    <td>Rp ${formatNumber(data.RP_BILLER_TAG || 0)}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge ${data.STATUS == 1 ? 'badge-success' : 'badge-danger'}">
                                            ${data.STATUS == 1 ? 'SUKSES' : 'GAGAL'}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Stat Core AGR:</strong></td>
                                    <td>
                                        <span class="badge ${data.v_STAT_CORE_AGR == 1 ? 'badge-info' : 'badge-warning'}">
                                            ${data.v_STAT_CORE_AGR == 1 ? 'AGREE' : 'NOT AGREE'}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Settle Verifikasi:</strong></td>
                                    <td>${data.v_SETTLE_VERIFIKASI || '-'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Settle RP Tag:</strong></td>
                                    <td>Rp ${formatNumber(data.v_SETTLE_RP_TAG || 0)}</td>
                                </tr>
                                <tr>
                                    <td><strong>Settle RP Fee:</strong></td>
                                    <td>Rp ${formatNumber(data.v_SETTLE_RP_FEE || 0)}</td>
                                </tr>
                                <tr>
                                    <td><strong>Is Direct Fee:</strong></td>
                                    <td>${data.v_IS_DIRECT_FEE == 1 ? 'Yes' : 'No'}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6><strong>RP Biller Pokok:</strong></h6>
                            <div class="border p-3 bg-light">
                                Rp ${formatNumber(data.RP_BILLER_POKOK || 0)}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6><strong>RP Biller Denda:</strong></h6>
                            <div class="border p-3 bg-light">
                                Rp ${formatNumber(data.RP_BILLER_DENDA || 0)}
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6><strong>RP Fee Struk:</strong></h6>
                            <div class="border p-3 bg-light">
                                Rp ${formatNumber(data.RP_FEE_STRUK || 0)}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6><strong>RP Amount Struk:</strong></h6>
                            <div class="border p-3 bg-light">
                                Rp ${formatNumber(data.RP_AMOUNT_STRUK || 0)}
                            </div>
                        </div>
                    </div>
                `;
                
                $('#detailContent').html(detailHtml);
            } else {
                $('#detailContent').html('<div class="alert alert-warning">Data detail tidak ditemukan</div>');
            }
        },
        error: function(xhr) {
            console.error('Detail error:', xhr.responseText);
            $('#detailContent').html('<div class="alert alert-danger">Terjadi kesalahan saat memuat detail</div>');
        }
    });
}

function showUpdateDispute(disputeId) {
    console.log('Showing update form for dispute ID:', disputeId);
    
    // Reset form
    $('#formUpdateDispute')[0].reset();
    $('#updateDisputeId').val(disputeId);
    
    // Load current data
    $.ajax({
        url: '{{ base_url('rekon/process/indirect-dispute/detail') }}',
        type: 'GET',
        data: { id: disputeId },
        success: function(response) {
            console.log('Detail response for update:', response);
            
            if (response.success && response.data) {
                const data = response.data;
                
                // Fill readonly fields
                $('#updatePartner').val(data.IDPARTNER || '');
                $('#updateTerminalID').val(data.TERMINALID || '');
                $('#updateTanggalRekon').val(data.v_TGL_FILE_REKON ? new Date(data.v_TGL_FILE_REKON).toLocaleDateString('id-ID') : '');
                $('#updateGroupProduk').val(data.v_GROUP_PRODUK || '');
                $('#updateIDPEL').val(data.IDPEL || '');
                $('#updateAmount').val('Rp ' + formatNumber(data.RP_BILLER_TAG || 0));
                
                // Fill editable fields
                $('#updateStatus').val(data.STATUS_DISPUTE || '');
                $('#updateKeterangan').val(data.KETERANGAN || '');
                $('#updateTindakLanjut').val(data.TINDAK_LANJUT || '');
                
                $('#modalUpdateDispute').modal('show');
            } else {
                showAlert('error', 'Data dispute tidak ditemukan');
            }
        },
        error: function(xhr) {
            console.error('Detail error for update:', xhr.responseText);
            showAlert('error', 'Terjadi kesalahan saat memuat data dispute');
        }
    });
}

function resetFilters() {
    $('#tanggal').val('{{ $tanggalRekon }}');
    $('#filterPartner').val('');
    $('#filterProduk').val('');
    
    // Reload table
    if (disputeTable) {
        disputeTable.ajax.reload();
    }
    
    // Update URL
    const url = new URL(window.location);
    url.searchParams.delete('partner');
    url.searchParams.delete('produk');
    url.searchParams.set('tanggal', '{{ $tanggalRekon }}');
    window.history.pushState({}, '', url);
}

function getStatusBadgeClass(status) {
    switch((status || 'PENDING').toUpperCase()) {
        case 'PENDING': return 'badge-warning';
        case 'IN_PROGRESS': return 'badge-info';
        case 'RESOLVED': return 'badge-success';
        default: return 'badge-secondary';
    }
}

function formatNumber(num) {
    // Convert string to number first, removing any existing commas
    const cleanNum = parseFloat(String(num).replace(/,/g, '')) || 0;
    return new Intl.NumberFormat('id-ID').format(cleanNum);
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
</script>
@endpush

@push('styles')
<style>
.badge {
    font-size: 0.75em;
    padding: 0.375rem 0.75rem;
}

.badge-warning {
    background-color: #ffc107;
    color: #212529;
}

.badge-info {
    background-color: #17a2b8;
    color: #fff;
}

.badge-success {
    background-color: #28a745;
    color: #fff;
}

.badge-secondary {
    background-color: #6c757d;
    color: #fff;
}

.table thead th {
    border-top: none;
    font-weight: 600;
    background-color: #f8f9fa;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.text-end {
    text-align: right !important;
}

.table td.text-end {
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
    font-weight: bolder;
}

.text-center {
    text-align: center !important;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.modal-lg {
    max-width: 800px;
}

.form-group {
    margin-bottom: 1rem;
}

.form-label {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.text-danger {
    color: #dc3545 !important;
}

.bg-light {
    background-color: #f8f9fa !important;
}

.border {
    border: 1px solid #dee2e6 !important;
}
</style>
@endpush
