@extends('layouts.app')

@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        <i class="fal fa-cogs"></i> {{ $title }}
        <small>Jalankan proses rekonsiliasi untuk tanggal {{ date('d/m/Y', strtotime($tanggalRekon ?? date('Y-m-d', strtotime('-1 day')))) }}</small>
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
                        <div class="step completed">
                            <div class="step-number"><i class="fal fa-check"></i></div>
                            <div class="step-title">Validasi Data</div>
                        </div>
                        <div class="step-line completed"></div>
                        <div class="step active">
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
        <div class="alert alert-primary" id="info-alert">
            <i class="fal fa-info-circle"></i>
            <strong>Siap Memulai Proses Rekonsiliasi</strong> 
            Untuk tanggal settlement: {{ date('d/m/Y', strtotime($tanggalRekon ?? date('Y-m-d', strtotime('-1 day')))) }}
        </div>
    </div>
</div>

<!-- Process Control -->
<div class="row">
    <div class="col-12">
        <div class="card" id="process-control-card">
            <div class="card-header bg-primary-100">
                <h5 class="card-title text-primary">
                    <i class="fal fa-play-circle"></i> Kontrol Proses Rekonsiliasi
                </h5>
            </div>
            <div class="card-body text-center">
                <div class="mb-4">
                    <h4 class="text-primary">Proses Rekonsiliasi Settlement</h4>
                    <p class="text-muted">
                        Klik tombol di bawah untuk memulai proses rekonsiliasi otomatis.<br>
                        Proses ini akan melakukan matching data dan generate laporan.
                    </p>
                </div>
                
                <div class="mb-4">
                    <button type="button" id="btn-start-reconciliation" class="btn btn-primary btn-lg">
                        <i class="fal fa-rocket"></i> Mulai Proses Rekonsiliasi
                    </button>
                </div>
                
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="alert alert-info">
                            <i class="fal fa-clock"></i>
                            <strong>Perkiraan Waktu:</strong> 2-5 menit tergantung volume data<br>
                            <small class="text-muted">Pastikan tidak menutup browser selama proses berlangsung</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Progress Card (Hidden by default) -->
<div class="row" id="progress-section" style="display: none;">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-info-100">
                <h5 class="card-title text-info">
                    <i class="fal fa-spinner fa-spin"></i> Proses Sedang Berjalan
                </h5>
            </div>
            <div class="card-body">
                <div class="progress mb-3" style="height: 25px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         id="progress-bar" 
                         role="progressbar" 
                         style="width: 0%">
                        <span id="progress-text">0%</span>
                    </div>
                </div>
                
                <div id="process-steps">
                    <div class="step-item" id="step-1">
                        <i class="fal fa-hourglass-half text-muted"></i>
                        <span>Mempersiapkan data...</span>
                        <small class="text-muted float-right" id="step-1-time"></small>
                    </div>
                    <div class="step-item" id="step-2" style="display: none;">
                        <i class="fal fa-hourglass-half text-muted"></i>
                        <span>Melakukan matching data...</span>
                        <small class="text-muted float-right" id="step-2-time"></small>
                    </div>
                    <div class="step-item" id="step-3" style="display: none;">
                        <i class="fal fa-hourglass-half text-muted"></i>
                        <span>Menganalisis perbedaan...</span>
                        <small class="text-muted float-right" id="step-3-time"></small>
                    </div>
                    <div class="step-item" id="step-4" style="display: none;">
                        <i class="fal fa-hourglass-half text-muted"></i>
                        <span>Membuat laporan...</span>
                        <small class="text-muted float-right" id="step-4-time"></small>
                    </div>
                    <div class="step-item" id="step-5" style="display: none;">
                        <i class="fal fa-hourglass-half text-muted"></i>
                        <span>Menyelesaikan proses...</span>
                        <small class="text-muted float-right" id="step-5-time"></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Results Card (Hidden by default) -->
<div class="row" id="results-section" style="display: none;">
    <div class="col-12">
        <div class="card border-success">
            <div class="card-header bg-success-100">
                <h5 class="card-title text-success">
                    <i class="fal fa-check-circle"></i> Proses Rekonsiliasi Selesai
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <i class="fal fa-trophy"></i>
                    <strong>Rekonsiliasi Berhasil!</strong> 
                    Proses untuk tanggal {{ date('d/m/Y', strtotime($tanggalRekon)) }} telah selesai.
                </div>
                
                <div class="row" id="reconciliation-summary">
                    <!-- Will be populated by JavaScript -->
                </div>
                
                <div class="text-center mt-4">
                    <a href="{{ site_url('rekon/reports/' . date('Y-m-d', strtotime($tanggalRekon))) }}" class="btn btn-primary btn-lg">
                        <i class="fal fa-file-alt"></i> Lihat Laporan
                    </a>
                    <a href="{{ site_url('rekon') }}" class="btn btn-outline-primary btn-lg ml-2">
                        <i class="fal fa-plus"></i> Proses Baru
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Navigation Buttons -->
<div class="row mt-4" id="navigation-buttons">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ site_url('rekon/step2') }}" class="btn btn-outline-secondary">
                        <i class="fal fa-arrow-left"></i> Kembali ke Validasi
                    </a>
                    
                    <div class="text-center">
                        <span class="text-muted">
                            Tanggal: <strong>{{ date('d/m/Y', strtotime($tanggalRekon ?? date('Y-m-d', strtotime('-1 day')))) }}</strong>
                        </span>
                    </div>
                    
                    <a href="{{ site_url('dashboard') }}" class="btn btn-outline-primary">
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

