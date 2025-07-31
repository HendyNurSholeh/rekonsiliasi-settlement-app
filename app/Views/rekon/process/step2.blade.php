@extends('layouts.app')

@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        <i class="fal fa-check-square"></i> {{ $title }}
        <small>Verifikasi isi data untuk tanggal {{ date('d/m/Y', strtotime($tanggalRekon)) }}</small>
    </h1>
</div>

<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fal fa-info-circle"></i>
            <strong>Tahap 2 - Verifikasi Isi Data</strong> 
            <br>Pastikan semua produk telah termapping dengan benar sebelum memulai proses rekonsiliasi.
        </div>
    </div>
</div>


<!-- Data Summary Cards -->
<div class="row mb-4">
    <!-- Agregator Detail Summary -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-success">
            <div class="card-header bg-success-200">
                <h5 class="card-title text-white">
                    <i class="fal fa-database"></i> Data Agregator Detail
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <h3 class="text-primary">{{ number_format($dataStats['agn_detail']['total_records'] ?? 0) }}</h3>
                            <small class="text-muted">Total Records</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h3 class="text-success">{{ number_format($dataStats['agn_detail']['total_amount'] ?? 0) }}</h3>
                            <small class="text-muted">Total Transaksi</small>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="small text-muted">
                    <i class="fal fa-calendar"></i> {{ date('d/m/Y', strtotime($tanggalRekon)) }}
                    <div class="float-right">
                        <i class="fal fa-check-circle text-success"></i> Valid
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settlement Education Summary -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-info">
            <div class="card-header bg-info-200">
                <h5 class="card-title text-white">
                    <i class="fal fa-graduation-cap"></i> Settlement Education
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <h3 class="text-primary">{{ number_format($dataStats['settle_edu']['total_records'] ?? 0) }}</h3>
                            <small class="text-muted">Total Records</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h3 class="text-success">{{ number_format($dataStats['settle_edu']['total_amount'] ?? 0) }}</h3>
                            <small class="text-muted">Total Nominal</small>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="small text-muted">
                    <i class="fal fa-calendar"></i> {{ date('d/m/Y', strtotime($tanggalRekon)) }}
                    <div class="float-right">
                        <i class="fal fa-check-circle text-success"></i> Valid
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settlement Pajak Summary -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-warning">
            <div class="card-header bg-warning-200">
                <h5 class="card-title text-white">
                    <i class="fal fa-receipt"></i> Settlement Pajak
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <h3 class="text-primary">{{ number_format($dataStats['settle_pajak']['total_records'] ?? 0) }}</h3>
                            <small class="text-muted">Total Records</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h3 class="text-success">{{ number_format($dataStats['settle_pajak']['total_amount'] ?? 0) }}</h3>
                            <small class="text-muted">Total Nominal</small>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="small text-muted">
                    <i class="fal fa-calendar"></i> {{ date('d/m/Y', strtotime($tanggalRekon)) }}
                    <div class="float-right">
                        <i class="fal fa-check-circle text-success"></i> Valid
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- M-Gate Summary -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-primary">
            <div class="card-header bg-primary-200">
                <h5 class="card-title text-white">
                    <i class="fal fa-credit-card"></i> Data M-Gate
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <h3 class="text-primary">{{ number_format($dataStats['mgate']['total_records'] ?? 0) }}</h3>
                            <small class="text-muted">Total Records</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h3 class="text-success">{{ number_format($dataStats['mgate']['total_amount'] ?? 0) }}</h3>
                            <small class="text-muted">Total Nilai</small>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="small text-muted">
                    <i class="fal fa-calendar"></i> {{ date('d/m/Y', strtotime($tanggalRekon)) }}
                    <div class="float-right">
                        <i class="fal fa-check-circle text-success"></i> Valid
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Validation Results -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fal fa-list-check text-success"></i> Hasil Validasi Data
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Validasi</th>
                                <th>Status</th>
                                <th>Hasil</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <i class="fal fa-database text-primary"></i> 
                                    Kelengkapan Data Agregator
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <i class="fal fa-check"></i> Valid
                                    </span>
                                </td>
                                <td>{{ number_format($dataStats['agn_detail']['total_records'] ?? 0) }} records</td>
                                <td>Data transaksi tersedia lengkap</td>
                            </tr>
                            <tr>
                                <td>
                                    <i class="fal fa-graduation-cap text-info"></i> 
                                    Data Settlement Education
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <i class="fal fa-check"></i> Valid
                                    </span>
                                </td>
                                <td>{{ number_format($dataStats['settle_edu']['total_records'] ?? 0) }} records</td>
                                <td>Data settlement education tersedia</td>
                            </tr>
                            <tr>
                                <td>
                                    <i class="fal fa-receipt text-warning"></i> 
                                    Data Settlement Pajak
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <i class="fal fa-check"></i> Valid
                                    </span>
                                </td>
                                <td>{{ number_format($dataStats['settle_pajak']['total_records'] ?? 0) }} records</td>
                                <td>Data settlement pajak tersedia</td>
                            </tr>
                            <tr>
                                <td>
                                    <i class="fal fa-calendar-check text-success"></i> 
                                    Konsistensi Tanggal
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <i class="fal fa-check"></i> Valid
                                    </span>
                                </td>
                                <td>{{ date('d/m/Y', strtotime($tanggalRekon)) }}</td>
                                <td>Semua data menggunakan tanggal yang sama</td>
                            </tr>
                            <tr>
                                <td>
                                    <i class="fal fa-credit-card text-primary"></i> 
                                    Data M-Gate (Payment Gateway)
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <i class="fal fa-check"></i> Valid
                                    </span>
                                </td>
                                <td>{{ number_format($dataStats['mgate']['total_records'] ?? 0) }} records</td>
                                <td>Data transaksi payment gateway tersedia</td>
                            </tr>
                            <tr>
                                <td>
                                    <i class="fal fa-sitemap text-info"></i> 
                                    Mapping Produk
                                </td>
                                <td>
                                    @if(($mappingStats['unmapped_products'] ?? 0) > 0)
                                        <span class="badge badge-warning">
                                            <i class="fal fa-exclamation-triangle"></i> Belum Lengkap
                                        </span>
                                    @else
                                        <span class="badge badge-success">
                                            <i class="fal fa-check"></i> Lengkap
                                        </span>
                                    @endif
                                </td>
                                <td>{{ ($mappingStats['mapped_products'] ?? 0) }}/{{ ($mappingStats['total_products'] ?? 0) }} produk mapped</td>
                                <td>
                                    @if(($mappingStats['unmapped_products'] ?? 0) > 0)
                                        Masih ada {{ $mappingStats['unmapped_products'] ?? 0 }} produk yang belum mapping
                                    @else
                                        Semua produk telah termapping dengan benar
                                    @endif
                                </td>
                            </tr>
                            <tr class="{{ ($mappingStats['unmapped_products'] ?? 0) > 0 ? 'table-warning' : 'table-success' }}">
                                <td>
                                    <strong>
                                        @if(($mappingStats['unmapped_products'] ?? 0) > 0)
                                            <i class="fal fa-exclamation-triangle text-warning"></i> 
                                            Status Keseluruhan
                                        @else
                                            <i class="fal fa-check-circle text-success"></i> 
                                            Status Keseluruhan
                                        @endif
                                    </strong>
                                </td>
                                <td>
                                    @if(($mappingStats['unmapped_products'] ?? 0) > 0)
                                        <span class="badge badge-warning">
                                            <i class="fal fa-exclamation-triangle"></i> PERLU MAPPING
                                        </span>
                                    @else
                                        <span class="badge badge-success">
                                            <i class="fal fa-thumbs-up"></i> SIAP PROSES
                                        </span>
                                    @endif
                                </td>
                                <td colspan="2">
                                    @if(($mappingStats['unmapped_products'] ?? 0) > 0)
                                        <strong>Ada {{ $mappingStats['unmapped_products'] ?? 0 }} produk yang belum mapping. Silakan lengkapi mapping produk terlebih dahulu.</strong>
                                    @else
                                        <strong>Semua validasi berhasil. Data siap untuk diproses rekonsiliasi.</strong>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Product Mapping Data Table -->
