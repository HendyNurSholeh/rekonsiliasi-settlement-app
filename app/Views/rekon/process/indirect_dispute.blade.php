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
                <form id="form-filter" method="GET" action="{{ current_url() }}">
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
    return $.get('{{ base_url('get-csrf-token') }}').then(function(response) {
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
    $('#form-filter').on('submit', function(e) {
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
        
        const vId = $('#prosesVId').val();
        
        // Ambil data langsung dari form yang terselected, bukan dari FormData
        const idpartner = $('#prosesIDPartnerSelect').val();
        const statusBiller = $('input[name="status_biller"]:checked').val();
        const statusCore = $('input[name="status_core"]:checked').val();
        const statusSettlement = $('input[name="status_settlement"]:checked').val();
        
        console.log('Submitting proses dispute for v_ID:', vId);
        console.log('Selected form values:');
        console.log('- idpartner:', idpartner);
        console.log('- status_biller:', statusBiller);
        console.log('- status_core:', statusCore);
        console.log('- status_settlement:', statusSettlement);
        
        // Validasi form berdasarkan nilai yang terselected
        if (!idpartner || !statusBiller || !statusCore || !statusSettlement) {
            let missingFields = [];
            
            // Reset semua border field
            $('#prosesIDPartnerSelect').removeClass('is-invalid');
            $('input[name="status_biller"]').closest('.form-group').removeClass('has-error');
            $('input[name="status_core"]').closest('.form-group').removeClass('has-error');
            $('input[name="status_settlement"]').closest('.form-group').removeClass('has-error');
            
            // Tandai field yang kosong
            if (!idpartner) {
                missingFields.push('IDPARTNER');
                $('#prosesIDPartnerSelect').addClass('is-invalid');
            }
            if (!statusBiller) {
                missingFields.push('Status Biller');
                $('input[name="status_biller"]').closest('.form-group').addClass('has-error');
            }
            if (!statusCore) {
                missingFields.push('Status Core');
                $('input[name="status_core"]').closest('.form-group').addClass('has-error');
            }
            if (!statusSettlement) {
                missingFields.push('Status Settlement');
                $('input[name="status_settlement"]').closest('.form-group').addClass('has-error');
            }
            
            toastr["error"]('Harap lengkapi field yang diperlukan: ' + missingFields.join(', '));
            return;
        }
        
        // Clear any previous validation errors
        $('#prosesIDPartnerSelect').removeClass('is-invalid');
        $('input[name="status_biller"]').closest('.form-group').removeClass('has-error');
        $('input[name="status_core"]').closest('.form-group').removeClass('has-error');
        $('input[name="status_settlement"]').closest('.form-group').removeClass('has-error');
        
        // Buat FormData dengan data yang sudah divalidasi
        const formData = new FormData();
        formData.append('v_id', vId);
        formData.append('idpartner', idpartner);
        formData.append('status_biller', statusBiller);
        formData.append('status_core', statusCore);
        formData.append('status_settlement', statusSettlement);
        
        // Pre-refresh CSRF token sebelum submit untuk mencegah 403 pertama
        refreshCSRFToken().then(function() {
            console.log('CSRF refreshed before submit, current token:', currentCSRF);
            
            // Update FormData dengan token yang fresh
            formData.set('csrf_test_name', currentCSRF);
            
            $.ajax({
                url: `{{ base_url('rekon/process/indirect-dispute/update') }}`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log('Proses response:', response);
                    
                    if (response.success) {
                        toastr["success"](response.message);
                        $('#modalProsesDispute').modal('hide');
                        disputeTable.ajax.reload();
                    } else {
                        toastr["error"](response.message);
                    }
                },
                error: function(xhr) {
                    console.error('Proses error:', xhr.responseText);
                    toastr["error"]('Terjadi kesalahan saat memproses data dispute');
                }
            });
        }).catch(function(error) {
            console.error('Failed to refresh CSRF before submit:', error);
            toastr["error"]('Gagal menyegarkan token keamanan. Silakan coba lagi.');
        });
    });
    
    // Clear validation errors saat user mengisi form
    $('#prosesIDPartnerSelect').on('change', function() {
        $(this).removeClass('is-invalid');
    });
    
    $('input[name="status_biller"]').on('change', function() {
        $(this).closest('.form-group').removeClass('has-error');
    });
    
    $('input[name="status_core"]').on('change', function() {
        $(this).closest('.form-group').removeClass('has-error');
    });
    
    $('input[name="status_settlement"]').on('change', function() {
        $(this).closest('.form-group').removeClass('has-error');
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
    
    // Reset form dan clear validation errors
    $('#formProsesDispute')[0].reset();
    $('#prosesVId').val(vId);
    
    // Clear any validation errors
    $('#prosesIDPartnerSelect').removeClass('is-invalid');
    $('input[name="status_biller"]').closest('.form-group').removeClass('has-error');
    $('input[name="status_core"]').closest('.form-group').removeClass('has-error');
    $('input[name="status_settlement"]').closest('.form-group').removeClass('has-error');
    
    // Pre-refresh CSRF token sebelum load data
    refreshCSRFToken().then(function() {
        console.log('CSRF refreshed before loading modal data');
        
        // Load current data
        $.ajax({
            url: '{{ base_url('rekon/process/indirect-dispute/detail') }}',
            type: 'POST',
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
                    toastr["error"]('Data dispute tidak ditemukan');
                }
            },
            error: function(xhr) {
                console.error('Detail error for proses:', xhr.responseText);
                toastr["error"]('Terjadi kesalahan saat memuat data dispute');
            }
        });
    }).catch(function() {
        toastr["error"]('Gagal menyegarkan token keamanan. Silakan coba lagi.');
    });
}

function resetFilters() {
    // Remove 'tanggal' parameter from URL and redirect
    const url = new URL(window.location);
    url.searchParams.delete('tanggal');
    url.searchParams.delete('status_biller');
    url.searchParams.delete('status_core');
    window.location.href = url.pathname + url.search;
}

function formatNumber(num) {
    // Convert string to number first, removing any existing commas
    const cleanNum = parseFloat(String(num).replace(/,/g, '')) || 0;
    return new Intl.NumberFormat('id-ID').format(cleanNum);
}
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="{{ base_url('css/rekon/process/indirect_dispute.css') }}">
<style>
.is-invalid {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

.has-error .form-check-label {
    color: #dc3545;
    font-weight: bold;
}

.has-error .badge {
    border: 2px solid #dc3545;
}
</style>
@endpush
