@extends('layouts.app')

@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        <i class="fal fa-chart-line"></i> {{ $title }}
        <small>Perbandingan data detail vs rekap untuk tanggal {{ date('d/m/Y', strtotime($tanggalRekon)) }}</small>
    </h1>
</div>

<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fal fa-info-circle"></i>
            <strong>Laporan Detail vs Rekap</strong> 
            <br>Menampilkan perbandingan data antara detail transaksi dengan data rekap settlement.
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
                            <label for="filter_selisih" class="form-label">Filter Selisih</label>
                            <select class="form-control" id="filter_selisih" name="filter_selisih">
                                <option value="">Semua Data</option>
                                <option value="ada_selisih" @if(request()->getGet('filter_selisih') == 'ada_selisih') selected @endif>Ada Selisih</option>
                                <option value="tidak_ada_selisih" @if(request()->getGet('filter_selisih') == 'tidak_ada_selisih') selected @endif>Tidak Ada Selisih</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
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

<!-- Statistics Section -->
@if(!empty($compareData))
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="d-flex flex-column">
                            <span class="text-muted small">Total Data</span>
                            <h4 class="mb-0 text-primary" id="stat-total">{{ count($compareData) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex flex-column">
                            <span class="text-muted small">Ada Selisih</span>
                            <h4 class="mb-0 text-danger" id="stat-ada-selisih">
                                @php
                                    $adaSelisih = 0;
                                    foreach($compareData as $item) {
                                        if((float)str_replace(',', '', $item['SELISIH'] ?? 0) != 0) {
                                            $adaSelisih++;
                                        }
                                    }
                                    echo $adaSelisih;
                                @endphp
                            </h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex flex-column">
                            <span class="text-muted small">Tidak Ada Selisih</span>
                            <h4 class="mb-0 text-success" id="stat-tidak-ada-selisih">
                                @php
                                    $tidakAdaSelisih = 0;
                                    foreach($compareData as $item) {
                                        if((float)str_replace(',', '', $item['SELISIH'] ?? 0) == 0) {
                                            $tidakAdaSelisih++;
                                        }
                                    }
                                    echo $tidakAdaSelisih;
                                @endphp
                            </h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex flex-column">
                            <span class="text-muted small">Persentase Selesai</span>
                            <h4 class="mb-0 text-info" id="stat-akurasi">
                                @php
                                    $total = count($compareData);
                                    $akurasi = $total > 0 ? round(($tidakAdaSelisih / $total) * 100, 1) : 0;
                                    echo $akurasi . '%';
                                @endphp
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Data Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fal fa-table"></i> Data Perbandingan Detail vs Rekap
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="compareTable">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Group</th>
                                <th>File Settle</th>
                                <th>Amount Detail</th>
                                <th>Amount Rekap</th>
                                <th>Selisih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data akan dimuat via AJAX -->
                        </tbody>
                    </table>
                </div>
                @if(empty($compareData))
                    <div class="text-center py-4">
                        <i class="fal fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak ada data ditemukan</h5>
                        <p class="text-muted">Silakan pilih tanggal rekonsiliasi untuk menampilkan data.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ base_url('css/rekon/process/detail_vs_rekap.css') }}">
@endpush

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
let compareTable;

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
        const tanggal = $('#tanggal').val();
        const filterSelisih = $('#filter_selisih').val();
        
        console.log('Form submit - Tanggal:', tanggal);
        console.log('Form submit - Filter Selisih:', filterSelisih);
        
        if (tanggal && compareTable) {
            // Update current URL parameters
            const url = new URL(window.location);
            url.searchParams.set('tanggal', tanggal);
            if (filterSelisih !== '') {
                url.searchParams.set('filter_selisih', filterSelisih);
            } else {
                url.searchParams.delete('filter_selisih');
            }
            window.history.pushState({}, '', url);
            
            console.log('Updated URL:', url.toString());
            
            // Reload DataTable with new filters
            compareTable.ajax.reload();
        }
    });
    
    // Handle filter selisih change
    $('#filter_selisih').on('change', function() {
        const tanggal = $('#tanggal').val();
        const filterSelisih = $(this).val();
        
        console.log('Filter selisih changed - Tanggal:', tanggal);
        console.log('Filter selisih changed - Filter Selisih:', filterSelisih);
        
        if (tanggal && compareTable) {
            // Update current URL parameters
            const url = new URL(window.location);
            url.searchParams.set('tanggal', tanggal);
            if (filterSelisih !== '') {
                url.searchParams.set('filter_selisih', filterSelisih);
            } else {
                url.searchParams.delete('filter_selisih');
            }
            window.history.pushState({}, '', url);
            
            console.log('Updated URL from filter change:', url.toString());
            
            // Reload DataTable with new filters
            compareTable.ajax.reload();
        }
    });
});

