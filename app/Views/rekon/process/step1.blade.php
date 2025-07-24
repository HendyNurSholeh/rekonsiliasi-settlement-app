@extends('layouts.app')

@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        <i class="fal fa-upload"></i> {{ $title }}
        <small>Upload file settlement untuk tanggal {{ date('d/m/Y', strtotime($tanggalRekon)) }}</small>
    </h1>
</div>

<!-- Progress Steps -->
{{-- <div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body py-2">
                <div class="d-flex align-items-center">
                    <div class="step-progress d-flex align-items-center w-100">
                        <div class="step active">
                            <div class="step-number">1</div>
                            <div class="step-title">Upload Files</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step">
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
        <div class="alert alert-info">
            <i class="fal fa-info-circle"></i>
            <strong>Tanggal Settlement:</strong> {{ date('d/m/Y', strtotime($tanggalRekon ?? date('Y-m-d', strtotime('-1 day')))) }}
            <span class="float-right">
                <strong>Process ID:</strong> #PRK-{{ date('ymd', strtotime($tanggalRekon ?? date('Y-m-d', strtotime('-1 day')))) }}-001
            </span>
        </div>
    </div>
</div>

<div class="row">
    <!-- Upload Data Agregator Detail -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-warning">
            <div class="card-header bg-warning-200">
                <h5 class="card-title">
                    <i class="fal fa-upload text-warning"></i>
                    Data Agregator Detail
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Upload file data transaksi detail dari agregator untuk tanggal {{ date('d/m/Y', strtotime($tanggalRekon ?? date('Y-m-d', strtotime('-1 day')))) }}
                </p>
                <form id="form-agn-detail" enctype="multipart/form-data">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="file-agn-detail" name="file" accept=".csv,.xlsx,.xls">
                        <label class="custom-file-label" for="file-agn-detail">Pilih file...</label>
                    </div>
                    <button type="button" class="btn btn-primary btn-block mt-2" onclick="uploadFile('agn_detail')">
                        <i class="fal fa-upload"></i> Upload
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Upload Data Settlement Education -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-success">
            <div class="card-header bg-success-200">
                <h5 class="card-title">
                    <i class="fal fa-check-circle text-success"></i>
                    Settlement Education
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <i class="fal fa-check"></i> 
                    <strong>2,456</strong> records uploaded
                </div>
                <div class="text-center">
                    <button class="btn btn-outline-success btn-sm" onclick="reuploadFile('agn_settle_edu')">
                        <i class="fal fa-redo"></i> Re-upload
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Data Settlement Pajak -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-warning">
            <div class="card-header bg-warning-200">
                <h5 class="card-title">
                    <i class="fal fa-upload text-warning"></i>
                    Settlement Pajak
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Upload file data settlement pajak untuk tanggal {{ date('d/m/Y', strtotime($tanggalRekon ?? date('Y-m-d', strtotime('-1 day')))) }}
                </p>
                <form id="form-agn-settle-pajak" enctype="multipart/form-data">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="file-agn-settle-pajak" name="file" accept=".csv,.xlsx,.xls">
                        <label class="custom-file-label" for="file-agn-settle-pajak">Pilih file...</label>
                    </div>
                    <button type="button" class="btn btn-primary btn-block mt-2" onclick="uploadFile('agn_settle_pajak')">
                        <i class="fal fa-upload"></i> Upload
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Upload Data M-Gate -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-warning">
            <div class="card-header bg-warning-200">
                <h5 class="card-title">
                    <i class="fal fa-upload text-warning"></i>
                    Data M-Gate
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Upload file transaksi M-Gate (Payment Gateway) untuk tanggal {{ date('d/m/Y', strtotime($tanggalRekon ?? date('Y-m-d', strtotime('-1 day')))) }}
                </p>
                <form id="form-agn-trx-mgate" enctype="multipart/form-data">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="file-agn-trx-mgate" name="file" accept=".csv,.xlsx,.xls">
                        <label class="custom-file-label" for="file-agn-trx-mgate">Pilih file...</label>
                    </div>
                    <button type="button" class="btn btn-primary btn-block mt-2" onclick="uploadFile('agn_trx_mgate')">
                        <i class="fal fa-upload"></i> Upload
                    </button>
                </form>
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
                    <a href="{{ site_url('rekon/process') }}" class="btn btn-secondary">
                        <i class="fal fa-arrow-left"></i> Kembali ke Daftar
                    </a>
                    
                    <div class="upload-summary">
                        <span class="text-muted">Status Upload:</span>
                        <span class="ml-2">
                            <strong class="text-warning">1/4 files</strong>
                        </span>
                    </div>
                    
                    <div class="demo-buttons">
                        <button class="btn btn-outline-info mr-2" onclick="simulateAllUploads()">
                            <i class="fal fa-magic"></i> Simulasi Upload Semua (Demo)
                        </button>
                        <button class="btn btn-outline-primary" disabled id="btn-next-step">
                            <i class="fal fa-lock"></i> Upload Semua File Dulu
                        </button>
                    </div>
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

.step-title {
    font-size: 12px;
    font-weight: 500;
    color: #6c757d;
}

.step.active .step-title {
    color: #007bff;
    font-weight: bold;
}

.step-line {
    flex: 1;
    height: 2px;
    background: #e9ecef;
    margin: 0 20px;
    margin-bottom: 20px;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Custom file input labels
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
    });
});

