@extends('layouts.app')

@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        <i class="fal fa-cloud-upload-alt"></i> {{ $title }}
        <small>Upload file data agregator untuk proses rekonsiliasi</small>
    </h1>
</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fal fa-file-excel text-success"></i>
                    Upload Data Agregator
                </h3>
            </div>
            <div class="card-body">
                <form action="{{ site_url('upload/process') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                    <input type="hidden" name="file_type" value="agregator" />
                    
                    <div class="form-group">
                        <label for="file_upload" class="form-label">Pilih File Data Agregator <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" 
                                   class="custom-file-input" 
                                   id="file_upload" 
                                   name="file_upload"
                                   accept=".xls,.xlsx,.csv"
                                   required>
                            <label class="custom-file-label" for="file_upload">Pilih file...</label>
                        </div>
                        <div class="help-block">
                            Format file yang didukung: Excel (.xls, .xlsx) dan CSV (.csv). Maksimal ukuran file: 10MB
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="validate_data" name="validate_data" checked>
                            <label class="custom-control-label" for="validate_data">Validasi data otomatis</label>
                        </div>
                        <div class="help-block">
                            Sistem akan memvalidasi format dan kelengkapan data secara otomatis
                        </div>
                    </div>

                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary" id="btnUpload">
                            <i class="fal fa-upload"></i> Upload File
                        </button>
                        <a href="{{ site_url('rekon/process') }}" class="btn btn-secondary ml-2">
                            <i class="fal fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>

                <!-- Progress bar -->
                <div class="progress mt-3" id="uploadProgress" style="display: none;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">
                        <span class="progress-text">0%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- File Format Info -->
        <div class="card mt-4">
            <div class="card-header bg-info-50">
                <h5 class="card-title text-info">
                    <i class="fal fa-info-circle"></i> Format File Data Agregator
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="bg-primary-50">
                            <tr>
                                <th>Kolom</th>
                                <th>Deskripsi</th>
                                <th>Format</th>
                                <th>Wajib</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>tanggal</code></td>
                                <td>Tanggal transaksi</td>
                                <td>YYYY-MM-DD</td>
                                <td><span class="badge badge-danger">Ya</span></td>
                            </tr>
                            <tr>
                                <td><code>reference_no</code></td>
                                <td>Nomor referensi</td>
                                <td>Text</td>
                                <td><span class="badge badge-danger">Ya</span></td>
                            </tr>
                            <tr>
                                <td><code>amount</code></td>
                                <td>Nominal transaksi</td>
                                <td>Numeric</td>
                                <td><span class="badge badge-danger">Ya</span></td>
                            </tr>
                            <tr>
                                <td><code>description</code></td>
                                <td>Keterangan transaksi</td>
                                <td>Text</td>
                                <td><span class="badge badge-secondary">Tidak</span></td>
                            </tr>
                            <tr>
                                <td><code>account_no</code></td>
                                <td>Nomor rekening</td>
                                <td>Text</td>
                                <td><span class="badge badge-warning">Kondisional</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Custom file input
    $('.custom-file-input').on('change', function(event) {
        var fileName = $(this).val().replace('C:\\fakepath\\', '');
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
    });
    
    // Form upload with progress
    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();
        
        var fileInput = $('#file_upload')[0];
        if (!fileInput.files.length) {
            toastr.error('Pilih file yang akan diupload');
            return;
        }
        
        var file = fileInput.files[0];
        var maxSize = 10 * 1024 * 1024; // 10MB
        
        if (file.size > maxSize) {
            toastr.error('Ukuran file terlalu besar. Maksimal 10MB');
            return;
        }
        
        var formData = new FormData(this);
        var submitBtn = $('#btnUpload');
        var progressBar = $('#uploadProgress');
        var progressBarInner = progressBar.find('.progress-bar');
        var progressText = progressBar.find('.progress-text');
        
        // Show progress bar
        progressBar.show();
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fal fa-spinner fa-spin"></i> Mengupload...');
        
        // AJAX upload
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                var xhr = new XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        var percent = Math.round((e.loaded / e.total) * 100);
                        progressBarInner.css('width', percent + '%');
                        progressText.text(percent + '%');
                    }
                });
                return xhr;
            },
            success: function(response) {
                toastr.success('File berhasil diupload');
                // Redirect to next step
                setTimeout(function() {
                    window.location.href = "{{ site_url('upload/settlement-pajak') }}";
                }, 1500);
            },
            error: function(xhr) {
                toastr.error('Terjadi kesalahan saat upload file');
                console.error(xhr.responseText);
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                submitBtn.html('<i class="fal fa-upload"></i> Upload File');
                progressBar.hide();
                progressBarInner.css('width', '0%');
                progressText.text('0%');
            }
        });
    });
});
</script>
@endpush
