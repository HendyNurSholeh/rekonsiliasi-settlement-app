@extends('layouts.app')

@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        <i class="fal fa-cloud-upload-alt"></i> {{ $title }}
        <small>Upload file settlement education untuk proses rekonsiliasi</small>
    </h1>
</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fal fa-file-excel text-success"></i>
                    Upload File Settlement Education
                </h3>
            </div>
            <div class="card-body">
                <form action="{{ site_url('upload/process') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                    <input type="hidden" name="file_type" value="settlement_edu" />
                    
                    <div class="form-group">
                        <label for="file_upload" class="form-label">Pilih File Settlement Edu <span class="text-danger">*</span></label>
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
                        <a href="{{ site_url('upload/settlement-pajak') }}" class="btn btn-secondary ml-2">
                            <i class="fal fa-arrow-left"></i> Kembali
                        </a>
                        <a href="{{ site_url('upload/mgate') }}" class="btn btn-outline-primary ml-2">
                            <i class="fal fa-arrow-right"></i> Lanjut ke MGate
                        </a>
                    </div>
                </form>

                <div class="progress mt-3" id="uploadProgress" style="display: none;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">
                        <span class="progress-text">0%</span>
                    </div>
                </div>
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
                toastr.success('File settlement edu berhasil diupload');
                setTimeout(function() {
                    window.location.href = "{{ site_url('upload/mgate') }}";
                }, 1500);
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
});
</script>
@endpush
