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
                    <br><span class="text-info"><strong>Format:</strong> .txt dengan delimiter ;</span>
                </p>
                <form id="form-agn-detail" enctype="multipart/form-data">
                    <input type="hidden" name="{{ csrf_token() }}" value="{{ csrf_hash() }}" />
                    <input type="hidden" name="tanggal_rekon" value="{{ $tanggalRekon }}" />
                    <input type="hidden" name="file_type" value="agn_detail" />
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="file-agn-detail" name="file" accept=".txt" required>
                        <label class="custom-file-label" for="file-agn-detail">Pilih file .txt...</label>
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
                    <br><span class="text-info"><strong>Format:</strong> .txt dengan delimiter ;</span>
                </p>
                <form id="form-settle-edu" enctype="multipart/form-data">
                    <input type="hidden" name="{{ csrf_token() }}" value="{{ csrf_hash() }}" />
                    <input type="hidden" name="tanggal_rekon" value="{{ $tanggalRekon }}" />
                    <input type="hidden" name="file_type" value="settle_edu" />
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="file-settle-edu" name="file" accept=".txt" required>
                        <label class="custom-file-label" for="file-settle-edu">Pilih file .txt...</label>
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
                    <br><span class="text-info"><strong>Format:</strong> .txt dengan delimiter |</span>
                </p>    
                <form id="form-settle-pajak" enctype="multipart/form-data">
                    <input type="hidden" name="{{ csrf_token() }}" value="{{ csrf_hash() }}" />
                    <input type="hidden" name="tanggal_rekon" value="{{ $tanggalRekon }}" />
                    <input type="hidden" name="file_type" value="settle_pajak" />
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="file-settle-pajak" name="file" accept=".txt" required>
                        <label class="custom-file-label" for="file-settle-pajak">Pilih file .txt...</label>
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
                    <br><span class="text-info"><strong>Format:</strong> .csv dengan delimiter ;</span>
                </p>
                <form id="form-mgate" enctype="multipart/form-data">
                    <input type="hidden" name="{{ csrf_token() }}" value="{{ csrf_hash() }}" />
                    <input type="hidden" name="tanggal_rekon" value="{{ $tanggalRekon }}" />
                    <input type="hidden" name="file_type" value="mgate" />
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="file-mgate" name="file" accept=".csv" required>
                        <label class="custom-file-label" for="file-mgate">Pilih file .csv...</label>
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

/* Modal styling */
.modal-lg {
    max-width: 90%;
}

.table-responsive {
    max-height: 400px;
    overflow-y: auto;
}

.badge-warning {
    background-color: #ffc107;
    color: #212529;
}

/* Alert styling */
.alert {
    border-radius: 0.375rem;
}

.alert-warning {
    background-color: #fff3cd;
    border-color: #ffecb5;
    color: #856404;
}

.alert-info {
    background-color: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
}
</style>
@endpush

@push('scripts')
<script>
// Variabel global untuk tracking upload
let uploadedFiles = [];
const requiredFiles = ['agn_detail', 'settle_edu', 'settle_pajak', 'mgate'];

// Fungsi untuk refresh CSRF token
function refreshCSRFToken() {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: '{{ site_url("rekon/get-csrf-token") }}',
            method: 'GET',
            success: function(response) {
                if (response.success && response.csrf_hash) {
                    // Update semua CSRF input di form
                    $('input[name="{{ csrf_token() }}"]').val(response.csrf_hash);
                    console.log('CSRF token refreshed:', response.csrf_hash);
                    resolve(response.csrf_hash);
                } else {
                    console.warn('Invalid CSRF refresh response');
                    reject('Invalid response');
                }
            },
            error: function(xhr, status, error) {
                console.warn('Failed to refresh CSRF token:', error);
                reject(error);
            }
        });
    });
}

