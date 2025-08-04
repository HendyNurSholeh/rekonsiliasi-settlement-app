@extends('layouts.app')

@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        <i class="fal fa-check-circle"></i> {{ $title }}
        <small>Konfirmasi saldo CA untuk tanggal {{ date('d/m/Y', strtotime($tanggalRekon)) }}</small>
    </h1>
</div>

<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fal fa-info-circle"></i>
            <strong>Konfirmasi Saldo CA</strong> 
            <br>Konfirmasi ketersediaan saldo pada rekening CA sesuai dengan jumlah transaksi sukses.
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
                            <label for="filterChannel" class="form-label">Channel</label>
                            <select class="form-control" id="filterChannel" name="channel">
                                <option value="">Semua Channel</option>
                                <!-- Options akan diisi via AJAX atau dari controller -->
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterStatus" class="form-label">Status Konfirmasi</label>
                            <select class="form-control" id="filterStatus" name="status">
                                <option value="">Semua Status</option>
                                <option value="PENDING">Pending</option>
                                <option value="CONFIRMED">Confirmed</option>
                                <option value="REJECTED">Rejected</option>
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

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0" id="totalPending">0</h4>
                        <p class="card-text">Pending</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fal fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0" id="totalConfirmed">0</h4>
                        <p class="card-text">Confirmed</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fal fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0" id="totalRejected">0</h4>
                        <p class="card-text">Rejected</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fal fa-times-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0" id="totalAmount">Rp 0</h4>
                        <p class="card-text">Total Amount</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fal fa-money-bill fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Actions -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="selectAll">
                            <label class="form-check-label" for="selectAll">
                                Pilih Semua
                            </label>
                        </div>
                        <small class="text-muted ml-3">
                            <span id="selectedCount">0</span> item dipilih
                        </small>
                    </div>
                    <div class="col-md-4 text-right">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-success btn-sm" id="btnBulkConfirm" disabled>
                                <i class="fal fa-check"></i> Konfirmasi Terpilih
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" id="btnBulkReject" disabled>
                                <i class="fal fa-times"></i> Tolak Terpilih
                            </button>
                        </div>
                    </div>
                </div>
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
                    <i class="fal fa-table"></i> Data Konfirmasi Saldo CA
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="konfirmasiTable">
                        <thead class="thead-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="selectAllTable">
                                </th>
                                <th>No</th>
                                <th>Channel</th>
                                <th>Tanggal Transaksi</th>
                                <th>Jumlah Transaksi</th>
                                <th>Total Amount</th>
                                <th>Saldo CA</th>
                                <th>Status</th>
                                <th>Keterangan</th>
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

<!-- Modal Konfirmasi Saldo -->
<div class="modal fade" id="modalKonfirmasiSaldo" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fal fa-check-circle"></i> Konfirmasi Saldo CA
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formKonfirmasiSaldo">
                <div class="modal-body">
                    <input type="hidden" id="konfirmasiId" name="id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="konfirmasiChannel">Channel</label>
                                <input type="text" class="form-control" id="konfirmasiChannel" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="konfirmasiTanggal">Tanggal Transaksi</label>
                                <input type="text" class="form-control" id="konfirmasiTanggal" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="konfirmasiJumlahTrx">Jumlah Transaksi</label>
                                <input type="text" class="form-control" id="konfirmasiJumlahTrx" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="konfirmasiTotalAmount">Total Amount</label>
                                <input type="text" class="form-control" id="konfirmasiTotalAmount" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="konfirmasiSaldoCA">Saldo CA Aktual <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="konfirmasiSaldoCA" name="saldo_ca" 
                               placeholder="Masukkan saldo CA aktual" step="0.01" required>
                        <small class="form-text text-muted">
                            Masukkan saldo CA aktual berdasarkan statement bank atau sistem internal
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="konfirmasiStatus">Status Konfirmasi <span class="text-danger">*</span></label>
                        <select class="form-control" id="konfirmasiStatus" name="status" required>
                            <option value="">Pilih Status</option>
                            <option value="CONFIRMED">Confirmed - Saldo Sesuai</option>
                            <option value="REJECTED">Rejected - Saldo Tidak Sesuai</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="konfirmasiKeterangan">Keterangan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="konfirmasiKeterangan" name="keterangan" rows="4" 
                                  placeholder="Masukkan keterangan konfirmasi saldo..." required></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fal fa-info-circle"></i>
                        <strong>Informasi:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Pilih <strong>Confirmed</strong> jika saldo CA sesuai dengan total transaksi</li>
                            <li>Pilih <strong>Rejected</strong> jika terdapat selisih yang perlu ditindaklanjuti</li>
                            <li>Pastikan keterangan diisi dengan detail yang jelas</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fal fa-save"></i> Simpan Konfirmasi
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
let konfirmasiTable;
let selectedRows = [];

