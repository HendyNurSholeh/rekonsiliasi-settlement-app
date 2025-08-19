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
                <div class="table-responsive">
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
                },
                className: 'text-right'
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
                    return data;
                }
            },
            { 
                data: 'd_CORE_REF', 
                name: 'd_CORE_REF'
            },
            { 
                data: 'd_CORE_DATETIME', 
                name: 'd_CORE_DATETIME',
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
<link rel="stylesheet" href="{{ base_url('css/settlement/settlement.css') }}">

@endpush
