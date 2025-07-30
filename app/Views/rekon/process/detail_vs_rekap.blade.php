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
                        <div class="col-md-4">
                            <label for="tanggal" class="form-label">Tanggal Rekonsiliasi</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                   value="{{ $tanggalRekon }}" required>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fal fa-search"></i> Tampilkan Data
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
                    <i class="fal fa-table"></i> Data Perbandingan Detail vs Rekap
                </h5>
            </div>
            <div class="card-body">
                @if(!empty($compareData))
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="compareTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>No</th>
                                    <th>Produk</th>
                                    <th>File Settle</th>
                                    <th>Jumlah Transaksi</th>
                                    <th>Total Amount</th>
                                    <th>Selisih</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($compareData as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item['produk'] ?? '' }}</td>
                                    <td>
                                        <span class="badge {{ ($item['file_settle'] ?? 0) == 0 ? 'badge-primary' : 'badge-info' }}">
                                            {{ ($item['file_settle'] ?? 0) == 0 ? 'Detail' : 'Rekap' }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($item['jumlah_transaksi'] ?? 0) }}</td>
                                    <td>{{ number_format($item['total_amount'] ?? 0) }}</td>
                                    <td class="{{ ($item['selisih'] ?? 0) != 0 ? 'text-danger fw-bold' : 'text-success' }}">
                                        {{ number_format($item['selisih'] ?? 0) }}
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

@push('js')
<script>
$(document).ready(function() {
    @if(!empty($compareData))
        $('#compareTable').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
            },
            order: [[1, 'asc']],
            columnDefs: [
                { targets: [0], orderable: false }
            ]
        });
    @endif
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
</style>
@endpush
