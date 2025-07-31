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
                                <option value="ada_selisih" {{ (isset($filterSelisih) && $filterSelisih == 'ada_selisih') ? 'selected' : '' }}>Ada Selisih</option>
                                <option value="tidak_ada_selisih" {{ (isset($filterSelisih) && $filterSelisih == 'tidak_ada_selisih') ? 'selected' : '' }}>Tidak Ada Selisih</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fal fa-search"></i> Tampilkan Data
                            </button>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <a href="{{ current_url() }}?tanggal={{ $tanggalRekon }}" class="btn btn-secondary">
                                <i class="fal fa-refresh"></i> Reset Filter
                            </a>
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
                            <span class="text-muted small">Akurasi</span>
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
                    <span id="filter-indicator" class="badge badge-info ml-2" style="display: none;"></span>
                </h5>
            </div>
            <div class="card-body">
                @if(!empty($compareData))
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
                                @foreach($compareData as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item['NAMA_GROUP'] ?? '' }}</td>
                                    <td>
                                        <span class="badge {{ ($item['FILE_SETTLE'] ?? 0) == 0 ? 'badge-primary' : 'badge-info' }}">
                                            {{ ($item['FILE_SETTLE'] ?? 0) == 0 ? 'Detail' : 'Rekap' }}
                                        </span>
                                    </td>
                                    <td class="text-end">Rp {{ number_format((float)str_replace(',', '', $item['AMOUNT_DETAIL'] ?? 0), 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format((float)str_replace(',', '', $item['AMOUNT_REKAP'] ?? 0), 0, ',', '.') }}</td>
                                    <td class="text-end {{ ((float)str_replace(',', '', $item['SELISIH'] ?? 0)) != 0 ? 'text-danger fw-bold' : 'text-success' }}">
                                        Rp {{ number_format((float)str_replace(',', '', $item['SELISIH'] ?? 0), 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
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

@push('scripts')
<script>
$(document).ready(function() {
    // Client-side filtering function
    function applyFilter() {
        var filterValue = $('#filter_selisih').val();
        var $tableRows = $('#compareTable tbody tr');
        var visibleCount = 0;
        
        $tableRows.each(function() {
            var $row = $(this);
            var selisihCell = $row.find('td:last');
            var selisihText = selisihCell.text().replace('Rp ', '').replace(/\./g, '').replace(/,/g, '').trim();
            var selisihValue = parseInt(selisihText) || 0;
            var shouldShow = false;
            
            if (filterValue === '') {
                // Show all rows
                shouldShow = true;
            } else if (filterValue === 'ada_selisih') {
                // Show only rows with selisih != 0
                shouldShow = (selisihValue !== 0);
            } else if (filterValue === 'tidak_ada_selisih') {
                // Show only rows with selisih = 0
                shouldShow = (selisihValue === 0);
            }
            
            if (shouldShow) {
                $row.show();
                visibleCount++;
            } else {
                $row.hide();
            }
        });
        
        // Update row numbers for visible rows
        var rowNumber = 1;
        $tableRows.filter(':visible').each(function() {
            $(this).find('td:first').text(rowNumber++);
        });
        
        // Update filter indicator (statistics tetap menampilkan data keseluruhan)
        updateFilterIndicator(filterValue, visibleCount);
        
        // Show/hide empty message
        if (visibleCount === 0) {
            if ($('#no-data-message').length === 0) {
                $('#compareTable').after(`
                    <div id="no-data-message" class="text-center py-4">
                        <i class="fal fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak ada data yang sesuai dengan filter</h5>
                        <p class="text-muted">Silakan ubah filter untuk menampilkan data.</p>
                    </div>
                `);
            }
            $('#compareTable').hide();
            $('#no-data-message').show();
        } else {
            $('#compareTable').show();
            $('#no-data-message').hide();
        }
    }
    
    function updateFilterIndicator(filterValue, visibleCount) {
        var $indicator = $('#filter-indicator');
        
        if (filterValue === '') {
            $indicator.hide();
        } else {
            var filterText = '';
            var badgeClass = 'badge-info';
            
            if (filterValue === 'ada_selisih') {
                filterText = 'Filter: Ada Selisih';
                badgeClass = 'badge-danger';
            } else if (filterValue === 'tidak_ada_selisih') {
                filterText = 'Filter: Tidak Ada Selisih';
                badgeClass = 'badge-success';
            }
            
            $indicator
                .removeClass('badge-info badge-danger badge-success')
                .addClass(badgeClass)
                .text(filterText + ' (' + visibleCount + ' data)')
                .show();
        }
    }
    
    // Event handler for filter change
    $('#filter_selisih').on('change', function() {
        applyFilter();
    });
    
    // Initialize filter on page load
    @if(!empty($compareData))
        // Apply initial filter if set from URL
        var urlParams = new URLSearchParams(window.location.search);
        var filterParam = urlParams.get('filter_selisih');
        if (filterParam) {
            $('#filter_selisih').val(filterParam);
        }
        
        // Apply filter on page load
        applyFilter();
    @endif
    
    // Remove auto-submit to prevent conflict with client-side filtering
    // $('#filter_selisih').on('change', function() {
    //     $(this).closest('form').submit();
    // });
});
</script>
@endpush

@push('styles')
<style>
.text-danger.fw-bold {
    color: #dc3545 !important;
    font-weight: 700 !important;
}

.badge {
    font-size: 0.75em;
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

.badge-primary {
    background-color: #007bff;
}

.badge-info {
    background-color: #17a2b8;
}
</style>
@endpush
