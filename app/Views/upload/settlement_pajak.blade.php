@extends('layouts.app')

@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        <i class="fal fa-cloud-upload-alt"></i> {{ $title }}
        <small>Upload file settlement pajak untuk proses rekonsiliasi</small>
    </h1>
</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fal fa-file-excel text-success"></i>
                    Upload File Settlement Pajak
                </h3>
            </div>
            <div class="card-body">
                <form action="{{ site_url('upload/process') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                    <input type="hidden" name="file_type" value="settlement_pajak" />
                    
                    <div class="form-group">
                        <label for="file_upload" class="form-label">Pilih File Settlement Pajak <span class="text-danger">*</span></label>
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

                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary" id="btnUpload">
                            <i class="fal fa-upload"></i> Upload File
                        </button>
                        <a href="{{ site_url('upload/agregator') }}" class="btn btn-secondary ml-2">
                            <i class="fal fa-arrow-left"></i> Kembali
                        </a>
                        <a href="{{ site_url('upload/settlement-edu') }}" class="btn btn-outline-primary ml-2">
                            <i class="fal fa-arrow-right"></i> Lanjut ke Settlement Edu
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
            <div class="card-header bg-warning-50">
                <h5 class="card-title text-warning">
                    <i class="fal fa-receipt"></i> Format File Settlement Pajak
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">File settlement pajak berisi data transaksi pajak yang telah di-settle oleh sistem pajak.</p>
                
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="bg-primary-50">
                            <tr>
                                <th>Kolom</th>
                                <th>Deskripsi</th>
                                <th>Format</th>
                                <th>Contoh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>trx_date</code></td>
                                <td>Tanggal transaksi pajak</td>
                                <td>YYYY-MM-DD</td>
                                <td>2025-01-20</td>
                            </tr>
                            <tr>
                                <td><code>tax_ref_no</code></td>
                                <td>Nomor referensi pajak</td>
                                <td>Text</td>
                                <td>TAX2025012012345</td>
                            </tr>
                            <tr>
                                <td><code>settlement_amount</code></td>
                                <td>Nominal settlement</td>
                                <td>Numeric</td>
                                <td>100000.00</td>
                            </tr>
                            <tr>
                                <td><code>tax_type</code></td>
                                <td>Jenis pajak</td>
                                <td>Text</td>
                                <td>PPh, PPN, PPnBM</td>
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
    
    // Form upload with progress (same as agregator)
    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();
        
        var fileInput = $('#file_upload')[0];
        if (!fileInput.files.length) {
            toastr.error('Pilih file yang akan diupload');
            return;
        }
        
        var formData = new FormData(this);
        var submitBtn = $('#btnUpload');
        var progressBar = $('#uploadProgress');
        var progressBarInner = progressBar.find('.progress-bar');
        var progressText = progressBar.find('.progress-text');
        
        progressBar.show();
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fal fa-spinner fa-spin"></i> Mengupload...');
        
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
                toastr.success('File settlement pajak berhasil diupload');
                setTimeout(function() {
                    window.location.href = "{{ site_url('upload/settlement-edu') }}";
                }, 1500);
            },
            error: function(xhr) {
                toastr.error('Terjadi kesalahan saat upload file');
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