$(document).ready(function() {
    // Refresh CSRF token saat page load untuk memastikan token fresh
    refreshCSRFToken().then(function() {
        console.log('CSRF token refreshed on page load');
        
        // Initialize DataTable dengan AJAX
        initializeDataTable();
        
        // Load summary statistics
        loadSummaryStats();
    });
    
    // Handle form submit for filters
    $('form').on('submit', function(e) {
        e.preventDefault();
        
        console.log('Form submit - Reloading DataTable with filters');
        
        if (konfirmasiTable) {
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
            konfirmasiTable.ajax.reload();
            loadSummaryStats();
        }
    });
    
    // Handle select all checkbox
    $('#selectAll, #selectAllTable').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.row-checkbox').prop('checked', isChecked);
        updateSelectedRows();
        updateBulkButtons();
    });
    
    // Handle individual row checkbox
    $(document).on('change', '.row-checkbox', function() {
        updateSelectedRows();
        updateBulkButtons();
        
        // Update select all checkboxes
        const totalCheckboxes = $('.row-checkbox').length;
        const checkedCheckboxes = $('.row-checkbox:checked').length;
        
        $('#selectAll, #selectAllTable').prop('checked', totalCheckboxes === checkedCheckboxes);
        $('#selectAll, #selectAllTable').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
    });
    
    // Handle bulk confirm
    $('#btnBulkConfirm').on('click', function() {
        if (selectedRows.length === 0) {
            showAlert('warning', 'Pilih data yang akan dikonfirmasi');
            return;
        }
        
        Swal.fire({
            title: 'Konfirmasi Bulk',
            text: `Konfirmasi ${selectedRows.length} data terpilih?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Konfirmasi',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                processBulkAction('CONFIRMED');
            }
        });
    });
    
    // Handle bulk reject
    $('#btnBulkReject').on('click', function() {
        if (selectedRows.length === 0) {
            showAlert('warning', 'Pilih data yang akan ditolak');
            return;
        }
        
        Swal.fire({
            title: 'Tolak Bulk',
            text: `Tolak ${selectedRows.length} data terpilih?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Tolak',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                processBulkAction('REJECTED');
            }
        });
    });
    
    // Handle form submit for konfirmasi saldo
    $('#formKonfirmasiSaldo').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        console.log('Submitting konfirmasi saldo');
        
        $.ajax({
            url: '{{ base_url('rekon/process/konfirmasi-saldo-ca') }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Konfirmasi response:', response);
                
                if (response.success) {
                    showAlert('success', response.message);
                    $('#modalKonfirmasiSaldo').modal('hide');
                    konfirmasiTable.ajax.reload();
                    loadSummaryStats();
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function(xhr) {
                console.error('Konfirmasi error:', xhr.responseText);
                showAlert('error', 'Terjadi kesalahan saat memproses konfirmasi');
            }
        });
    });
});