function initializeDataTable() {
    compareTable = $('#compareTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ base_url('rekon/process/detail-vs-rekap/datatable') }}',
            type: 'GET',
            data: function(d) {
                // Add current filters
                d.tanggal = $('#tanggal').val() || '{{ $tanggalRekon }}';
                d.filter_selisih = $('#filter_selisih').val();
                console.log('DataTable request data:', d);
                console.log('Filter Selisih:', d.filter_selisih);
                return d;
            },
            error: function(xhr, error, thrown) {
                console.error('DataTable AJAX Error:', error, thrown, xhr.responseText);
                if (xhr.status === 403 || xhr.status === 419) {
                    console.log('CSRF error in DataTable, refreshing token...');
                    refreshCSRFToken().then(function() {
                        console.log('CSRF refreshed, reloading DataTable...');
                        compareTable.ajax.reload();
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
            { data: 'NAMA_GROUP', name: 'NAMA_GROUP' },
            { 
                data: 'FILE_SETTLE', 
                name: 'FILE_SETTLE',
                render: function(data, type, row) {
                    const fileSettle = parseInt(data || 0);
                    if (fileSettle === 0) {
                        return '<span class="badge badge-primary">Detail</span>';
                    } else {
                        return '<span class="badge badge-info">Rekap</span>';
                    }
                }
            },
            { 
                data: 'AMOUNT_DETAIL', 
                name: 'AMOUNT_DETAIL',
                className: 'text-end',
                render: function(data, type, row) {
                    const amount = parseFloat(String(data || 0).replace(/,/g, ''));
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
                }
            },
            { 
                data: 'AMOUNT_REKAP', 
                name: 'AMOUNT_REKAP',
                className: 'text-end',
                render: function(data, type, row) {
                    const amount = parseFloat(String(data || 0).replace(/,/g, ''));
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
                }
            },
            { 
                data: 'SELISIH', 
                name: 'SELISIH',
                className: 'text-end',
                render: function(data, type, row) {
                    const amount = parseFloat(String(data || 0).replace(/,/g, ''));
                    const isNonZero = amount !== 0;
                    const className = isNonZero ? 'text-danger fw-bold' : 'text-success';
                    return '<span class="' + className + '">Rp ' + new Intl.NumberFormat('id-ID').format(amount) + '</span>';
                }
            }
        ],
        pageLength: 25,
        lengthMenu: [[25, 50, 100, 200], [25, 50, 100, 200]],
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
        dom: '<"row"<"col-sm-12">>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-5"i><"col-sm-7"p>>'
    });
}

function formatNumber(num) {
    // Convert string to number first, removing any existing commas
    const cleanNum = parseFloat(String(num).replace(/,/g, '')) || 0;
    return new Intl.NumberFormat('id-ID').format(cleanNum);
}

function resetFilters() {
    $('#tanggal').val('{{ $tanggalRekon }}');
    $('#filter_selisih').val('');
    // Update URL params
    const url = new URL(window.location);
    url.searchParams.set('tanggal', '{{ $tanggalRekon }}');
    url.searchParams.delete('filter_selisih');
    window.history.pushState({}, '', url);
    // Reload DataTable if exists
    if (typeof compareTable !== 'undefined' && compareTable) {
        compareTable.ajax.reload();
    }
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