.step-item {
    padding: 8px 0;
    border-bottom: 1px solid #f8f9fa;
}

.step-item:last-child {
    border-bottom: none;
}

.step-item.completed i {
    color: #28a745 !important;
}
</style>
@endpush

@push('scripts')
<script>
let reconciliationInProgress = false;

$(document).ready(function() {
    $('#btn-start-reconciliation').on('click', function() {
        startReconciliation();
    });
});

function startReconciliation() {
    if (reconciliationInProgress) {
        return;
    }
    
    reconciliationInProgress = true;
    
    // Hide control card and show progress
    $('#process-control-card').slideUp();
    $('#navigation-buttons').slideUp();
    $('#info-alert').removeClass('alert-primary').addClass('alert-info');
    $('#info-alert i').removeClass('fa-info-circle').addClass('fa-spinner fa-spin');
    $('#info-alert strong').text('Proses Rekonsiliasi Sedang Berjalan');
    $('#progress-section').slideDown();
    
    // Simulate reconciliation process
    simulateReconciliationProcess();
}

function simulateReconciliationProcess() {
    const steps = [
        { step: 1, duration: 2000, progress: 20, text: 'Mempersiapkan data...' },
        { step: 2, duration: 3000, progress: 40, text: 'Melakukan matching data...' },
        { step: 3, duration: 2500, progress: 65, text: 'Menganalisis perbedaan...' },
        { step: 4, duration: 2000, progress: 85, text: 'Membuat laporan...' },
        { step: 5, duration: 1500, progress: 100, text: 'Menyelesaikan proses...' }
    ];
    
    let currentStep = 0;
    
    function processNextStep() {
        if (currentStep < steps.length) {
            const stepInfo = steps[currentStep];
            const stepId = `#step-${stepInfo.step}`;
            
            // Show current step
            $(stepId).show();
            $(stepId + '-time').text('Memproses...');
            
            // Update progress bar
            $('#progress-bar').css('width', stepInfo.progress + '%');
            $('#progress-text').text(stepInfo.progress + '%');
            
            // Complete step after duration
            setTimeout(() => {
                // Mark step as completed
                $(stepId + ' i').removeClass('fa-hourglass-half text-muted')
                              .addClass('fa-check-circle text-success');
                $(stepId + '-time').text('Selesai');
                $(stepId).addClass('completed');
                
                currentStep++;
                
                if (currentStep < steps.length) {
                    processNextStep();
                } else {
                    // Process completed
                    completeReconciliation();
                }
            }, stepInfo.duration);
        }
    }
    
    // Start first step
    processNextStep();
}

function completeReconciliation() {
    // Call actual reconciliation API
    $.ajax({
        url: '{{ site_url("rekon/step3/process") }}',
        type: 'POST',
        data: {
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        },
        success: function(response) {
            if (response.success) {
                showReconciliationResults();
            } else {
                showReconciliationError(response.message);
            }
        },
        error: function(xhr) {
            showReconciliationError('Terjadi kesalahan saat memproses rekonsiliasi');
        }
    });
}

function showReconciliationResults() {
    // Hide progress section
    $('#progress-section').slideUp();
    
    // Update info alert
    $('#info-alert').removeClass('alert-info').addClass('alert-success');
    $('#info-alert i').removeClass('fa-spinner fa-spin').addClass('fa-check-circle');
    $('#info-alert strong').text('Rekonsiliasi Berhasil Diselesaikan!');
    
    // Populate results summary
    $('#reconciliation-summary').html(`
        <div class="col-md-3">
            <div class="text-center">
                <h4 class="text-success">99.8%</h4>
                <p class="text-muted mb-0">Match Rate</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-center">
                <h4 class="text-warning">12</h4>
                <p class="text-muted mb-0">Selisih Items</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-center">
                <h4 class="text-info">4</h4>
                <p class="text-muted mb-0">File Diproses</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-center">
                <h4 class="text-primary">${new Date().toLocaleTimeString('id-ID')}</h4>
                <p class="text-muted mb-0">Waktu Selesai</p>
            </div>
        </div>
    `);
    
    // Show results section
    $('#results-section').slideDown();
    
    // Show success toast
    toastr.success('Proses rekonsiliasi berhasil diselesaikan!');
    
    reconciliationInProgress = false;
}

function showReconciliationError(message) {
    // Hide progress section
    $('#progress-section').slideUp();
    
    // Update info alert
    $('#info-alert').removeClass('alert-info').addClass('alert-danger');
    $('#info-alert i').removeClass('fa-spinner fa-spin').addClass('fa-exclamation-triangle');
    $('#info-alert strong').text('Proses Rekonsiliasi Gagal');
    
    // Show error message
    toastr.error(message || 'Terjadi kesalahan saat memproses rekonsiliasi');
    
    // Show navigation buttons again
    $('#navigation-buttons').slideDown();
    $('#process-control-card').slideDown();
    
    reconciliationInProgress = false;
}
</script>
@endpush
