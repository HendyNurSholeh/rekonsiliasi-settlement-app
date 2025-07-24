@extends('layouts.app')

@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        <i class="fal fa-upload"></i> {{ $title }}
        <small>Upload file settlement untuk tanggal {{ date('d/m/Y', strtotime($tanggalRekon)) }}</small>
    </h1>
</div>

<!-- Success/Error Messages -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fal fa-check-circle"></i> {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fal fa-exclamation-circle"></i> {{ session('error') }}
    <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fal fa-info-circle"></i>
            <strong>Tanggal Settlement:</strong> {{ date('d/m/Y', strtotime($tanggalRekon)) }}
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
                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                    <input type="hidden" name="tanggal_rekon" value="{{ $tanggalRekon }}" />
                    <input type="hidden" name="file_type" value="agn_detail" />
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="file-agn-detail" name="file" accept=".csv,.xlsx,.xls" required>
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
        <div class="card border-warning">
            <div class="card-header bg-warning-200">
                <h5 class="card-title">
                    <i class="fal fa-upload text-warning"></i>
                    Settlement Education
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Upload file settlement education untuk tanggal {{ date('d/m/Y', strtotime($tanggalRekon ?? date('Y-m-d', strtotime('-1 day')))) }}
                </p>
                <form id="form-agn-settle-edu" enctype="multipart/form-data">
                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                    <input type="hidden" name="tanggal_rekon" value="{{ $tanggalRekon }}" />
                    <input type="hidden" name="file_type" value="settle_edu" />
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="file-agn-settle-edu" name="file" accept=".csv,.xlsx,.xls" required>
                        <label class="custom-file-label" for="file-agn-settle-edu">Pilih file...</label>
                    </div>
                    <button type="button" class="btn btn-primary btn-block mt-2" onclick="uploadFile('settle_edu')">
                        <i class="fal fa-upload"></i> Upload
                    </button>
                </form>
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
                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                    <input type="hidden" name="tanggal_rekon" value="{{ $tanggalRekon }}" />
                    <input type="hidden" name="file_type" value="settle_pajak" />
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="file-agn-settle-pajak" name="file" accept=".csv,.xlsx,.xls" required>
                        <label class="custom-file-label" for="file-agn-settle-pajak">Pilih file...</label>
                    </div>
                    <button type="button" class="btn btn-primary btn-block mt-2" onclick="uploadFile('settle_pajak')">
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
                    Upload file transaksi M-Gate (Payment Gateway) untuk tanggal {{ date('d/m/Y', strtotime($tanggalRekon ?? date('Y-m-d', strtotime('-1 day')))) }} <span class="text-danger">*Wajib</span>
                </p>
                <form id="form-agn-trx-mgate" enctype="multipart/form-data">
                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                    <input type="hidden" name="tanggal_rekon" value="{{ $tanggalRekon }}" />
                    <input type="hidden" name="file_type" value="mgate" />
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="file-agn-trx-mgate" name="file" accept=".csv,.xlsx,.xls" required>
                        <label class="custom-file-label" for="file-agn-trx-mgate">Pilih file...</label>
                    </div>
                    <button type="button" class="btn btn-primary btn-block mt-2" onclick="uploadFile('mgate')">
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
                    <a href="{{ site_url('rekon') }}" class="btn btn-secondary">
                        <i class="fal fa-arrow-left"></i> Kembali ke Daftar
                    </a>
                    
                    <div class="upload-summary">
                        <span class="text-muted">Status Upload:</span>
                        <span class="ml-2">
                            <strong class="text-warning" id="upload-count">0/4 files</strong>
                        </span>
                    </div>
                    
                    <div class="action-buttons">
                        <button class="btn btn-success mr-2" onclick="validateAndProceed()" disabled id="btn-validate">
                            <i class="fal fa-check-circle"></i> Validasi & Lanjutkan
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
.upload-checklist .checklist-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.75rem;
    padding: 0.5rem;
    border-radius: 0.375rem;
    background-color: #f8f9fa;
}

.upload-checklist .checklist-item i {
    margin-right: 0.75rem;
    font-size: 1rem;
}

.upload-checklist .checklist-item span {
    flex: 1;
    font-weight: 500;
}

.upload-checklist .checklist-item small {
    margin-left: 0.5rem;
}

.upload-checklist .checklist-item.success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
}

.custom-file-label::after {
    content: "Browse";
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Variabel untuk tracking upload
    let uploadedFiles = [];
    const requiredFiles = ['agn_detail', 'settle_edu', 'settle_pajak', 'mgate'];
    
    // Custom file input labels
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
        
        // Validasi file
        validateFile(this);
    });
    
    // Fungsi validasi file
    function validateFile(input) {
        const file = input.files[0];
        if (!file) return false;
        
        // Cek ukuran file (max 10MB)
        if (file.size > 10 * 1024 * 1024) {
            alert('File terlalu besar! Maksimal 10MB');
            $(input).val('');
            return false;
        }
        
        // Cek format file
        const allowedTypes = ['.xlsx', '.xls', '.csv'];
        const fileName = file.name.toLowerCase();
        const isValidType = allowedTypes.some(type => fileName.endsWith(type));
        
        if (!isValidType) {
            alert('Format file tidak valid! Hanya menerima file Excel (.xlsx, .xls) atau CSV (.csv)');
            $(input).val('');
            return false;
        }
        
        return true;
    }
});

