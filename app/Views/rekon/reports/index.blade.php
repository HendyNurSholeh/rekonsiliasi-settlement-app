@extends('layouts.app')

@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        <i class="fal fa-file-chart-line"></i> {{ $title }}
        <small>Laporan hasil rekonsiliasi untuk tanggal {{ date('d/m/Y', strtotime($tanggalRekon)) }}</small>
    </h1>
    <div class="subheader-right">
        <div class="btn-group">
            <button class="btn btn-success" onclick="downloadReport('excel')">
                <i class="fal fa-file-excel"></i> Download Excel
            </button>
            <button class="btn btn-danger" onclick="downloadReport('pdf')">
                <i class="fal fa-file-pdf"></i> Download PDF
            </button>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card border-left-primary">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Transaksi
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ number_format($reportData['summary']['total_transactions']) }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fal fa-list-ol fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-left-success">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Amount
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            Rp {{ number_format($reportData['summary']['total_amount'], 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fal fa-money-bill-wave fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-left-info">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Match Rate
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $reportData['summary']['match_rate'] }}%
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-info" style="width: {{ $reportData['summary']['match_rate'] }}%"></div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fal fa-percentage fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-left-warning">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Selisih Items
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ number_format($reportData['summary']['discrepancies']) }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fal fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- File Processing Summary -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fal fa-files text-primary"></i> Ringkasan Proses File
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Jenis File</th>
                                <th>Total Records</th>
                                <th>Total Amount</th>
                                <th>Matched</th>
                                <th>Unmatched</th>
                                <th>Match Rate</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <i class="fal fa-database text-primary"></i>
                                    <strong>Agregator Detail</strong>
                                </td>
                                <td>{{ number_format($reportData['file_summary']['agn_detail']['records']) }}</td>
                                <td>Rp {{ number_format($reportData['file_summary']['agn_detail']['amount'], 0, ',', '.') }}</td>
                                <td class="text-success">{{ number_format($reportData['file_summary']['agn_detail']['matched']) }}</td>
                                <td class="text-warning">{{ number_format($reportData['file_summary']['agn_detail']['unmatched']) }}</td>
                                <td>
                                    @php $rate = ($reportData['file_summary']['agn_detail']['matched'] / $reportData['file_summary']['agn_detail']['records']) * 100 @endphp
                                    <span class="badge badge-{{ $rate >= 99 ? 'success' : 'warning' }}">{{ number_format($rate, 2) }}%</span>
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <i class="fal fa-check"></i> Processed
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <i class="fal fa-graduation-cap text-info"></i>
                                    <strong>Settlement Education</strong>
                                </td>
                                <td>{{ number_format($reportData['file_summary']['settlement_edu']['records']) }}</td>
                                <td>Rp {{ number_format($reportData['file_summary']['settlement_edu']['amount'], 0, ',', '.') }}</td>
                                <td class="text-success">{{ number_format($reportData['file_summary']['settlement_edu']['matched']) }}</td>
                                <td class="text-warning">{{ number_format($reportData['file_summary']['settlement_edu']['unmatched']) }}</td>
                                <td>
                                    <span class="badge badge-success">100.00%</span>
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <i class="fal fa-check"></i> Processed
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <i class="fal fa-receipt text-warning"></i>
                                    <strong>Settlement Pajak</strong>
                                </td>
                                <td>{{ number_format($reportData['file_summary']['settlement_pajak']['records']) }}</td>
                                <td>Rp {{ number_format($reportData['file_summary']['settlement_pajak']['amount'], 0, ',', '.') }}</td>
                                <td class="text-success">{{ number_format($reportData['file_summary']['settlement_pajak']['matched']) }}</td>
                                <td class="text-warning">{{ number_format($reportData['file_summary']['settlement_pajak']['unmatched']) }}</td>
                                <td>
                                    @php $rate = ($reportData['file_summary']['settlement_pajak']['matched'] / $reportData['file_summary']['settlement_pajak']['records']) * 100 @endphp
                                    <span class="badge badge-{{ $rate >= 99 ? 'success' : 'warning' }}">{{ number_format($rate, 2) }}%</span>
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <i class="fal fa-check"></i> Processed
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <i class="fal fa-credit-card text-primary"></i>
                                    <strong>M-Gate (Payment Gateway)</strong>
                                </td>
                                <td>{{ number_format($reportData['file_summary']['mgate']['records']) }}</td>
                                <td>Rp {{ number_format($reportData['file_summary']['mgate']['amount'], 0, ',', '.') }}</td>
                                <td class="text-success">{{ number_format($reportData['file_summary']['mgate']['matched']) }}</td>
                                <td class="text-warning">{{ number_format($reportData['file_summary']['mgate']['unmatched']) }}</td>
                                <td>
                                    @php $rate = ($reportData['file_summary']['mgate']['matched'] / $reportData['file_summary']['mgate']['records']) * 100 @endphp
                                    <span class="badge badge-{{ $rate >= 99 ? 'success' : 'warning' }}">{{ number_format($rate, 2) }}%</span>
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <i class="fal fa-check"></i> Processed
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Discrepancies Detail -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fal fa-search text-warning"></i> Detail Selisih & Discrepancies
                </h5>
                <div class="card-header-actions">
                    <span class="badge badge-warning">{{ count($reportData['discrepancies']) }} Items</span>
                </div>
            </div>
            <div class="card-body">
                @if(count($reportData['discrepancies']) > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Source File</th>
                                <th>Reference</th>
                                <th>AGN Amount</th>
                                <th>M-Gate Amount</th>
                                <th>Difference</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData['discrepancies'] as $disc)
                            <tr>
                                <td><strong>{{ $disc['id'] }}</strong></td>
                                <td>
                                    @if($disc['type'] == 'Amount Mismatch')
                                        <span class="badge badge-warning">{{ $disc['type'] }}</span>
                                    @elseif($disc['type'] == 'Missing Transaction')
                                        <span class="badge badge-danger">{{ $disc['type'] }}</span>
                                    @else
                                        <span class="badge badge-info">{{ $disc['type'] }}</span>
                                    @endif
                                </td>
                                <td>{{ $disc['source_file'] }}</td>
                                <td><code>{{ $disc['reference'] }}</code></td>
                                <td>Rp {{ number_format($disc['agn_amount'], 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($disc['mgate_amount'], 0, ',', '.') }}</td>
                                <td class="{{ $disc['difference'] < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $disc['difference'] > 0 ? '+' : '' }}Rp {{ number_format($disc['difference'], 0, ',', '.') }}
                                </td>
                                <td>
                                    @if($disc['status'] == 'Resolved')
                                        <span class="badge badge-success">{{ $disc['status'] }}</span>
                                    @elseif($disc['status'] == 'Under Review')
                                        <span class="badge badge-warning">{{ $disc['status'] }}</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $disc['status'] }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary btn-sm" onclick="viewDetail('{{ $disc['id'] }}')">
                                            <i class="fal fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-sm" onclick="resolveDiscrepancy('{{ $disc['id'] }}')">
                                            <i class="fal fa-check"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fal fa-check-circle fa-3x text-success mb-3"></i>
                    <h5 class="text-success">Tidak Ada Selisih!</h5>
                    <p class="text-muted">Semua data telah match dengan sempurna.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Statistics Charts -->
