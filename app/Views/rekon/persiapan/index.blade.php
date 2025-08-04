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
                <!-- Alert for existing process -->
                @if(session('need_confirmation'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading">
                        <i class="fal fa-exclamation-triangle"></i> Proses Sudah Ada
                    </h5>
                    <p>{{ session('warning') }}</p>
                    <hr>
                    <p class="mb-0">
                        <strong>Pilihan Anda:</strong><br>
                        • <strong>Batalkan:</strong> Kembali dan pilih tanggal lain<br>
                        • <strong>Reset & Lanjutkan:</strong> Hapus semua data rekonsiliasi untuk tanggal tersebut dan buat proses baru
                    </p>
                    <div class="mt-3">
                        <button type="button" class="btn btn-danger" onclick="confirmReset('{{ session('existing_date') }}')">
                            <i class="fal fa-redo"></i> Reset & Lanjutkan
                        </button>
                        <button type="button" class="btn btn-secondary ml-2" onclick="dismissAlert()">
                            <i class="fal fa-times"></i> Batalkan
                        </button>
                    </div>
                </div>
                @endif

                <form id="processForm" action="{{ site_url('rekon/create') }}" method="POST">
                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                    <input type="hidden" id="reset_confirmed" name="reset_confirmed" value="false" />
                    
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
                                   value="{{ session('existing_date') ?? ($defaultDate ?? date('Y-m-d', strtotime('-1 day'))) }}"
                                   max="{{ date('Y-m-d') }}" 
                                   required>
                        </div>
                        <div class="help-block">
                            <i class="fal fa-info-circle text-info"></i> 
                            Pilih tanggal settlement yang akan direkonsiliasi. Default: <strong>{{ isset($defaultDate) ? date('d/m/Y', strtotime($defaultDate)) : date('d/m/Y', strtotime('-1 day')) }}</strong>
                        </div>
                        <!-- Date status indicator -->
                        <div id="dateStatus" class="mt-2" style="display: none;"></div>
                    </div>

                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
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
                <h5 class="card-title">
                    <i class="fal fa-sitemap"></i> Alur Proses Rekonsiliasi
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <ol class="list-group list-group-numbered">
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold">Pilih Tanggal Settlement</div>
                                    Tentukan tanggal settlement yang akan direkonsiliasi
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold">Upload File Settlement</div>
                                    Upload file data dari berbagai sumber (Agregator, Education, Pajak, M-Gate)
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold">Validasi & Proses Data</div>
                                    Sistem memvalidasi dan memproses data yang telah diupload
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold">Proses Rekonsiliasi</div>
                                    Sistem melakukan matching dan generate laporan rekonsiliasi
                                </div>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics & Recent Processes -->
    <div class="col-xl-4 col-lg-12">
        <!-- Important Notes Card -->
        <div class="card">
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

@push('styles')
<link rel="stylesheet" href="{{ base_url('css/rekon/persiapan/index.css') }}">
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Set max date to today
    $('#tanggal_rekon').attr('max', new Date().toISOString().split('T')[0]);
    
    // Check date when it changes
    $('#tanggal_rekon').on('change', function() {
        const selectedDate = $(this).val();
        if (selectedDate) {
            checkDateExists(selectedDate);
        }
    });
    
    // Form validation
    $('#processForm').on('submit', function(e) {
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
    });
});

function checkDateExists(date) {
    const statusDiv = $('#dateStatus');
    
    // Show loading indicator
    statusDiv.html('<i class="fal fa-spinner fa-spin text-info"></i> Memeriksa tanggal...').show();
    
    // Get CSRF token
    const csrfName = $('#processForm input[name^="csrf_"]').attr('name');
    const csrfValue = $('#processForm input[name^="csrf_"]').val();
    
    $.ajax({
        url: '{{ site_url("rekon/checkDate") }}',
        method: 'POST',
        data: {
            tanggal: date,
            [csrfName]: csrfValue
        },
        success: function(response) {
            // Update CSRF token
            if (response.csrf_token && response.csrf_name) {
                $('#processForm input[name^="csrf_"]').attr('name', response.csrf_name).val(response.csrf_token);
            }
            
            if (response.success) {
                if (response.exists) {
                    statusDiv.html(`
                        <div class="alert alert-info alert-sm">
                            <i class="fal fa-info-circle"></i> 
                            Proses untuk tanggal <strong>${response.formatted_date}</strong> sudah ada
                        </div>
                    `);
                } else {
                    statusDiv.html(`
                        <div class="alert alert-success alert-sm">
                            <i class="fal fa-check-circle"></i> 
                            Tanggal <strong>${response.formatted_date}</strong> tersedia untuk proses baru
                        </div>
                    `);
                }
            } else {
                statusDiv.html(`
                    <div class="alert alert-danger alert-sm">
                        <i class="fal fa-exclamation-circle"></i> 
                        Error: ${response.message}
                    </div>
                `);
            }
        },
        error: function(xhr) {
            if (xhr.status === 403 || xhr.status === 419) {
                statusDiv.html(`
                    <div class="alert alert-warning alert-sm">
                        <i class="fal fa-exclamation-triangle"></i> 
                        Session expired. Silakan refresh halaman.
                    </div>
                `);
            } else {
                statusDiv.html(`
                    <div class="alert alert-danger alert-sm">
                        <i class="fal fa-exclamation-circle"></i> 
                        Terjadi kesalahan saat memeriksa tanggal
                    </div>
                `);
            }
        }
    });
}

function confirmReset(date) {
    // Gunakan konfirmasi JavaScript biasa agar pasti berfungsi
    if (confirm(`Reset proses rekonsiliasi untuk tanggal ${formatDate(date)}? Semua data akan dihapus.`)) {
        doReset(date);
    }
}

function doReset(date) {
    $('#reset_confirmed').val('true');
    $('#tanggal_rekon').val(date);
    $('#submitBtn').html('<i class="fal fa-redo fa-spin"></i> Mereset Proses...');
    $('#processForm').submit();
}

function dismissAlert() {
    $('.alert-warning').fadeOut();
    $('#tanggal_rekon').val('{{ $defaultDate ?? date('Y-m-d', strtotime('-1 day')) }}');
    $('#dateStatus').hide();
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID');
}
</script>
@endpush
