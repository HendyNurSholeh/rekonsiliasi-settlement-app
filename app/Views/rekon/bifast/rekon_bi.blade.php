@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="<?= base_url('css/rekon/process/direct_jurnal_rekap.css') ?>">
@endpush

@section('content')

<div class="subheader">
    <h1 class="subheader-title">
        <i class="fal fa-file-invoice"></i> <?= esc($title) ?>
        <small>Rekap transaksi direct jurnal </small>
    </h1>
</div>
<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fal fa-info-circle"></i>
            <strong>Rekap Tx Direct Jurnal</strong> 
            <br>Menampilkan rekap transaksi yang memerlukan direct jurnal dari sistem.
        </div>
    </div>
</div>

<!-- XLSX Upload Form (AJAX) -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card h-100 d-flex flex-column justify-content-center align-items-center p-4" style="min-height: 250px;">
            <form id="upload-xlsx-form-1" class="w-100 h-100 d-flex flex-column justify-content-center align-items-center" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <label for="xlsx_files_1" class="w-100 h-100 d-flex flex-column justify-content-center align-items-center border border-primary rounded drag-upload-area" style="cursor:pointer; min-height:180px; background:#f8f9fa;">
                    <i class="fal fa-cloud-upload fa-3x mb-2 text-primary"></i>
                    <span class="mb-2">Drag & drop or click to upload XLSX (multiple allowed)</span>
                    <input type="file" name="xlsx_files[]" id="xlsx_files_1" class="form-control-file d-none" accept=".xlsx" multiple required>
                    <span id="selected-files-1" class="text-secondary small mt-2"></span>
                </label>
                <button type="submit" class="btn btn-primary mt-3" id="upload-btn-1">Upload & Import</button>
                <span id="uploading-indicator-1" class="ml-2" style="display:none;">
                    <i class="fa fa-spinner fa-spin"></i> Uploading...
                </span>
            </form>
            <div id="upload-result-1" class="alert alert-info mt-3 w-100" style="display:none"></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100 d-flex flex-column justify-content-center align-items-center p-4" style="min-height: 250px;">
            <form id="upload-xlsx-form-2" class="w-100 h-100 d-flex flex-column justify-content-center align-items-center" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <label for="xlsx_files_2" class="w-100 h-100 d-flex flex-column justify-content-center align-items-center border border-primary rounded drag-upload-area" style="cursor:pointer; min-height:180px; background:#f8f9fa;">
                    <i class="fal fa-cloud-upload fa-3x mb-2 text-primary"></i>
                    <span class="mb-2">Drag & drop or click to upload XLSX (multiple allowed)</span>
                    <input type="file" name="xlsx_files[]" id="xlsx_files_2" class="form-control-file d-none" accept=".xlsx" multiple required>
                    <span id="selected-files-2" class="text-secondary small mt-2"></span>
                </label>
                <button type="submit" class="btn btn-primary mt-3" id="upload-btn-2">Upload & Import</button>
                <span id="uploading-indicator-2" class="ml-2" style="display:none;">
                    <i class="fa fa-spinner fa-spin"></i> Uploading...
                </span>
            </form>
            <div id="upload-result-2" class="alert alert-info mt-3 w-100" style="display:none"></div>
        </div>
    </div>
</div>

@push('styles')
<style>
.drag-upload-area {
    transition: border-color 0.2s, background 0.2s;
}
.drag-upload-area.dragover {
    border-color: #007bff !important;
    background: #e3f2fd !important;
}
</style>
@endpush