function uploadFile(type) {
    var formId = '#form-' + type.replace('_', '-');
    var fileInputId = '#file-' + type.replace('_', '-');
    var fileInput = $(fileInputId)[0];
    
    if (!fileInput.files[0]) {
        toastr.error('Pilih file terlebih dahulu');
        return;
    }
    
    // Show loading
    var btn = $(formId).find('button');
    var originalHtml = btn.html();
    btn.prop('disabled', true).html('<i class="fal fa-spinner fa-spin"></i> Uploading...');
    
    // Simulate upload
    setTimeout(() => {
        // Update card to show uploaded
        var card = $(formId).closest('.card');
        card.removeClass('border-warning').addClass('border-success');
        card.find('.card-header').removeClass('bg-warning-200').addClass('bg-success-200');
        card.find('.card-title i').removeClass('fa-upload text-warning').addClass('fa-check-circle text-success');
        
        // Update card body
        $(formId).parent().html(`
            <div class="alert alert-success">
                <i class="fal fa-check"></i> 
                <strong>5,234</strong> records uploaded (Demo)
            </div>
            <div class="text-center">
                <button class="btn btn-outline-success btn-sm" onclick="reuploadFile('${type}')">
                    <i class="fal fa-redo"></i> Re-upload
                </button>
            </div>
        `);
        
        toastr.success('File berhasil diupload!');
        
        // Check if all files are uploaded
        checkUploadStatus();
    }, 1500);
}

function checkUploadStatus() {
    var uploadedCount = $('.border-success').length;
    var totalFiles = 4;
    
    $('.upload-summary strong').text(uploadedCount + '/' + totalFiles + ' files');
    
    if (uploadedCount === totalFiles) {
        $('.upload-summary strong').removeClass('text-warning').addClass('text-success');
        $('#btn-next-step').removeClass('btn-outline-primary').addClass('btn-success')
                          .prop('disabled', false)
                          .html('<i class="fal fa-arrow-right"></i> Lanjut ke Validasi')
                          .attr('onclick', "window.location.href='{{ site_url('rekon/process/step2') }}'");
    } else {
        $('.upload-summary strong').removeClass('text-success').addClass('text-warning');
    }
}

function reuploadFile(type) {
    if (confirm('Yakin ingin upload ulang? Data sebelumnya akan dihapus.')) {
        // Reset upload status for this type
        window.location.reload();
    }
}

function simulateAllUploads() {
    // Show loading animation
    toastr.info('Mensimulasikan upload semua file...');
    
    // Disable demo button
    $('[onclick="simulateAllUploads()"]').prop('disabled', true).html('<i class="fal fa-spinner fa-spin"></i> Uploading...');
    
    // Simulate upload process for remaining files
    setTimeout(() => {
        // Update cards to show all uploaded
        $('.card.border-warning').removeClass('border-warning').addClass('border-success');
        $('.card-header.bg-warning-200').removeClass('bg-warning-200').addClass('bg-success-200');
        $('.fa-upload.text-warning').removeClass('fa-upload text-warning').addClass('fa-check-circle text-success');
        
        // Update card bodies to show success
        $('#form-agn-detail, #form-agn-settle-pajak, #form-agn-trx-mgate').parent().html(`
            <div class="alert alert-success">
                <i class="fal fa-check"></i> 
                <strong>File berhasil diupload</strong> (Demo)
            </div>
            <div class="text-center">
                <button class="btn btn-outline-success btn-sm" onclick="reuploadFile('demo')">
                    <i class="fal fa-redo"></i> Re-upload
                </button>
            </div>
        `);
        
        // Update status and enable next button
        $('.upload-summary strong').removeClass('text-warning').addClass('text-success').text('4/4 files');
        $('#btn-next-step').removeClass('btn-outline-primary').addClass('btn-success')
                          .prop('disabled', false)
                          .html('<i class="fal fa-arrow-right"></i> Lanjut ke Validasi')
                          .attr('onclick', "window.location.href='{{ site_url('rekon/process/step2') }}'");
        
        toastr.success('Semua file berhasil diupload!');
    }, 2000);
}
</script>
@endpush
