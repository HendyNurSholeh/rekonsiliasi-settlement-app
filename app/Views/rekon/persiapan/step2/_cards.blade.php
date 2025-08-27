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
                    <i class="fal fa-calendar"></i> {{ date('d/m/Y', strtotime($tanggalData)) }}
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
                    <i class="fal fa-calendar"></i> {{ date('d/m/Y', strtotime($tanggalData)) }}
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
                    <i class="fal fa-calendar"></i> {{ date('d/m/Y', strtotime($tanggalData)) }}
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
                    <i class="fal fa-calendar"></i> {{ date('d/m/Y', strtotime($tanggalData)) }}
                    <div class="float-right">
                        <i class="fal fa-check-circle text-success"></i> Valid
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>