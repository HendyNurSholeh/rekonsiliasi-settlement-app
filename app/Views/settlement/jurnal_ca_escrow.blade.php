@extends('layouts.app')

@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        <i class="fal fa-exchange-alt"></i> {{ $title }}
        <small>Jurnal CA to Escrow untuk tanggal {{ date('d/m/Y', strtotime($tanggalData)) }}</small>
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
                    <i class="fal fa-table"></i> Data Jurnal CA to Escrow
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-sm" id="jurnalCaEscrowTable">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Kode Settle</th>
                                <th>Nama Produk</th>
                                <th>Amount Escrow</th>
                                <th>Total Jurnal</th>
                                <th>Jurnal Pending</th>
                                <th>Jurnal Sukses</th>
                                <th>No. Ref</th>
                                <th>Debit Account</th>
                                <th>Debit Name</th>
                                <th>Credit Account</th>
                                <th>Credit Name</th>
                                <th>Amount</th>
                                <th>Response Code</th>
                                <th>Core Ref</th>
                                <th>Core DateTime</th>
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
let jurnalCaEscrowTable;

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
        
        if (tanggal && jurnalCaEscrowTable) {
            // Update current URL parameters
            const url = new URL(window.location);
            url.searchParams.set('tanggal', tanggal);
            window.history.pushState({}, '', url);
            
            console.log('Updated URL:', url.toString());
            
            // Reload data tetap di halaman yang sama
            jurnalCaEscrowTable.ajax.reload(null, false);
        }
    });
});

function initializeDataTable() {
    jurnalCaEscrowTable = $('#jurnalCaEscrowTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ base_url('settlement/jurnal-ca-escrow/datatable') }}',
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
                        jurnalCaEscrowTable.ajax.reload();
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
                responsivePriority: 1, // Always visible
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { 
                data: 'r_KD_SETTLE', 
                name: 'r_KD_SETTLE',
                responsivePriority: 2, // High priority - visible on most screens
                render: function(data, type, row) {
                    return '<code>' + (data || '') + '</code>';
                }
            },
            { 
                data: 'r_NAMA_PRODUK', 
                name: 'r_NAMA_PRODUK',
                responsivePriority: 3 // Medium priority
            },
            { 
                data: 'r_AMOUNT_ESCROW', 
                name: 'r_AMOUNT_ESCROW',
                responsivePriority: 4, // Medium priority
                render: function(data, type, row) {
                    return formatCurrency(data);
                },
                className: 'text-right'
            },
            { 
                data: 'r_TOTAL_JURNAL', 
                name: 'r_TOTAL_JURNAL',
                responsivePriority: 5, // Hidden in responsive - goes to details
                className: 'text-center'
            },
            { 
                data: 'r_JURNAL_PENDING', 
                name: 'r_JURNAL_PENDING',
                responsivePriority: 6, // Medium priority
                className: 'text-center',
                render: function(data, type, row) {
                    if (parseInt(data) > 0) {
                        return '<span class="badge badge-warning">' + data + '</span>';
                    }
                    return data;
                }
            },
            { 
                data: 'r_JURNAL_SUKSES', 
                name: 'r_JURNAL_SUKSES',
                responsivePriority: 7, // Medium priority
                className: 'text-center',
                render: function(data, type, row) {
                    if (parseInt(data) > 0) {
                        return '<span class="badge badge-success">' + data + '</span>';
                    }
                    return data;
                }
            },
            { 
                data: 'd_NO_REF', 
                name: 'd_NO_REF',
                responsivePriority: 10001 // Hidden in responsive - goes to details
            },
            { 
                data: 'd_DEBIT_ACCOUNT', 
                name: 'd_DEBIT_ACCOUNT',
                responsivePriority: 10002 // Hidden in responsive - goes to details
            },
            { 
                data: 'd_DEBIT_NAME', 
                name: 'd_DEBIT_NAME',
                responsivePriority: 10003 // Hidden in responsive - goes to details
            },
            { 
                data: 'd_CREDIT_ACCOUNT', 
                name: 'd_CREDIT_ACCOUNT',
                responsivePriority: 10004 // Hidden in responsive - goes to details
            },
            { 
                data: 'd_CREDIT_NAME', 
                name: 'd_CREDIT_NAME',
                responsivePriority: 10005 // Hidden in responsive - goes to details
            },
            { 
                data: 'd_AMOUNT', 
                name: 'd_AMOUNT',
                responsivePriority: 10006, // Medium-low priority
                render: function(data, type, row) {
                    return formatCurrency(data);
                },
                className: 'text-right'
            },
            { 
                data: 'd_CODE_RES', 
                name: 'd_CODE_RES',
                responsivePriority: 10007, // Lower priority
                render: function(data, type, row) {
                    if (data && data.startsWith('00')) {
                        return '<span class="badge badge-success">' + data + '</span>';
                    } else if (data) {
                        return '<span class="badge badge-danger">' + data + '</span>';
                    }
                    return data;
                }
            },
            { 
                data: 'd_CORE_REF', 
                name: 'd_CORE_REF',
                responsivePriority: 10008 // Hidden in responsive - goes to details
            },
            { 
                data: 'd_CORE_DATETIME', 
                name: 'd_CORE_DATETIME',
                responsivePriority: 10009, // Hidden in responsive - goes to details
                render: function(data, type, row) {
                    if (data) {
                        return new Date(data).toLocaleString('id-ID');
                    }
                    return '';
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
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="{{ base_url('assets/css/toastr.min.css') }}">
<link rel="stylesheet" href="{{ base_url('css/settlement/settlement.css') }}">
<style>
.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    border: 1px solid #e3e6f0;
}

/* Table styling */
#jurnalCaEscrowTable {
    font-size: 0.875rem;
}

#jurnalCaEscrowTable th {
    background-color: #f8f9fc;
    border-top: 1px solid #e3e6f0;
    font-weight: 600;
    font-size: 0.75rem;
    white-space: nowrap;
}

#jurnalCaEscrowTable td {
    vertical-align: middle;
    font-size: 0.8rem;
}

.badge {
    font-size: 0.7rem;
    padding: 0.25em 0.5em;
}
</style>
@endpush