<div class="row">
    <div class="col-12">
        <!-- Product Mapping Statistics -->
        <div class="row mb-3">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-primary h-100">
                    <div class="card-header bg-primary text-white py-2">
                        <h6 class="card-title mb-0">
                            <i class="fal fa-cube"></i> Total Produk
                        </h6>
                    </div>
                    <div class="card-body text-center py-3">
                        <h3 class="text-primary mb-1" id="totalProducts">{{ $mappingStats['total_products'] ?? 0 }}</h3>
                        <small class="text-muted">Produk Ditemukan</small>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-success h-100">
                    <div class="card-header bg-success text-white py-2">
                        <h6 class="card-title mb-0">
                            <i class="fal fa-check-circle"></i> Sudah Mapping
                        </h6>
                    </div>
                    <div class="card-body text-center py-3">
                        <h3 class="text-success mb-1" id="mappedProducts">{{ $mappingStats['mapped_products'] ?? 0 }}</h3>
                        <small class="text-muted">Produk Termapping</small>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-danger h-100">
                    <div class="card-header bg-danger text-white py-2">
                        <h6 class="card-title mb-0">
                            <i class="fal fa-exclamation-circle"></i> Belum Mapping
                        </h6>
                    </div>
                    <div class="card-body text-center py-3">
                        <h3 class="text-danger mb-1" id="unmappedProducts">{{ $mappingStats['unmapped_products'] ?? 0 }}</h3>
                        <small class="text-muted">Produk Belum Mapping</small>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-info h-100">
                    <div class="card-header bg-info text-white py-2">
                        <h6 class="card-title mb-0">
                            <i class="fal fa-percentage"></i> Persentase
                        </h6>
                    </div>
                    <div class="card-body text-center py-3">
                        <h3 class="text-info mb-1" id="mappingPercentage">{{ number_format($mappingStats['mapping_percentage'] ?? 0, 1) }}%</h3>
                        <small class="text-muted">Mapping Selesai</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Mapping Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fal fa-table text-primary"></i> Data Product Mapping (v_cek_group_produk)
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-striped table-hover table-sm" id="mappingTable">
                        <thead class="thead-light sticky-top">
                            <tr>
                                <th style="width: 60px;">No</th>
                                <th style="width: 100px;">Source</th>
                                <th>Produk</th>
                                <th>Nama Group</th>
                                <th style="width: 120px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!empty($mappingData) && count($mappingData) > 0)
                                @foreach($mappingData as $index => $item)
                                <tr class="{{ empty($item['NAMA_GROUP']) ? 'table-warning' : '' }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <span class="badge {{ $item['SOURCE'] === 'DETAIL' ? 'badge-primary' : 'badge-info' }}">
                                            {{ $item['SOURCE'] ?? '' }}
                                        </span>
                                    </td>
                                    <td><code class="font-weight-bold">{{ $item['PRODUK'] ?? '' }}</code></td>
                                    <td>
                                        @if(!empty($item['NAMA_GROUP']))
                                            <span class="badge badge-success">{{ $item['NAMA_GROUP'] }}</span>
                                        @else
                                            <span class="badge badge-danger">Belum Mapping</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!empty($item['NAMA_GROUP']))
                                            <i class="fal fa-check-circle text-success"></i> Mapped
                                        @else
                                            <i class="fal fa-exclamation-triangle text-warning"></i> Not Mapped
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        <i class="fal fa-inbox"></i> Tidak ada data ditemukan
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-2">Proses Selanjutnya</h6>
                        @if(($mappingStats['unmapped_products'] ?? 0) > 0)
                            <p class="text-danger mb-0">
                                <i class="fal fa-exclamation-triangle"></i>
                                Masih ada <strong>{{ $mappingStats['unmapped_products'] ?? 0 }} produk</strong> yang belum termapping. 
                                Pastikan semua produk telah termapping terlebih dahulu.
                            </p>
                        @else
                            <p class="text-success mb-0">
                                <i class="fal fa-check-circle"></i>
                                Semua produk telah termapping dengan benar. Siap untuk memulai rekonsiliasi.
                            </p>
                        @endif
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary me-2" onclick="window.location.href='{{ base_url('rekon') }}'">
                            <i class="fal fa-arrow-left"></i> Kembali ke Step 1
                        </button>
                        @if(($mappingStats['unmapped_products'] ?? 0) > 0)
                            <button type="button" class="btn btn-primary" disabled id="btnMulaiRekonsiliasi" title="Masih ada produk yang belum mapping">
                                <i class="fal fa-exclamation-triangle"></i> Produk Belum Mapping Semua
                            </button>
                        @else
                            <button type="button" class="btn btn-primary" id="btnMulaiRekonsiliasi" onclick="startReconciliation()">
                                <i class="fal fa-play"></i> Mulai Rekonsiliasi
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script>
let currentTanggalRekon = '{{ $tanggalRekon }}';