function initializeDataTable() {
    konfirmasiTable = $('#konfirmasiTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ base_url('rekon/process/konfirmasi-saldo-ca/datatable') }}',
            type: 'GET',
            data: function(d) {
                // Add current filters
                d.tanggal = $('#tanggal').val() || '{{ $tanggalRekon }}';
                d.channel = $('#filterChannel').val();
                d.status = $('#filterStatus').val();
                
                console.log('DataTable request data:', d);
                return d;
            },
            error: function(xhr, error, thrown) {
                console.error('DataTable AJAX Error:', error, thrown, xhr.responseText);
                if (xhr.status === 403 || xhr.status === 419) {
                    console.log('CSRF error in DataTable, refreshing token...');
                    refreshCSRFToken().then(function() {
                        console.log('CSRF refreshed, reloading DataTable...');
                        konfirmasiTable.ajax.reload();
                    });
                }
            }
        },
        columns: [
            { 
                data: null,
                name: 'checkbox',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `<input type="checkbox" class="row-checkbox" value="${row.ID || row.CHANNEL}">`;
                }
            },
            { 
                data: null,
                name: 'no',
                orderable: false,
                searchable: false,
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { data: 'CHANNEL', name: 'CHANNEL' },
            { 
                data: 'TANGGAL_TRX', 
                name: 'TANGGAL_TRX',
                render: function(data, type, row) {
                    if (data) {
                        const date = new Date(data);
                        return date.toLocaleDateString('id-ID');
                    }
                    return '-';
                }
            },
            { 
                data: 'JUMLAH_TRANSAKSI', 
                name: 'JUMLAH_TRANSAKSI',
                className: 'text-center',
                render: function(data, type, row) {
                    return new Intl.NumberFormat('id-ID').format(data || 0);
                }
            },
            { 
                data: 'TOTAL_AMOUNT', 
                name: 'TOTAL_AMOUNT',
                className: 'text-end',
                render: function(data, type, row) {
                    const amount = parseFloat(String(data || 0).replace(/,/g, ''));
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
                }
            },
            { 
                data: 'SALDO_CA', 
                name: 'SALDO_CA',
                className: 'text-end',
                render: function(data, type, row) {
                    if (data !== null && data !== undefined) {
                        const amount = parseFloat(String(data).replace(/,/g, ''));
                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
                    }
                    return '<span class="text-muted">Belum diinput</span>';
                }
            },
            { 
                data: 'STATUS_KONFIRMASI', 
                name: 'STATUS_KONFIRMASI',
                className: 'text-center',
                render: function(data, type, row) {
                    let badgeClass = 'badge-warning';
                    let statusText = data || 'PENDING';
                    
                    switch(statusText.toUpperCase()) {
                        case 'PENDING':
                            badgeClass = 'badge-warning';
                            break;
                        case 'CONFIRMED':
                            badgeClass = 'badge-success';
                            break;
                        case 'REJECTED':
                            badgeClass = 'badge-danger';
                            break;
                    }
                    
                    return '<span class="badge ' + badgeClass + '">' + statusText + '</span>';
                }
            },
            { 
                data: 'KETERANGAN', 
                name: 'KETERANGAN',
                render: function(data, type, row) {
                    if (data && data.length > 50) {
                        return data.substring(0, 50) + '...';
                    }
                    return data || '-';
                }
            },
            { 
                data: null,
                name: 'aksi',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    const status = (row.STATUS_KONFIRMASI || 'PENDING').toUpperCase();
                    
                    if (status === 'PENDING') {
                        return `
                            <button type="button" class="btn btn-sm btn-primary" onclick="showKonfirmasiModal('${row.ID || row.CHANNEL}')">
                                <i class="fal fa-check-circle"></i> Konfirmasi
                            </button>
                        `;
                    } else {
                        return `
                            <span class="badge badge-secondary">
                                <i class="fal fa-lock"></i> ${status}
                            </span>
                        `;
                    }
                }
            }
        ],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[2, 'asc']],
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
            emptyTable: "Tidak ada data konfirmasi yang tersedia",
            zeroRecords: "Tidak ditemukan data konfirmasi yang sesuai"
        },
        responsive: true,
        searching: false,
        dom: '<"row"<"col-sm-12">>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-5"i><"col-sm-7"p>>'
    });
}

function loadSummaryStats() {
    // Load summary statistics
    const filters = {
        tanggal: $('#tanggal').val() || '{{ $tanggalRekon }}',
        channel: $('#filterChannel').val(),
        status: $('#filterStatus').val()
    };
    
    $.ajax({
        url: '{{ base_url('rekon/process/konfirmasi-saldo-ca/summary') }}',
        type: 'GET',
        data: filters,
        success: function(response) {
            if (response.success && response.data) {
                const data = response.data;
                $('#totalPending').text(data.total_pending || 0);
                $('#totalConfirmed').text(data.total_confirmed || 0);
                $('#totalRejected').text(data.total_rejected || 0);
                $('#totalAmount').text('Rp ' + formatNumber(data.total_amount || 0));
            }
        },
        error: function(xhr) {
            console.error('Summary stats error:', xhr.responseText);
        }
    });
}