function uploadFile(type) {
    var formId = '#form-' + type.replace('_', '-');
    var fileInputId = '#file-' + type.replace('_', '-');
    var fileInput = $(fileInputId)[0];
    
    if (!fileInput.files[0]) {
        alert('Pilih file terlebih dahulu');
        return;
    }
    
    // Konfirmasi upload
    if (!confirm('Yakin ingin upload file ini?')) {
        return;
    }
    
    // Show loading
    var btn = $(formId).find('button');
    var originalHtml = btn.html();
    btn.prop('disabled', true).html('<i class="fal fa-spinner fa-spin"></i> Uploading...');
    
    // Ambil data form
    var formData = new FormData($(formId)[0]);
    
    // AJAX Upload
    $.ajax({
        url: '{{ site_url("rekon/step1/upload") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                // Update card menjadi success
                var card = $(formId).closest('.card');
                card.removeClass('border-warning').addClass('border-success');
                card.find('.card-header').removeClass('bg-warning-200').addClass('bg-success-200');
                card.find('.card-title i').removeClass('fa-upload text-warning').addClass('fa-check-circle text-success');
                
                // Update card body
                $(formId).parent().html(`
                    <div class="alert alert-success">
                        <i class="fal fa-check"></i> 
                        <strong>File berhasil diupload!</strong><br>
                        <small>${response.message || 'Upload completed successfully'}</small>
                    </div>
                    <div class="text-center">
                        <button class="btn btn-outline-success btn-sm" onclick="reuploadFile('${type}')">
                            <i class="fal fa-redo"></i> Re-upload
                        </button>
                    </div>
                `);
                
                alert('File berhasil diupload!');
                
                // Tambahkan ke daftar uploaded files
                if (!uploadedFiles.includes(type)) {
                    uploadedFiles.push(type);
                }
                
                // Update status upload
                updateUploadStatus();
            } else {
                alert('Gagal upload file: ' + (response.message || 'Unknown error'));
                btn.prop('disabled', false).html(originalHtml);
            }
        },
        error: function(xhr, status, error) {
            alert('Error saat upload file: ' + error);
            btn.prop('disabled', false).html(originalHtml);
        }
    });
}

function updateUploadStatus() {
    const requiredFiles = ['agn_detail', 'settle_edu', 'settle_pajak', 'mgate'];
    const uploadedRequired = uploadedFiles.filter(file => requiredFiles.includes(file));
    const totalRequired = requiredFiles.length;
    
    // Update counter
    $('#upload-count').text(uploadedRequired.length + '/' + totalRequired + ' files');
    
    // Jika semua file wajib sudah diupload
    if (uploadedRequired.length === totalRequired) {
        $('#upload-count').removeClass('text-warning').addClass('text-success');
        $('#btn-validate').prop('disabled', false);
        alert('Semua file wajib telah diupload! Silakan klik "Validasi & Lanjutkan"');
    } else {
        $('#upload-count').removeClass('text-success').addClass('text-warning');
        $('#btn-validate').prop('disabled', true);
    }
}

function reuploadFile(type) {
    if (confirm('Yakin ingin upload ulang? File sebelumnya akan diganti.')) {
        // Reset upload status untuk tipe ini
        uploadedFiles = uploadedFiles.filter(file => file !== type);
        
        // Reload halaman untuk reset form
        window.location.reload();
    }
}

function validateAndProceed() {
    const requiredFiles = ['agn_detail', 'settle_edu', 'settle_pajak', 'mgate'];
    const uploadedRequired = uploadedFiles.filter(file => requiredFiles.includes(file));
    
    if (uploadedRequired.length < requiredFiles.length) {
        alert('Harap upload semua file yang wajib terlebih dahulu!');
        return;
    }
    
    if (!confirm('Yakin ingin melanjutkan ke proses validasi?')) {
        return;
    }
    
    // Disable button dan show loading
    $('#btn-validate').prop('disabled', true).html('<i class="fal fa-spinner fa-spin"></i> Memvalidasi...');
    
    // AJAX untuk validasi
    $.ajax({
        url: '{{ site_url("rekon/step1/validate") }}',
        method: 'POST',
        data: {
            tanggal_rekon: '{{ $tanggalRekon }}',
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        },
        success: function(response) {
            if (response.success && response.validation_passed) {
                alert('Validasi berhasil! Melanjutkan ke proses data upload...');
                callDataUploadProcess();
            } else {
                alert('Validasi gagal: ' + (response.message || 'Unknown error'));
                $('#btn-validate').prop('disabled', false).html('<i class="fal fa-check-circle"></i> Validasi & Lanjutkan');
            }
        },
        error: function() {
            alert('Error saat validasi files');
            $('#btn-validate').prop('disabled', false).html('<i class="fal fa-check-circle"></i> Validasi & Lanjutkan');
        }
    });
}

function callDataUploadProcess() {
    // AJAX untuk proses data upload
    $.ajax({
        url: '{{ site_url("rekon/step1/process") }}',
        method: 'POST',
        data: {
            tanggal_rekon: '{{ $tanggalRekon }}',
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        },
        success: function(response) {
            if (response.success) {
                alert('Proses data upload berhasil! Mengarahkan ke halaman selanjutnya...');
                setTimeout(() => {
                    window.location.href = '{{ site_url("rekon/step2") }}';
                }, 2000);
            } else {
                alert('Gagal proses data upload: ' + (response.message || 'Unknown error'));
                $('#btn-validate').prop('disabled', false).html('<i class="fal fa-check-circle"></i> Validasi & Lanjutkan');
            }
        },
        error: function() {
            alert('Error saat proses data upload');
            $('#btn-validate').prop('disabled', false).html('<i class="fal fa-check-circle"></i> Validasi & Lanjutkan');
        }
    });
}
</script>
@endpush