function updateMappingStats(stats) {
    $('#totalProducts').text(stats.total_products || 0);
    $('#mappedProducts').text(stats.mapped_products || 0);
    $('#unmappedProducts').text(stats.unmapped_products || 0);
    $('#mappingPercentage').text((stats.mapping_percentage || 0).toFixed(1) + '%');
}

function startReconciliation() {
    let btn = $('#btnMulaiRekonsiliasi');
    let originalText = btn.html();
    
    btn.prop('disabled', true).html('<i class="fal fa-spinner fa-spin"></i> Memproses...');
    
    $.ajax({
        url: '{{ base_url('rekon/step2/validate') }}',
        type: 'POST',
        data: {
            tanggal_rekon: currentTanggalRekon,
            '{{ csrf_token() }}': '{{ csrf_hash() }}'
        },
        dataType: 'json',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success) {
                toastr['success'](response.message);
                if (response.redirect) {
                    setTimeout(function() {
                        window.location.href = response.redirect;
                    }, 1500);
                }
            } else {
                if (response.unmapped_products && response.unmapped_products.length > 0) {
                    let productList = response.unmapped_products.map(p => `${p.SOURCE}: ${p.PRODUK}`).join('<br>');
                    toastr['warning'](response.message + '<br><br><strong>Produk yang belum mapping:</strong><br>' + productList);
                } else {
                    toastr['error'](response.message);
                }
                btn.prop('disabled', false).html(originalText);
            }
        },
        error: function() {
            toastr['error']('Error saat memulai rekonsiliasi');
            btn.prop('disabled', false).html(originalText);
        }
    });
}
</script>
@endpush

@push('styles')
<style>
.table-warning {
    background-color: rgba(255, 193, 7, 0.1);
}

.badge {
    font-size: 0.75em;
}

.text-muted {
    color: #6c757d !important;
}

#mappingTable th {
    border-top: none;
    font-weight: 600;
    background-color: #f8f9fa;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-header {
    padding: 0.75rem 1.25rem;
    margin-bottom: 0;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

/* Source badge styling */
.badge-primary {
    background-color: #007bff;
}

.badge-info {
    background-color: #17a2b8;
}

/* Table row styling for unmapped items */
.table-warning td {
    background-color: rgba(255, 193, 7, 0.15) !important;
}

/* Fixed table styling */
.table-responsive {
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
}

.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
}

#mappingTable thead th {
    background-color: #f8f9fa !important;
    border-bottom: 2px solid #dee2e6;
}

/* Compact table for better space usage */
.table-sm th, .table-sm td {
    padding: 0.5rem;
}
</style>
@endpush
