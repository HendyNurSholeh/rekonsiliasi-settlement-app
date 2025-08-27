// Variabel global untuk tracking upload
let uploadedFiles = [];
let isUploading = false; // Flag untuk mencegah multiple upload bersamaan
const requiredFiles = ['agn_detail', 'settle_edu', 'settle_pajak', 'mgate'];

// Fungsi untuk refresh CSRF token
function refreshCSRFToken() {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: window.appConfig.baseUrl + "get-csrf-token",
            method: 'GET',
            success: function(response) {
                if (response.csrf_token) {
                    // Update semua CSRF input di form
                    $('input[name="' + window.appConfig.csrfToken + '"]').val(response.csrf_token);
                    console.log('CSRF token refreshed:', response.csrf_token);
                    resolve(response.csrf_token);
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
    
    // Check jika sedang ada upload lain yang berjalan
    if (isUploading) {
        alert('Ada upload lain yang sedang berjalan. Harap tunggu hingga selesai.');
        return;
    }
    
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
    
    // Set flag uploading dan disable semua upload cards
    isUploading = true;
    disableAllUploadCards(type);
    
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
            url: window.appConfig.baseUrl + "rekon/step1/upload",
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
                    
                    toastr['success'](response.message);

                    // Tambahkan ke daftar uploaded files
                    if (!uploadedFiles.includes(type)) {
                        uploadedFiles.push(type);
                    }
                    
                    // Update status upload
                    updateUploadStatus();
                    
                    // Reset flag uploading dan enable kembali semua cards
                    isUploading = false;
                    enableAllUploadCards();
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
                    
                    // Reset flag uploading dan enable kembali semua cards
                    isUploading = false;
                    enableAllUploadCards();
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
                
                // Reset flag uploading dan enable kembali semua cards
                isUploading = false;
                enableAllUploadCards();
            }
        });
    }).catch(function(error) {
        console.error('Failed to refresh CSRF token:', error);
        alert('Gagal refresh CSRF token. Silakan refresh halaman dan coba lagi.');
        btn.prop('disabled', false).html(originalHtml);
        
        // Reset flag uploading dan enable kembali semua cards
        isUploading = false;
        enableAllUploadCards();
    });
}

// Fungsi untuk disable semua upload cards kecuali yang sedang diupload
function disableAllUploadCards(currentType) {
    const cardTypes = ['agn_detail', 'settle_edu', 'settle_pajak', 'mgate'];
    
    cardTypes.forEach(function(type) {
        if (type !== currentType) {
            // Disable file input dan button
            $('#file-' + type.replace('_', '-')).prop('disabled', true);
            $('#form-' + type.replace('_', '-')).find('button').prop('disabled', true);
            
            // Add opacity untuk visual feedback
            $('#form-' + type.replace('_', '-')).closest('.card').addClass('upload-disabled');
        }
    });
    
    // Tambahkan overlay message
    if (!$('.upload-overlay').length) {
        $('body').append(`
            <div class="upload-overlay">
                <div class="upload-overlay-content">
                    <i class="fal fa-upload fa-2x text-primary mb-2"></i>
                    <h5>Upload Sedang Berlangsung</h5>
                    <p>Harap tunggu hingga upload selesai.</p>
                </div>
            </div>
        `);
    }
}

// Fungsi untuk enable kembali semua upload cards
function enableAllUploadCards() {
    const cardTypes = ['agn_detail', 'settle_edu', 'settle_pajak', 'mgate'];
    
    cardTypes.forEach(function(type) {
        // Enable file input dan button (kecuali yang sudah diupload)
        if (!uploadedFiles.includes(type)) {
            $('#file-' + type.replace('_', '-')).prop('disabled', false);
            $('#form-' + type.replace('_', '-')).find('button').prop('disabled', false);
        }
        
        // Remove opacity
        $('#form-' + type.replace('_', '-')).closest('.card').removeClass('upload-disabled');
    });
    
    // Remove overlay
    $('.upload-overlay').remove();
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
    // Konfirmasi dihapus untuk mempercepat workflow
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
        url: window.appConfig.baseUrl + "rekon/step1/process",
        method: 'POST',
        data: {
            tanggal_rekon: window.appConfig.tanggalData,
            [window.appConfig.csrfToken]: $('input[name="' + window.appConfig.csrfToken + '"]').val()
        },
        success: function(response) {
            // Refresh CSRF token setelah response
            refreshCSRFToken();
            
            if (response.success) {
                // Update button ke status sukses
                toastr['success'](response.message || 'Data berhasil diproses');
                // Ganti tombol dengan tombol "Lanjut ke Step 2"
                $('#btn-validate').replaceWith(`
                    <a href="${response.redirect}" class="btn btn-success" id="btn-next-step">
                        <i class="fal fa-arrow-right"></i> Lanjut ke Step 2
                    </a>
                `);
                
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