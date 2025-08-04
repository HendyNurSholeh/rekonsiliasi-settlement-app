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
                            <label for="tanggal" class="form-label">Tanggal Rekonsiliasi <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                   value="{{ $tanggalRekon }}" required>
                        </div>
                        <div class="col-md-3">
                            <label for="status_biller" class="form-label">Status Biller</label>
                            <select class="form-control" id="status_biller" name="status_biller">
                                <option value="">Semua Status</option>
                                <option value="0">Pending</option>
                                <option value="1">Sukses</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="status_core" class="form-label">Status Core</label>
                            <select class="form-control" id="status_core" name="status_core">
                                <option value="">Semua Status</option>
                                <option value="0">Tidak Terdebet</option>
                                <option value="1">Terdebet</option>
                                <option value="2">Belum Diproses</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2">
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
                                <th>Partner</th>
                                <th>Terminal ID</th>
                                <th>Produk</th>
                                <th>IDPEL</th>
                                <th>RP Biller Tag</th>
                                <th>Status Biller</th>
                                <th>Status Core</th>
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

<!-- Modal Proses Data Dispute -->
<div class="modal fade" id="modalProsesDispute" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fal fa-wrench"></i> Proses Data Dispute
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formProsesDispute">
                <div class="modal-body">
                    <input type="hidden" id="prosesVId" name="v_id">
                    
                    <!-- Data Transaksi -->
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fal fa-info-circle"></i> A. Data Transaksi</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Partner</label>
                                        <input type="text" class="form-control" id="prosesPartner" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Terminal ID</label>
                                        <input type="text" class="form-control" id="prosesTerminalID" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Group Produk</label>
                                        <input type="text" class="form-control" id="prosesGroupProduk" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>IDPEL</label>
                                        <input type="text" class="form-control" id="prosesIDPEL" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Data Tagihan -->
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fal fa-money-bill"></i> B. Data Tagihan</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>RP Biller Pokok</label>
                                        <input type="text" class="form-control" id="prosesBillerPokok" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>RP Biller Denda</label>
                                        <input type="text" class="form-control" id="prosesBillerDenda" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>RP Biller Tag</label>
                                        <input type="text" class="form-control" id="prosesBillerTag" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Rekonsiliasi -->
                    <div class="card mb-3">
                        <div class="card-header bg-warning text-white">
                            <h6 class="mb-0"><i class="fal fa-cog"></i> Status Rekonsiliasi</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- IDPARTNER Select -->
                                <div class="col-md-12 mb-3">
                                    <div class="form-group">
                                        <label for="prosesIDPartnerSelect">IDPARTNER <span class="text-danger">*</span></label>
                                        <select class="form-control" id="prosesIDPartnerSelect" name="idpartner" required>
                                            <option value="">Pilih Partner</option>
                                            <option value="CHANNEL KON">CHANNEL KON</option>
                                            <option value="CHANNEL SYA">CHANNEL SYA</option>
                                            <option value="VA DIGITAL KON">VA DIGITAL KON</option>
                                            <option value="VA DIGITAL SYA">VA DIGITAL SYA</option>
                                            <option value="PPOB KON">PPOB KON</option>
                                            <option value="PPOB SYA">PPOB SYA</option>
                                            <option value="MITRACOMM">MITRACOMM</option>
                                            <option value="POS INDONESIA">POS INDONESIA</option>
                                            <option value="GO-PAY">GO-PAY</option>
                                            <option value="ARTAJASA">ARTAJASA</option>
                                            <option value="PDAM BARITO KUALA">PDAM BARITO KUALA</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <!-- STATUS BILLER -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Status Biller <span class="text-danger">*</span></label>
                                        <div class="mt-2">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="status_biller" id="statusBiller1" value="1" required>
                                                <label class="form-check-label" for="statusBiller1">
                                                    <span class="badge badge-success">Sukses (1)</span>
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="status_biller" id="statusBiller0" value="0" required>
                                                <label class="form-check-label" for="statusBiller0">
                                                    <span class="badge badge-warning">Pending (0)</span>
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="status_biller" id="statusBiller2" value="2" required>
                                                <label class="form-check-label" for="statusBiller2">
                                                    <span class="badge badge-danger">Gagal (2)</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- STATUS CORE -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Status Core <span class="text-danger">*</span></label>
                                        <div class="mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="status_core" id="statusCore1" value="1" required>
                                                <label class="form-check-label" for="statusCore1">
                                                    <span class="badge badge-info">Terdebet (1)</span>
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="status_core" id="statusCore0" value="0" required>
                                                <label class="form-check-label" for="statusCore0">
                                                    <span class="badge badge-danger">Tidak Terdebet (0)</span>
                                                </label>
                                            </div>
                                            {{-- <div class="form-check">
                                                <input class="form-check-input" type="radio" name="status_core" id="statusCore2" value="2" required>
                                                <label class="form-check-label" for="statusCore2">
                                                    <span class="badge badge-secondary">Belum Diproses (2)</span>
                                                </label>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- STATUS SETTLEMENT -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Status Settlement <span class="text-danger">*</span></label>
                                        <div class="mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="status_settlement" id="statusSettlement1" value="1" required>
                                                <label class="form-check-label" for="statusSettlement1">
                                                    <span class="badge badge-primary">Dilimpahkan (1)</span>
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="status_settlement" id="statusSettlement9" value="9" required>
                                                <label class="form-check-label" for="statusSettlement9">
                                                    <span class="badge badge-danger">Transaksi Gagal (9)</span>
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="status_settlement" id="statusSettlement8" value="8" required>
                                                <label class="form-check-label" for="statusSettlement8">
                                                    <span class="badge badge-secondary">Transaksi di Revershal (8)</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fal fa-save"></i> Simpan
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
    // Set initial filter values from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status_biller')) {
        $('#status_biller').val(urlParams.get('status_biller'));
    }
    if (urlParams.get('status_core')) {
        $('#status_core').val(urlParams.get('status_core'));
    }
    
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
    
    // Handle form submit for proses dispute
    $('#formProsesDispute').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const vId = $('#prosesVId').val();
        
        console.log('Submitting proses dispute for v_ID:', vId);
        
        // Debug: Log all form data
        console.log('Form data contents:');
        for (let [key, value] of formData.entries()) {
            console.log(key + ': ' + value);
        }
        
        // Validasi form dengan logging detail
        const idpartner = formData.get('idpartner');
        const statusBiller = formData.get('status_biller');
        const statusCore = formData.get('status_core');
        const statusSettlement = formData.get('status_settlement');
        
        console.log('Validation check:');
        console.log('idpartner:', idpartner);
        console.log('status_biller:', statusBiller);
        console.log('status_core:', statusCore);
        console.log('status_settlement:', statusSettlement);
        
        if (!idpartner || !statusBiller || !statusCore || !statusSettlement) {
            let missingFields = [];
            if (!idpartner) missingFields.push('IDPARTNER');
            if (!statusBiller) missingFields.push('Status Biller');
            if (!statusCore) missingFields.push('Status Core');
            if (!statusSettlement) missingFields.push('Status Settlement');
            
            showAlert('error', 'Harap lengkapi field yang diperlukan: ' + missingFields.join(', '));
            return;
        }
        
        $.ajax({
            url: '{{ base_url('rekon/process/indirect-dispute/update') }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Proses response:', response);
                
                if (response.success) {
                    showAlert('success', response.message);
                    $('#modalProsesDispute').modal('hide');
                    disputeTable.ajax.reload();
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function(xhr) {
                console.error('Proses error:', xhr.responseText);
                showAlert('error', 'Terjadi kesalahan saat memproses data dispute');
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
                // Add current filters sesuai arahan senior
                d.tanggal = $('#tanggal').val() || '{{ $tanggalRekon }}';
                d.status_biller = $('#status_biller').val();
                d.status_core = $('#status_core').val();
                
                console.log('DataTable request data:');
                console.log('- tanggal:', d.tanggal);
                console.log('- status_biller:', d.status_biller);
                console.log('- status_core:', d.status_core);
                console.log('Full request data:', d);
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
            { data: 'IDPARTNER', name: 'IDPARTNER' },
            { data: 'TERMINALID', name: 'TERMINALID' },
            { data: 'PRODUK', name: 'PRODUK' },
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
                data: 'STATUS_BILLER', 
                name: 'STATUS_BILLER',
                className: 'text-center',
                render: function(data, type, row) {
                    let badgeClass = 'badge-warning';
                    let statusText = 'Pending';
                    
                    if (data == 1) {
                        badgeClass = 'badge-success';
                        statusText = 'Sukses';
                    }
                    
                    return '<span class="badge ' + badgeClass + '">' + statusText + '</span>';
                }
            },
            { 
                data: 'STATUS_CORE', 
                name: 'STATUS_CORE',
                className: 'text-center',
                render: function(data, type, row) {
                    let badgeClass = 'badge-secondary';
                    let statusText = 'Belum Diproses';
                    
                    if (data == 1) {
                        badgeClass = 'badge-info';
                        statusText = 'Terdebet';
                    } else if (data == 0) {
                        badgeClass = 'badge-danger';
                        statusText = 'Tidak Terdebet';
                    }
                    
                    return '<span class="badge ' + badgeClass + '">' + statusText + '</span>';
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
                        <button type="button" class="btn btn-sm btn-primary" onclick="showProsesDispute('${row.v_ID}')">
                            <i class="fal fa-wrench"></i> Proses Data Dispute
                        </button>
                    `;
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

function showProsesDispute(vId) {
    console.log('Showing proses dispute form for v_ID:', vId);
    
    // Reset form
    $('#formProsesDispute')[0].reset();
    $('#prosesVId').val(vId);
    
    // Load current data
    $.ajax({
        url: '{{ base_url('rekon/process/indirect-dispute/detail') }}',
        type: 'GET',
        data: { id: vId },
        success: function(response) {
            console.log('Detail response for proses:', response);
            
            if (response.success && response.data) {
                const data = response.data;
                
                // Fill readonly fields - Data Transaksi
                $('#prosesPartner').val(data.IDPARTNER || '');
                $('#prosesTerminalID').val(data.TERMINALID || '');
                $('#prosesGroupProduk').val(data.v_GROUP_PRODUK || '');
                $('#prosesIDPEL').val(data.IDPEL || '');
                
                // Fill readonly fields - Data Tagihan
                $('#prosesBillerPokok').val('Rp ' + formatNumber(data.RP_BILLER_POKOK || 0));
                $('#prosesBillerDenda').val('Rp ' + formatNumber(data.RP_BILLER_DENDA || 0));
                $('#prosesBillerTag').val('Rp ' + formatNumber(data.RP_BILLER_TAG || 0));
                
                // Set selected values for form fields
                // IDPARTNER Select - set to current partner value
                $('#prosesIDPartnerSelect').val(data.IDPARTNER || '');
                
                // Status Biller Radio - set to current status
                const currentStatusBiller = data.STATUS;
                if (currentStatusBiller !== null && currentStatusBiller !== undefined) {
                    $('input[name="status_biller"][value="' + currentStatusBiller + '"]').prop('checked', true);
                }
                
                // Status Core Radio - set to current status
                const currentStatusCore = data.v_STAT_CORE_AGR;
                if (currentStatusCore !== null && currentStatusCore !== undefined) {
                    $('input[name="status_core"][value="' + currentStatusCore + '"]').prop('checked', true);
                }
                
                // Status Settlement Radio - leave unselected (user must choose)
                // This is intentionally left empty as user needs to select the appropriate action
                
                $('#modalProsesDispute').modal('show');
            } else {
                showAlert('error', 'Data dispute tidak ditemukan');
            }
        },
        error: function(xhr) {
            console.error('Detail error for proses:', xhr.responseText);
            showAlert('error', 'Terjadi kesalahan saat memuat data dispute');
        }
    });
}

function resetFilters() {
    $('#tanggal').val('{{ $tanggalRekon }}');
    $('#status_biller').val('');
    $('#status_core').val('');
    
    // Reload table
    if (disputeTable) {
        disputeTable.ajax.reload();
    }
    
    // Update URL
    const url = new URL(window.location);
    url.searchParams.delete('status_biller');
    url.searchParams.delete('status_core');
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
    background-color: #d39e00;
    color: #fff;
}

.badge-info {
    background-color: #17a2b8;
    color: #fff;
}

.badge-success {
    background-color: #28a745;
    color: #fff;
}

.badge-danger {
    background-color: #dc3545;
    color: #fff;
}

.badge-primary {
    background-color: #007bff;
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

.modal-xl {
    max-width: 1140px;
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

/* Card headers with colors */
.card-header.bg-primary {
    background-color: #007bff !important;
}

.card-header.bg-success {
    background-color: #28a745 !important;
}

.card-header.bg-warning {
    background-color: #d39e00 !important;
}

/* Form check styling */
.form-check {
    margin-bottom: 0.5rem;
}

.form-check-input {
    margin-right: 0.5rem;
}

.form-check-label {
    display: flex;
    align-items: center;
}

.form-check-inline {
    margin-right: 1rem;
}

/* Radio button badges styling */
.form-check-label .badge {
    margin-left: 0.25rem;
}

/* Modal body spacing */
.modal-body .card {
    border: 1px solid #dee2e6;
}

.modal-body .card-header {
    padding: 0.75rem 1rem;
}

.modal-body .card-body {
    padding: 1rem;
}

/* Required field indicator */
.text-danger {
    font-weight: bold;
}

/* Status badge spacing in form */
.form-check-label .badge {
    min-width: 100px;
    text-align: center;
}
</style>
@endpush
