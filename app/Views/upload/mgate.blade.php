@extends('layouts.app')

@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        <i class="fal fa-cloud-upload-alt"></i> {{ $title }}
        <small>Upload file MGate untuk proses rekonsiliasi</small>
    </h1>
</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fal fa-file-excel text-success"></i>
                    Upload File MGate
                </h3>
            </div>
            <div class="card-body">
                <form action="{{ site_url('upload/process') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                    <input type="hidden" name="file_type" value="mgate" />
                    
                    <div class="form-group">
                        <label for="file_upload" class="form-label">Pilih File MGate <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" 
                                   class="custom-file-input" 
                                   id="file_upload" 
                                   name="file_upload"
                                   accept=".xls,.xlsx,.csv"
                                   required>
                            <label class="custom-file-label" for="file_upload">Pilih file...</label>
                        </div>
                    </div>

                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary" id="btnUpload">
                            <i class="fal fa-upload"></i> Upload File
                        </button>
                        <a href="{{ site_url('upload/settlement-edu') }}" class="btn btn-secondary ml-2">
                            <i class="fal fa-arrow-left"></i> Kembali
                        </a>
                        <button type="button" class="btn btn-success ml-2" id="btnFinish">
                            <i class="fal fa-check"></i> Selesai & Proses Rekonsiliasi
                        </button>
                    </div>
                </form>

                <div class="progress mt-3" id="uploadProgress" style="display: none;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">
                        <span class="progress-text">0%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Card -->
        <div class="card mt-4">
            <div class="card-header bg-success-50">
                <h5 class="card-title text-success">
                    <i class="fal fa-check-circle"></i> Ringkasan Upload Files
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <i class="fal fa-file-excel text-success mr-2"></i> Data Agregator
                                <span class="badge badge-success">✓ Uploaded</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <i class="fal fa-receipt text-warning mr-2"></i> Settlement Pajak  
                                <span class="badge badge-success">✓ Uploaded</span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <i class="fal fa-graduation-cap text-info mr-2"></i> Settlement Edu
                                <span class="badge badge-success">✓ Uploaded</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <i class="fal fa-gateway text-primary mr-2"></i> MGate
                                <span class="badge badge-secondary" id="mgateStatus">⏳ Pending</span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <hr>
                <p class="text-muted small mb-0">
                    <i class="fal fa-info-circle"></i> 
                    Setelah semua file berhasil diupload, sistem akan memulai proses rekonsiliasi otomatis.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().replace('C:\\fakepath\\', '');
        $(this).siblings('.custom-file-label').html(fileName);
    });
    
    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        var submitBtn = $('#btnUpload');
        var progressBar = $('#uploadProgress');
        
        progressBar.show();
        submitBtn.prop('disabled', true).html('<i class="fal fa-spinner fa-spin"></i> Mengupload...');
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function() {
                toastr.success('File MGate berhasil diupload');
                $('#mgateStatus').removeClass('badge-secondary').addClass('badge-success').html('✓ Uploaded');
                $('#btnFinish').show();
            },
            error: function() {
                toastr.error('Terjadi kesalahan saat upload file');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fal fa-upload"></i> Upload File');
                progressBar.hide();
            }
        });
    });
    
    $('#btnFinish').on('click', function() {
        swalr.fire({
            title: 'Mulai Proses Rekonsiliasi?',
            text: 'Semua file telah diupload. Sistem akan memulai proses rekonsiliasi otomatis.',
            icon: 'question',
            confirmButtonText: 'Ya, Mulai Proses',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to reconciliation process or dashboard
                window.location.href = "{{ site_url('dashboard') }}";
                toastr.success('Proses rekonsiliasi telah dimulai');
            }
        });
    });
});
</script>
@endpush
