@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ base_url('css/rekon/process/indirect_jurnal_rekap.css') }}">
@endpush

@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        <i class="fal fa-chart-bar"></i> {{ $title }}
        <small>Rekap transaksi indirect jurnal untuk tanggal {{ date('d/m/Y', strtotime($tanggalRekon)) }}</small>
    </h1>
</div>

<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fal fa-info-circle"></i>
            <strong>Rekap Tx Indirect Jurnal</strong> 
            <br>Menampilkan rekap transaksi indirect jurnal dengan analisis selisih antara data sukses dan core.
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
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">
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
                    <i class="fal fa-table"></i> Data Rekap Indirect Jurnal
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="rekapTable">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Tanggal Rekon</th>
                                <th>Nama Group</th>
                                <th>Sukses (N)</th>
                                <th>Sukses (Amount)</th>
                                <th>Core Sukses (N)</th>
                                <th>Core Sukses (Amount)</th>
                                <th>Selisih (N)</th>
                                <th>Selisih (Amount)</th>
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

<!-- Modal Konfirmasi -->
<div class="modal fade" id="konfirmasiModal" tabindex="-1" role="dialog" aria-labelledby="konfirmasiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="konfirmasiModalLabel">
                    <i class="fal fa-shield-check text-primary"></i>
                    Konfirmasi Saldo Rekening Escrow
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fal fa-info-circle"></i>
                    <strong>Konfirmasi Saldo Escrow!</strong> Pastikan saldo rekening escrow sudah sesuai sebelum melakukan konfirmasi.
                </div>
                <i>
                    <p id="konfirmasiMessage" class="mb-3"></p>
                </i>
                <p class="text-muted small">
                    <i class="fal fa-lightbulb"></i>
                    Pastikan saldo fisik di rekening escrow sudah sesuai dengan nominal yang ditampilkan.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fal fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-primary" id="confirmUpdateBtn">
                    <i class="fal fa-check"></i> Ya, Saldo Sudah Sesuai
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Enhanced CSRF Management with auto-refresh
let currentCSRF = '{{ csrf_token() }}';

// Function untuk refresh CSRF token
function refreshCSRFToken() {
    console.log('Requesting fresh CSRF token...');
    return $.get('{{ base_url('get-csrf-token') }}').then(function(response) {
        console.log('CSRF token response:', response);
        if (response.csrf_token) {
            const oldToken = currentCSRF;
            currentCSRF = response.csrf_token;
            console.log('CSRF token refreshed successfully');
            console.log('Old token:', oldToken);
            console.log('New token:', currentCSRF);
            return currentCSRF;
        }
        throw new Error('No CSRF token in response');
    }).catch(function(error) {
        console.error('Failed to refresh CSRF:', error);
        // Fallback: reload page if can't refresh token
        setTimeout(function() {
            if (confirm('Session expired. Reload page?')) {
                location.reload();
            }
        }, 1000);
        throw error;
    });
}

