@extends('layouts.app')

@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        <i class="fal fa-calendar-check"></i> {{ $title }}
        <small>Sistem rekonsiliasi settlement dengan workflow berurutan</small>
    </h1>
</div>

<div class="row">
    <!-- Main Form Card -->
    <div class="col-xl-8 col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fal fa-calendar-plus text-primary"></i>
                    Buat Proses Rekonsiliasi Baru
                </h3>
            </div>
            <div class="card-body">
                <form action="{{ site_url('rekon/process/create') }}" method="POST">
                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                    
                    <div class="form-group">
                        <label for="tanggal_rekon" class="form-label">
                            <strong>Tanggal Settlement</strong> 
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fal fa-calendar"></i></span>
                            </div>
                            <input type="date" 
                                   class="form-control" 
                                   id="tanggal_rekon" 
                                   name="tanggal_rekon" 
                                   value="{{ date('Y-m-d', strtotime('-1 day')) }}"
                                   max="{{ date('Y-m-d') }}" 
                                   required>
                        </div>
                        <div class="help-block">
                            <i class="fal fa-info-circle text-info"></i> 
                            Pilih tanggal settlement yang akan direkonsiliasi. Default: <strong>{{ date('d/m/Y', strtotime('-1 day')) }}</strong>
                        </div>
                    </div>

                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fal fa-rocket"></i> Buat Proses Rekonsiliasi
                        </button>
                        <a href="{{ site_url('dashboard') }}" class="btn btn-secondary btn-lg ml-2">
                            <i class="fal fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Workflow Info Card -->
        <div class="card mt-4">
            <div class="card-header bg-fusion-50">
                <h5 class="card-title ">
                    <i class="fal fa-sitemap"></i> Alur Proses Rekonsiliasi
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="timeline timeline-sm">
                            <div class="timeline-item">
                                <div class="timeline-item-marker">
                                    <div class="timeline-item-marker-text">1</div>
                                    <div class="timeline-item-marker-indicator bg-primary"></div>
                                </div>
                                <div class="timeline-item-content">
                                    <h6 class="timeline-item-title">Pilih Tanggal Settlement</h6>
                                    <p class="timeline-item-description">Tentukan tanggal settlement yang akan direkonsiliasi dan buat proses baru</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-item-marker">
                                    <div class="timeline-item-marker-text">2</div>
                                    <div class="timeline-item-marker-indicator bg-info"></div>
                                </div>
                                <div class="timeline-item-content">
                                    <h6 class="timeline-item-title">Upload File Settlement</h6>
                                    <p class="timeline-item-description">Upload file data dari berbagai sumber:
                                        <br><small class="text-muted">• Data Agregator Detail • Data Settlement Education • Data Settlement Pajak • Data M-Gate (Payment Gateway)</small>
                                    </p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-item-marker">
                                    <div class="timeline-item-marker-text">3</div>
                                    <div class="timeline-item-marker-indicator bg-warning"></div>
                                </div>
                                <div class="timeline-item-content">
                                    <h6 class="timeline-item-title">Validasi & Review Data</h6>
                                    <p class="timeline-item-description">Sistem akan memvalidasi kelengkapan dan konsistensi data yang telah diupload</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-item-marker">
                                    <div class="timeline-item-marker-text">4</div>
                                    <div class="timeline-item-marker-indicator bg-success"></div>
                                </div>
                                <div class="timeline-item-content">
                                    <h6 class="timeline-item-title">Proses Rekonsiliasi</h6>
                                    <p class="timeline-item-description">Sistem melakukan matching otomatis dan generate laporan rekonsiliasi</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics & Recent Processes -->
    <div class="col-xl-4 col-lg-12">
        <!-- Statistics Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fal fa-chart-bar text-success"></i> Statistik
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Total Proses</span>
                            <strong class="text-primary">15</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Pending</span>
                            <strong class="text-warning">2</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Completed</span>
                            <strong class="text-success">13</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Terakhir</span>
                            <strong class="text-info">{{ date('d/m/Y', strtotime('-1 day')) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Processes Card -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fal fa-history text-info"></i> Proses Terbaru
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <div class="fw-500">{{ date('d/m/Y', strtotime('-1 day')) }}</div>
                        <small class="text-muted">ID: #001</small>
                    </div>
                    <div>
                        <span class="badge badge-success">Completed</span>
                    </div>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <div class="fw-500">{{ date('d/m/Y', strtotime('-2 days')) }}</div>
                        <small class="text-muted">ID: #002</small>
                    </div>
                    <div>
                        <span class="badge badge-success">Completed</span>
                    </div>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <div class="fw-500">{{ date('d/m/Y', strtotime('-3 days')) }}</div>
                        <small class="text-muted">ID: #003</small>
                    </div>
                    <div>
                        <span class="badge badge-warning">Pending</span>
                    </div>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-500">{{ date('d/m/Y', strtotime('-4 days')) }}</div>
                        <small class="text-muted">ID: #004</small>
                    </div>
                    <div>
                        <span class="badge badge-success">Completed</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Important Notes Card -->
        <div class="card mt-4">
            <div class="card-header bg-warning-100">
                <h5 class="card-title text-warning-700">
                    <i class="fal fa-exclamation-triangle"></i> Catatan Penting
                </h5>
            </div>
            <div class="card-body">
                <ul class="small text-muted mb-0">
                    <li>Pastikan tanggal settlement sudah benar sebelum melanjutkan</li>
                    <li>Proses rekonsiliasi untuk tanggal yang sama tidak dapat dibuat dua kali</li>
                    <li>Siapkan semua file settlement sebelum memulai proses</li>
                    <li>Jangan menutup browser selama proses upload dan rekonsiliasi</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Set max date to today
    $('#tanggal_rekon').attr('max', new Date().toISOString().split('T')[0]);
    
    // Form validation
    $('form').on('submit', function(e) {
        var tanggal = $('#tanggal_rekon').val();
        if (!tanggal) {
            e.preventDefault();
            toastr.error('Tanggal settlement harus dipilih');
            return false;
        }
        
        // Show loading
        var submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fal fa-spinner fa-spin"></i> Membuat Proses...');
        
        // Show processing toast
        toastr.info('Sedang membuat proses rekonsiliasi...');
    });
});
</script>
@endpush