$(document).ready(function() {
    
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
        
        // Cek format file berdasarkan input ID
        const fileName = file.name.toLowerCase();
        const inputId = $(input).attr('id');
        let isValidType = false;
        let errorMessage = '';
        
        if (inputId === 'file-mgate') {
            // M-Gate harus CSV
            isValidType = fileName.endsWith('.csv');
            errorMessage = 'Format file tidak valid! M-Gate harus file CSV (.csv)';
        } else {
            // Yang lain harus TXT
            isValidType = fileName.endsWith('.txt');
            errorMessage = 'Format file tidak valid! File harus berformat TXT (.txt)';
        }
        
        if (!isValidType) {
            alert(errorMessage);
            $(input).val('');
            return false;
        }
        
        return true;
    }
});

function uploadFile(type) {
    console.log('Uploading file for type:', type);
    
    // Map file type to correct form and input IDs
    var formIdMap = {
        'agn_detail': 'form-agn-detail',
        'settle_edu': 'form-settle-edu',
        'settle_pajak': 'form-settle-pajak',
        'mgate': 'form-mgate'
    };
    
    var fileInputIdMap = {
        'agn_detail': 'file-agn-detail',
        'settle_edu': 'file-settle-edu',
        'settle_pajak': 'file-settle-pajak',
        'mgate': 'file-mgate'
    };
    
    var formId = '#' + formIdMap[type];
    var fileInputId = '#' + fileInputIdMap[type];
    
    console.log('Form ID:', formId);
    console.log('File Input ID:', fileInputId);
    
    var fileInput = $(fileInputId)[0];
    
    if (!fileInput) {
        alert('Form tidak ditemukan untuk tipe: ' + type);
        console.error('File input not found:', fileInputId);
        return;
    }
    
    if (!fileInput.files[0]) {
        alert('Pilih file terlebih dahulu');
        return;
    }
    
    // Show loading
    var btn = $(formId).find('button');
    var originalHtml = btn.html();
    btn.prop('disabled', true).html('<i class="fal fa-spinner fa-spin"></i> Uploading...');
    
    // Refresh CSRF token sebelum upload
    refreshCSRFToken().then(function() {
        // Ambil data form dengan CSRF token yang fresh
        var formData = new FormData($(formId)[0]);
        
        console.log('Form data prepared with fresh CSRF token');
        
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
                            <small>${response.message || 'Upload selesai dengan sukses'}</small>
                        </div>
                        <div class="text-center">
                            <button class="btn btn-outline-success btn-sm" onclick="reuploadFile('${type}')">
                                <i class="fal fa-redo"></i> Upload Ulang
                            </button>
                        </div>
                    `);

                    toastr['success']('File berhasil diupload dan divalidasi!');

                    // Tambahkan ke daftar uploaded files
                    if (!uploadedFiles.includes(type)) {
                        uploadedFiles.push(type);
                    }
                    
                    // Update status upload
                    updateUploadStatus();
                } else {
                    var errorMsg = response.message || 'Terjadi kesalahan saat upload file';
                    
                    // Tampilkan detail error jika ada
                    if (response.errors && response.errors.length > 0) {
                        errorMsg += '\n\nDetail error:\n' + response.errors.join('\n');
                    }
                    
                    // Tampilkan debug info jika ada (untuk development)
                    if (response.debug_info) {
                        errorMsg += '\n\nDebug Info:\nFile: ' + response.debug_info.file + '\nLine: ' + response.debug_info.line;
                    }
                    
                    alert('Upload gagal: ' + errorMsg);
                    btn.prop('disabled', false).html(originalHtml);
                }
            },
            error: function(xhr, status, error) {
                var errorMsg = 'Terjadi kesalahan saat upload file';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    
                    // Tampilkan debug info jika ada
                    if (xhr.responseJSON.debug_info) {
                        errorMsg += '\n\nDebug Info:\nFile: ' + xhr.responseJSON.debug_info.file + '\nLine: ' + xhr.responseJSON.debug_info.line;
                    }
                    
                    // Tampilkan error detail jika ada
                    if (xhr.responseJSON.errors && xhr.responseJSON.errors.length > 0) {
                        errorMsg += '\n\nDetail error:\n' + xhr.responseJSON.errors.join('\n');
                    }
                } else if (xhr.status === 419) {
                    errorMsg = 'CSRF token expired. Silakan refresh halaman dan coba lagi.';
                } else {
                    errorMsg += '\n\nHTTP Status: ' + xhr.status + '\nError: ' + error;
                }
                
                alert('Upload gagal: ' + errorMsg);
                btn.prop('disabled', false).html(originalHtml);
            }
        });
    }).catch(function(error) {
        console.error('Failed to refresh CSRF token:', error);
        alert('Gagal refresh CSRF token. Silakan refresh halaman dan coba lagi.');
        btn.prop('disabled', false).html(originalHtml);
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
    
    // Disable button dan show loading
    $('#btn-validate').prop('disabled', true).html('<i class="fal fa-spinner fa-spin"></i> Memvalidasi...');
    
    // Refresh CSRF token sebelum proses dengan async/await pattern
    refreshCSRFToken().then(function() {
        // Langsung panggil proses data upload (skip validasi terpisah)
        callDataUploadProcess();
    }).catch(function(error) {
        console.error('Failed to refresh CSRF token:', error);
        alert('Gagal refresh CSRF token. Silakan refresh halaman dan coba lagi.');
        $('#btn-validate').prop('disabled', false).html('<i class="fal fa-check-circle"></i> Validasi & Lanjutkan');
    });
}

function callDataUploadProcess() {
    // Pastikan CSRF token fresh
    refreshCSRFToken();
    
    // Update button text
    $('#btn-validate').html('<i class="fal fa-spinner fa-spin"></i> Memproses Data...');
    
    // AJAX untuk proses data upload dengan stored procedure
    $.ajax({
        url: '{{ site_url("rekon/step1/process") }}',
        method: 'POST',
        data: {
            tanggal_rekon: '{{ $tanggalRekon }}',
            '{{ csrf_token() }}': $('input[name="{{ csrf_token() }}"]').val()
        },
        timeout: 300000, // 5 menit timeout untuk proses yang lama
        success: function(response) {
            // Refresh CSRF token setelah response
            refreshCSRFToken();
            
            if (response.success) {
                alert('Proses penyimpanan data berhasil! Data telah disimpan ke database.');
                
                // Update button ke status sukses
                $('#btn-validate').removeClass('btn-success').addClass('btn-primary')
                    .prop('disabled', false)
                    .html('<i class="fal fa-check-circle"></i> Proses Selesai');
                
                toastr["success"](response.message);
                
                // Redirect ke step 2 untuk validasi mapping
                if (response.redirect) {
                    setTimeout(() => {
                        window.location.href = response.redirect;
                    }, 2000);
                } else {
                    setTimeout(() => {
                        window.location.href = '{{ base_url("rekon/step2") }}?tanggal={{ $tanggalRekon }}';
                    }, 2000);
                }
                
            } else {
                var errorMsg = 'Proses penyimpanan data gagal';
                if (response.message) {
                    errorMsg = response.message;
                }
                if (response.errors && response.errors.length > 0) {
                    errorMsg += '\n\nDetail error:\n' + response.errors.join('\n');
                }
                alert(errorMsg);
                $('#btn-validate').prop('disabled', false).html('<i class="fal fa-check-circle"></i> Validasi & Lanjutkan');
            }
        },
        error: function(xhr) {
            // Refresh CSRF token setelah error
            refreshCSRFToken();
            
            var errorMsg = 'Terjadi kesalahan saat memproses data';
            
            if (xhr.responseJSON) {
                if (xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                // Debug info untuk development
                if (xhr.responseJSON.debug_info) {
                    console.error('Debug Info:', xhr.responseJSON.debug_info);
                }
            } else if (xhr.status === 419) {
                errorMsg = 'CSRF token expired. Silakan refresh halaman dan coba lagi.';
            } else {
                errorMsg += '\n\nHTTP Status: ' + xhr.status + '\nError: ' + xhr.statusText;
            }
            
            alert(errorMsg);
            $('#btn-validate').prop('disabled', false).html('<i class="fal fa-check-circle"></i> Validasi & Lanjutkan');
        }
    });
}

// Step 1 - Simple initialization for file upload and processing only
$(document).ready(function() {
    console.log('Step 1 - File Upload & Processing initialized');
});
</script>
@endpush