// Global AJAX setup untuk auto-inject CSRF
$.ajaxSetup({
    beforeSend: function(xhr, settings) {
        // Untuk semua POST request, refresh CSRF token dulu
        if (settings.type === 'POST') {
            console.log('POST request detected, injecting CSRF token:', currentCSRF);
            console.log('Request URL:', settings.url);
            console.log('Request data before CSRF injection:', settings.data);
            
            // Jika data adalah FormData
            if (settings.data instanceof FormData) {
                settings.data.append('csrf_test_name', currentCSRF);
            } 
            // Jika data adalah string biasa
            else {
                const separator = settings.data ? '&' : '';
                settings.data = (settings.data || '') + separator + 'csrf_test_name=' + encodeURIComponent(currentCSRF);
            }
            
            console.log('Request data after CSRF injection:', settings.data);
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

// DataTable instance
let rekapTable;

$(document).ready(function() {
    // Refresh CSRF token saat page load untuk memastikan token fresh
    console.log('Initial CSRF token:', currentCSRF);
    
    refreshCSRFToken().then(function() {
        console.log('CSRF token refreshed on page load, new token:', currentCSRF);
        
        // Initialize DataTable dengan AJAX
        initializeDataTable();
    }).catch(function(error) {
        console.error('Failed to refresh CSRF on page load:', error);
        // Fallback: tetap initialize DataTable dengan token yang ada
        initializeDataTable();
    });
    
    // Auto-refresh CSRF token setiap 5 menit untuk mencegah expiry
    setInterval(function() {
        refreshCSRFToken().then(function() {
            console.log('CSRF token auto-refreshed:', currentCSRF);
        }).catch(function(error) {
            console.error('Auto-refresh CSRF failed:', error);
        });
    }, 5 * 60 * 1000); // 5 menit
    
    // Handle form submit for date filter
    $('form').on('submit', function(e) {
        e.preventDefault();
        const tanggal = $('#tanggal').val();
        
        console.log('Form submit - Tanggal:', tanggal);
        
        if (tanggal && rekapTable) {
            // Update current URL parameters
            const url = new URL(window.location);
            url.searchParams.set('tanggal', tanggal);
            window.history.pushState({}, '', url);
            
            console.log('Updated URL:', url.toString());
            
            // Reload DataTable with new filters
            rekapTable.ajax.reload();
        }
    });
});

function initializeDataTable() {
    rekapTable = $('#rekapTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ base_url('rekon/process/indirect-jurnal-rekap/datatable') }}',
            type: 'GET',
            data: function(d) {
                // Add current date filter
                d.tanggal = $('#tanggal').val() || '{{ $tanggalRekon }}';
                console.log('DataTable request data:', d);
                return d;
            },
            error: function(xhr, error, thrown) {
                console.error('DataTable AJAX Error:', error, thrown, xhr.responseText);
                if (xhr.status === 403 || xhr.status === 419) {
                    console.log('CSRF error in DataTable, refreshing token...');
                    refreshCSRFToken().then(function() {
                        console.log('CSRF refreshed, reloading DataTable...');
                        rekapTable.ajax.reload();
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
            { data: 'NAMA_GROUP', name: 'NAMA_GROUP' },
            { 
                data: 'N_SUKSES', 
                name: 'N_SUKSES',
                className: 'text-center',
                render: function(data, type, row) {
                    return new Intl.NumberFormat('id-ID').format(data || 0);
                }
            },
            { 
                data: 'A_SUKSES', 
                name: 'A_SUKSES',
                className: 'text-end',
                render: function(data, type, row) {
                    const amount = parseFloat(String(data || 0).replace(/,/g, ''));
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
                }
            },
            { 
                data: 'N_CORE_SUKSES', 
                name: 'N_CORE_SUKSES',
                className: 'text-center',
                render: function(data, type, row) {
                    return new Intl.NumberFormat('id-ID').format(data || 0);
                }
            },
            { 
                data: 'A_CORE_SUKSES', 
                name: 'A_CORE_SUKSES',
                className: 'text-end',
                render: function(data, type, row) {
                    const amount = parseFloat(String(data || 0).replace(/,/g, ''));
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
                }
            },
            { 
                data: 'N_SELISIH', 
                name: 'N_SELISIH',
                className: 'text-center',
                render: function(data, type, row) {
                    const value = parseInt(data || 0);
                    const isNonZero = value !== 0;
                    const className = isNonZero ? 'text-danger fw-bold' : 'text-success';
                    return '<span class="' + className + '">' + new Intl.NumberFormat('id-ID').format(value) + '</span>';
                }
            },
            { 
                data: 'A_SELISIH', 
                name: 'A_SELISIH',
                className: 'text-end',
                render: function(data, type, row) {
                    const amount = parseFloat(String(data || 0).replace(/,/g, ''));
                    const isNonZero = amount !== 0;
                    const className = isNonZero ? 'text-danger fw-bold' : 'text-success';
                    return '<span class="' + className + '">Rp ' + new Intl.NumberFormat('id-ID').format(amount) + '</span>';
                }
            },
            { 
                data: null,
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    if (row.NAMA_GROUP) {
                        return `
                            <button type="button" class="btn btn-sm btn-primary btn-update-sukses" 
                                    data-group="${row.NAMA_GROUP}" 
                                    data-count="${row.N_SUKSES || 0}" 
                                    data-amount="${row.A_SUKSES || 0}"
                                    title="Konfirmasi Saldo Escrow">
                                <i class="fal fa-check"></i> Konfirmasi Saldo
                            </button>
                        `;
                    }
                    return '<span class="text-muted">-</span>';
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
    
    // Event handler untuk tombol update sukses
    $('#rekapTable').on('click', '.btn-update-sukses', function() {
        const group = $(this).data('group');
        const count = $(this).data('count');
        const amount = $(this).data('amount');
        
        // Format message untuk modal
        const formattedCount = new Intl.NumberFormat('id-ID').format(count);
        const formattedAmount = new Intl.NumberFormat('id-ID').format(amount);
        
        const message = `Data menunjukkan ${group} memiliki ${formattedCount} transaksi sukses dengan total nominal Rp. ${formattedAmount}. Silakan cek dan pastikan saldo fisik di Rekening Escrow ${group} sudah tersedia sebesar Rp. ${formattedAmount} sebelum melakukan konfirmasi!`;
        
        $('#konfirmasiMessage').text(message);
        $('#confirmUpdateBtn').data('group', group);
        $('#konfirmasiModal').modal('show');
    });
    
    // Event handler untuk konfirmasi update
    $('#confirmUpdateBtn').on('click', function() {
        const group = $(this).data('group');
        updateSuksesTx(group);
    });
}

function updateSuksesTx(group) {
    // Show loading state
    $('#confirmUpdateBtn').html('<i class="fal fa-spinner fa-spin"></i> Memproses Konfirmasi...').prop('disabled', true);
    
    // Refresh CSRF token dulu sebelum request
    refreshCSRFToken().then(function(newToken) {
        console.log('Using fresh CSRF token for update:', newToken);
        
        // Validasi token format (basic check)
        if (!newToken || newToken.length < 10) {
            throw new Error('Invalid CSRF token format');
        }
        
        console.log('Sending request with data:', {
            group: group,
            csrf_test_name: currentCSRF
        });
        
        $.ajax({
            url: '{{ base_url('rekon/process/indirect-jurnal-rekap/update-sukses') }}',
            type: 'POST',
            data: {
                group: group,
                csrf_test_name: currentCSRF
            },
            success: function(response) {
                console.log('Success response:', response);
                $('#konfirmasiModal').modal('hide');
                
                if (response.success) {
                    showAlert('success', response.message || 'Konfirmasi saldo berhasil dilakukan dan status transaksi telah diupdate');
                    
                    // Update CSRF token dari response jika ada
                    if (response.csrf_token) {
                        currentCSRF = response.csrf_token;
                        console.log('CSRF token updated from response:', currentCSRF);
                    }
                    
                    // Reload DataTable
                    if (rekapTable) {
                        rekapTable.ajax.reload();
                    }
                } else {
                    showAlert('error', response.message || 'Terjadi kesalahan saat melakukan konfirmasi saldo');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error
                });
                
                $('#konfirmasiModal').modal('hide');
                
                let errorMessage = 'Terjadi kesalahan saat melakukan konfirmasi saldo';
                if (xhr.status === 403) {
                    errorMessage = 'Token keamanan tidak valid. Halaman akan dimuat ulang untuk memperbarui token.';
                    // Auto reload jika CSRF error
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                showAlert('error', errorMessage);
            },
            complete: function() {
                // Reset button state
                $('#confirmUpdateBtn').html('<i class="fal fa-check-circle"></i> Ya, Saldo Sudah Sesuai').prop('disabled', false);
            }
        });
    }).catch(function(error) {
        console.error('Failed to refresh CSRF before update:', error);
        $('#konfirmasiModal').modal('hide');
        $('#confirmUpdateBtn').html('<i class="fal fa-check-circle"></i> Ya, Saldo Sudah Sesuai').prop('disabled', false);
        showAlert('error', 'Gagal memperbarui token keamanan. Halaman akan dimuat ulang.');
        setTimeout(function() {
            location.reload();
        }, 2000);
    });
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

function resetFilters() {
    // Remove 'tanggal' parameter from URL and redirect
    const url = new URL(window.location);
    url.searchParams.delete('tanggal');
    window.location.href = url.pathname + url.search;
}
</script>
@endpush