function showKonfirmasiModal(id) {
    console.log('Showing konfirmasi modal for ID:', id);
    
    // Reset form
    $('#formKonfirmasiSaldo')[0].reset();
    $('#konfirmasiId').val(id);
    
    // Load current data (implementasi tergantung struktur data)
    // Untuk sementara, kita bisa ambil dari table row yang ada
    const tableData = konfirmasiTable.row(function(idx, data, node) {
        return data.ID === id || data.CHANNEL === id;
    }).data();
    
    if (tableData) {
        $('#konfirmasiChannel').val(tableData.CHANNEL || '');
        $('#konfirmasiTanggal').val(tableData.TANGGAL_TRX ? new Date(tableData.TANGGAL_TRX).toLocaleDateString('id-ID') : '');
        $('#konfirmasiJumlahTrx').val(formatNumber(tableData.JUMLAH_TRANSAKSI || 0));
        $('#konfirmasiTotalAmount').val('Rp ' + formatNumber(tableData.TOTAL_AMOUNT || 0));
        
        if (tableData.SALDO_CA !== null && tableData.SALDO_CA !== undefined) {
            $('#konfirmasiSaldoCA').val(tableData.SALDO_CA);
        }
        
        $('#modalKonfirmasiSaldo').modal('show');
    } else {
        showAlert('error', 'Data tidak ditemukan');
    }
}

function updateSelectedRows() {
    selectedRows = [];
    $('.row-checkbox:checked').each(function() {
        selectedRows.push($(this).val());
    });
    
    $('#selectedCount').text(selectedRows.length);
}

function updateBulkButtons() {
    const hasSelection = selectedRows.length > 0;
    $('#btnBulkConfirm, #btnBulkReject').prop('disabled', !hasSelection);
}

function processBulkAction(status) {
    const action = status === 'CONFIRMED' ? 'konfirmasi' : 'tolak';
    
    $.ajax({
        url: '{{ base_url('rekon/process/konfirmasi-saldo-ca/bulk') }}',
        type: 'POST',
        data: {
            ids: selectedRows,
            status: status,
            keterangan: `Bulk ${action} - ${new Date().toLocaleString('id-ID')}`
        },
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                
                // Reset selections
                selectedRows = [];
                $('.row-checkbox').prop('checked', false);
                $('#selectAll, #selectAllTable').prop('checked', false);
                updateBulkButtons();
                
                // Reload table and stats
                konfirmasiTable.ajax.reload();
                loadSummaryStats();
            } else {
                showAlert('error', response.message);
            }
        },
        error: function(xhr) {
            console.error('Bulk action error:', xhr.responseText);
            showAlert('error', `Terjadi kesalahan saat memproses bulk ${action}`);
        }
    });
}

function resetFilters() {
    $('#tanggal').val('{{ $tanggalRekon }}');
    $('#filterChannel').val('');
    $('#filterStatus').val('');
    
    // Reload table and stats
    if (konfirmasiTable) {
        konfirmasiTable.ajax.reload();
        loadSummaryStats();
    }
    
    // Update URL
    const url = new URL(window.location);
    url.searchParams.delete('channel');
    url.searchParams.delete('status');
    url.searchParams.set('tanggal', '{{ $tanggalRekon }}');
    window.history.pushState({}, '', url);
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

.badge-success {
    background-color: #28a745;
    color: #fff;
}

.badge-danger {
    background-color: #dc3545;
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

.form-check-input:indeterminate {
    background-color: #007bff;
    border-color: #007bff;
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

.text-muted {
    color: #6c757d !important;
}

.bg-info {
    background-color: #17a2b8 !important;
}

.bg-success {
    background-color: #28a745 !important;
}

.bg-danger {
    background-color: #dc3545 !important;
}

.bg-primary {
    background-color: #007bff !important;
}

.fa-2x {
    font-size: 2em;
}

.card-body .d-flex {
    display: flex !important;
}

.justify-content-between {
    justify-content: space-between !important;
}

.align-self-center {
    align-self: center !important;
}

.me-2 {
    margin-right: 0.5rem !important;
}

.ml-3 {
    margin-left: 1rem !important;
}

.mt-2 {
    margin-top: 0.5rem !important;
}

.mb-0 {
    margin-bottom: 0 !important;
}

.text-right {
    text-align: right !important;
}
</style>
@endpush