<div class="row mt-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fal fa-chart-pie text-info"></i> Match Rate by Source
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="matchRateChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fal fa-chart-bar text-success"></i> Volume by File Type
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="volumeChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Processing Info -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-info-100">
                <h5 class="card-title text-info">
                    <i class="fal fa-info-circle"></i> Informasi Proses
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-item">
                            <strong>Tanggal Settlement:</strong><br>
                            {{ date('d F Y', strtotime($tanggalRekon)) }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-item">
                            <strong>Waktu Proses:</strong><br>
                            {{ $reportData['summary']['processing_time'] }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-item">
                            <strong>File Diproses:</strong><br>
                            {{ $reportData['summary']['processed_files'] }} files
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-item">
                            <strong>Generated:</strong><br>
                            {{ date('d/m/Y H:i:s') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center">
                <div class="btn-group btn-group-lg">
                    <a href="{{ site_url('rekon') }}" class="btn btn-outline-primary">
                        <i class="fal fa-plus"></i> Proses Baru
                    </a>
                    <button class="btn btn-success" onclick="downloadReport('excel')">
                        <i class="fal fa-download"></i> Download Excel
                    </button>
                    <button class="btn btn-danger" onclick="downloadReport('pdf')">
                        <i class="fal fa-download"></i> Download PDF
                    </button>
                    <a href="{{ site_url('dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fal fa-home"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.info-item {
    padding: 10px;
    text-align: center;
}

.chart-container {
    position: relative;
    height: 300px;
}

.progress-sm {
    height: 0.5rem;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Initialize charts
    initMatchRateChart();
    initVolumeChart();
});

function initMatchRateChart() {
    const ctx = document.getElementById('matchRateChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['AGN Detail vs M-Gate', 'Settlement Education', 'Settlement Pajak'],
            datasets: [{
                data: [99.97, 100.0, 99.78],
                backgroundColor: [
                    '#4e73df',
                    '#1cc88a', 
                    '#f6c23e'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function initVolumeChart() {
    const ctx = document.getElementById('volumeChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['AGN Detail', 'Settlement Edu', 'Settlement Pajak', 'M-Gate'],
            datasets: [{
                label: 'Records',
                data: [15847, 2456, 892, 18923],
                backgroundColor: '#4e73df'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function downloadReport(type) {
    const url = `{{ site_url('rekon/reports/download') }}/${type}/{{ $tanggalRekon }}`;
    window.open(url, '_blank');
    toastr.info(`Download ${type.toUpperCase()} akan dimulai...`);
}

function viewDetail(discrepancyId) {
    // TODO: Implement view detail modal or page
    toastr.info('Detail discrepancy: ' + discrepancyId);
}

function resolveDiscrepancy(discrepancyId) {
    if (confirm('Yakin ingin mark discrepancy ini sebagai resolved?')) {
        // TODO: Implement resolve discrepancy
        toastr.success('Discrepancy ' + discrepancyId + ' telah di-resolve');
    }
}
</script>
@endpush
