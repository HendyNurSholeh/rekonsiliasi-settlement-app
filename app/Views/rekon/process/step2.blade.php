@extends('layouts.app')

@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        <i class="fal fa-check-square"></i> {{ $title }}
        <small>Review dan validasi data untuk tanggal {{ date('d/m/Y', strtotime($tanggalRekon ?? date('Y-m-d', strtotime('-1 day')))) }}</small>
    </h1>
</div>

<!-- Progress Steps -->
{{-- <div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body py-2">
                <div class="d-flex align-items-center">
                    <div class="step-progress d-flex align-items-center w-100">
                        <div class="step completed">
                            <div class="step-number"><i class="fal fa-check"></i></div>
                            <div class="step-title">Upload Files</div>
                        </div>
                        <div class="step-line completed"></div>
                        <div class="step active">
                            <div class="step-number">2</div>
                            <div class="step-title">Validasi Data</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step">
                            <div class="step-number">3</div>
                            <div class="step-title">Rekonsiliasi</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> --}}

<div class="row">
    <div class="col-12">
        <div class="alert alert-success">
            <i class="fal fa-check-circle"></i>
            <strong>Semua file berhasil diupload!</strong> 
            Tanggal Settlement: {{ date('d/m/Y', strtotime($tanggalRekon ?? date('Y-m-d', strtotime('-1 day')))) }}
        </div>
    </div>
</div>

<!-- Data Summary Cards -->
<div class="row">
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
                            <h3 class="text-primary">15,847</h3>
                            <small class="text-muted">Total Records</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h3 class="text-success">2,456,890,000</h3>
                            <small class="text-muted">Total Amount</small>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="small text-muted">
                    <i class="fal fa-calendar"></i> {{ date('d/m/Y', strtotime($tanggalRekon ?? date('Y-m-d', strtotime('-1 day')))) }}
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
                            <h3 class="text-primary">2,456</h3>
                            <small class="text-muted">Total Records</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h3 class="text-success">156,780,000</h3>
                            <small class="text-muted">Settlement Amount</small>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="small text-muted">
                    <i class="fal fa-calendar"></i> {{ date('d/m/Y', strtotime($tanggalRekon ?? date('Y-m-d', strtotime('-1 day')))) }}
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
                            <h3 class="text-primary">892</h3>
                            <small class="text-muted">Total Records</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h3 class="text-success">45,230,000</h3>
                            <small class="text-muted">Settlement Amount</small>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="small text-muted">
                    <i class="fal fa-calendar"></i> {{ date('d/m/Y', strtotime($tanggalRekon ?? date('Y-m-d', strtotime('-1 day')))) }}
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
                            <h3 class="text-primary">18,923</h3>
                            <small class="text-muted">Total Records</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h3 class="text-success">2,658,900,000</h3>
                            <small class="text-muted">Transaction Amount</small>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="small text-muted">
                    <i class="fal fa-calendar"></i> {{ date('d/m/Y', strtotime($tanggalRekon ?? date('Y-m-d', strtotime('-1 day')))) }}
                    <div class="float-right">
                        <i class="fal fa-check-circle text-success"></i> Valid
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Validation Results -->
<div class="row mt-4">
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
                                <td>15,847 records</td>
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
                                <td>2,456 records</td>
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
                                <td>892 records</td>
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
                                <td>{{ date('d/m/Y', strtotime($tanggalRekon ?? date('Y-m-d', strtotime('-1 day')))) }}</td>
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
                                <td>18,923 records</td>
                                <td>Data transaksi payment gateway tersedia</td>
                            </tr>
                            <tr class="table-success">
                                <td>
                                    <strong>
                                        <i class="fal fa-check-circle text-success"></i> 
                                        Status Keseluruhan
                                    </strong>
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <i class="fal fa-thumbs-up"></i> SIAP PROSES
                                    </span>
                                </td>
                                <td colspan="2">
                                    <strong>Semua validasi berhasil. Data siap untuk diproses rekonsiliasi.</strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Summary Statistics -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary-100">
                <h5 class="card-title text-white">
                    <i class="fal fa-chart-line"></i> Ringkasan Data Settlement
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2-4">
                        <div class="text-center">
                            <h4 class="text-primary">37,270</h4>
                            <p class="text-muted mb-0">Total Transaksi</p>
                        </div>
                    </div>
                    <div class="col-md-2-4">
                        <div class="text-center">
                            <h4 class="text-success">Rp 2,658,900,000</h4>
                            <p class="text-muted mb-0">Total Amount</p>
                        </div>
                    </div>
                    <div class="col-md-2-4">
                        <div class="text-center">
                            <h4 class="text-info">Rp 156,780,000</h4>
                            <p class="text-muted mb-0">Settlement Education</p>
                        </div>
                    </div>
                    <div class="col-md-2-4">
                        <div class="text-center">
                            <h4 class="text-warning">Rp 45,230,000</h4>
                            <p class="text-muted mb-0">Settlement Pajak</p>
                        </div>
                    </div>
                    <div class="col-md-2-4">
                        <div class="text-center">
                            <h4 class="text-primary">Rp 2,658,900,000</h4>
                            <p class="text-muted mb-0">M-Gate Amount</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Navigation Buttons -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ site_url('rekon/step1') }}" class="btn btn-outline-secondary">
                        <i class="fal fa-arrow-left"></i> Kembali ke Upload
                    </a>
                    
                    <div class="text-center">
                        <span class="text-success">
                            <i class="fal fa-check-circle"></i>
                            <strong>Validasi Berhasil</strong>
                        </span>
                    </div>
                    
                    <a href="{{ site_url('rekon/step3') }}" class="btn btn-success btn-lg">
                        <i class="fal fa-rocket"></i> Mulai Rekonsiliasi
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.step-progress {
    display: flex;
    align-items: center;
    justify-content: center;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 8px;
}

.step.active .step-number {
    background: #007bff;
    color: white;
}

.step.completed .step-number {
    background: #28a745;
    color: white;
}

.step-title {
    font-size: 12px;
    font-weight: 500;
    color: #6c757d;
}

.step.active .step-title {
    color: #007bff;
    font-weight: bold;
}

.step.completed .step-title {
    color: #28a745;
    font-weight: bold;
}

.step-line {
    flex: 1;
    height: 2px;
    background: #e9ecef;
    margin: 0 20px;
    margin-bottom: 20px;
}

.step-line.completed {
    background: #28a745;
}

.col-md-2-4 {
    flex: 0 0 20%;
    max-width: 20%;
}

@media (max-width: 768px) {
    .col-md-2-4 {
        flex: 0 0 50%;
        max-width: 50%;
        margin-bottom: 1rem;
    }
}
</style>
@endpush