@push('scripts')
<script>
function setupDragAndDrop(formId, inputId, resultId, btnId, indicatorId, selectedFilesId) {
    var $form = $('#' + formId);
    var $input = $('#' + inputId);
    var $area = $form.find('.drag-upload-area');
    var $result = $('#' + resultId);
    var $btn = $('#' + btnId);
    var $indicator = $('#' + indicatorId);
    var $selectedFiles = $('#' + selectedFilesId);

    $area.on('click', function(e) {
        $input.trigger('click');
    });

    $area.on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $area.addClass('dragover');
    });

    $area.on('dragleave drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $area.removeClass('dragover');
    });

    $area.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var files = e.originalEvent.dataTransfer.files;
        $input[0].files = files;
        showSelectedFiles($input, $selectedFiles);
    });

    $input.on('change', function() {
        showSelectedFiles($input, $selectedFiles);
    });

    $form.on('submit', function(e) {
        e.preventDefault();
        var data = new FormData(this);
        $result.hide().html('');
        $btn.prop('disabled', true);
        $indicator.show();

        // Always get the latest CSRF token value before each upload
        var csrfTokenName = $form.find('input[type="hidden"][name^="csrf_"]').attr('name');
        var csrfTokenVal = $form.find('input[type="hidden"][name^="csrf_"]').val();

        if (csrfTokenName && csrfTokenVal) {
            data.set(csrfTokenName, csrfTokenVal);
        }

        $.ajax({
            url: "<?= route_to('rekon-bifast.upload') ?>",
            type: 'POST',
            data: data,
            processData: false,
            contentType: false,
            success: function(resp) {
                if (resp && resp.messages) {
                    $result.html(resp.messages.join('<br>')).show();
                } else {
                    $result.html('Unexpected response.').show();
                }
                $input.val('');
                $selectedFiles.text('');
                refreshCsrfToken();
            },
            error: function(xhr) {
                $result.html('Upload failed.').show();
                $input.val('');
                $selectedFiles.text('');
                refreshCsrfToken();
            },
            complete: function() {
                $btn.prop('disabled', false);
                $indicator.hide();
            }
        });
    });
}

function showSelectedFiles($input, $selectedFiles) {
    var files = $input[0].files;
    if (files && files.length > 0) {
        var names = [];
        for (var i = 0; i < files.length; i++) {
            names.push(files[i].name);
        }
        $selectedFiles.text(names.join(', '));
    } else {
        $selectedFiles.text('');
    }
}

$(function() {
    setupDragAndDrop('upload-xlsx-form-1', 'xlsx_files_1', 'upload-result-1', 'upload-btn-1', 'uploading-indicator-1', 'selected-files-1');
    setupDragAndDrop('upload-xlsx-form-2', 'xlsx_files_2', 'upload-result-2', 'upload-btn-2', 'uploading-indicator-2', 'selected-files-2');
});
</script>
@endpush



@endsection

@push('scripts')
<script>
function refreshCsrfToken(callback) {
    $.get("<?= base_url('get-new-csrf-token') ?>", function(resp) {
        if (resp && resp.csrf_token && resp.csrf_hash) {
            var $csrf = $('input[type="hidden"][name^="csrf_"]');
            $csrf.attr('name', resp.csrf_token);
            $csrf.val(resp.csrf_hash);
        }
        if (typeof callback === 'function') callback();
    });
}

$(function() {
    $('#upload-xlsx-form').on('submit', function(e) {
        e.preventDefault();
        var form = this;
        var data = new FormData(form);
        var $result = $('#upload-result');
        var $btn = $('#upload-btn');
        var $indicator = $('#uploading-indicator');
        $result.hide().html('');
        $btn.prop('disabled', true);
        $indicator.show();

        // Always get the latest CSRF token value before each upload
        var csrfTokenName = $('input[type="hidden"][name^="csrf_"]').attr('name');
        var csrfTokenVal = $('input[type="hidden"][name^="csrf_"]').val();

        // Add the CSRF token to the FormData (required for CI4 AJAX POST)
        if (csrfTokenName && csrfTokenVal) {
            data.set(csrfTokenName, csrfTokenVal);
        }

        $.ajax({
            url: "<?= route_to('rekon-bifast.upload') ?>",
            type: 'POST',
            data: data,
            processData: false,
            contentType: false,
            success: function(resp) {
                if (resp && resp.messages) {
                    $result.html(resp.messages.join('<br>')).show();
                } else {
                    $result.html('Unexpected response.').show();
                }
                $('#xlsx_files').val('');
                refreshCsrfToken();
            },
            error: function(xhr) {
                $result.html('Upload failed.').show();
                $('#xlsx_files').val('');
                refreshCsrfToken();
            },
            complete: function() {
                $btn.prop('disabled', false);
                $indicator.hide();
            }
        });
    });
});

function resetFilters() {
    // Remove 'tanggal' parameter from URL and redirect
    const url = new URL(window.location);
    url.searchParams.delete('tanggal');
    window.location.href = url.pathname + url.search;
}
</script>
@endpush


