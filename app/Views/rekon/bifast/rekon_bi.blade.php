@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="<?= base_url('css/rekon/process/direct_jurnal_rekap.css') ?>">
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

@section('content')

<div style="margin-bottom: 600px;">

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
                <div class="card-header w-100 text-center mb-2">
                    <strong>FILE CT BIFAST</strong>
                </div>
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

    <!-- Uploaded Files Table -->
    <div class="row mt-4">
        <div class="col-12">
            <form id="filter-date-form" class="form-inline mb-3">
                <label for="filter-date" class="mr-2">Tampilkan data tanggal:</label>
                <input type="date" id="filter-date" name="filter_date" class="form-control mr-2"
                    value="<?= date('Y-m-d') ?>">
                <button type="submit" class="btn btn-info btn-sm">Tampilkan</button>
            </form>
        </div>
    </div>
    <div class="row mb-4 mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <strong><i class="fal fa-table"></i> Daftar File XLSX yang Telah Diupload</strong>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive mb-0">
                        <table class="table table-bordered table-striped mb-0" id="uploaded-files-table">
    <thead class="thead-light">
        <tr>
            <th style="width:5%; cursor:pointer;" data-sort="no"># <span class="sort-indicator"></span></th>
            <th style="cursor:pointer;" data-sort="file_title">Nama File <span class="sort-indicator"></span></th>
            <th style="cursor:pointer;" data-sort="trx_date">Tanggal Upload <span class="sort-indicator"></span></th>
            <th style="cursor:pointer;" data-sort="core_type">Tipe <span class="sort-indicator"></span></th>
        </tr>
    </thead>
    <tbody>
        <!-- Will be filled by JS -->
    </tbody>
</table>
<div id="uploaded-files-pagination" class="mt-2 d-flex justify-content-center"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        </div>
        <div class="col-md-6">
            <div class="card h-100 d-flex flex-column justify-content-center align-items-center p-4" style="min-height: 250px;">
                <div class="card-header w-100 text-center mb-2">
                    <strong>FILE MEMBER STATEMENT</strong>
                </div>
                <form id="upload-csv-form" class="w-100 h-100 d-flex flex-column justify-content-center align-items-center" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <label for="csv_files" class="w-100 h-100 d-flex flex-column justify-content-center align-items-center border border-success rounded drag-upload-area" style="cursor:pointer; min-height:180px; background:#f8f9fa;">
                        <i class="fal fa-file-csv fa-3x mb-2 text-success"></i>
                        <span class="mb-2">Drag & drop or click to upload CSV (multiple allowed)</span>
                        <input type="file" name="xlsx_files_2[]" id="csv_files" class="form-control-file d-none" accept=".csv" multiple required>
                        <span id="selected-csv-files" class="text-secondary small mt-2"></span>
                    </label>
                    <button type="submit" class="btn btn-success mt-3" id="upload-csv-btn">Upload CSV</button>
                    <span id="uploading-csv-indicator" class="ml-2" style="display:none;">
                        <i class="fa fa-spinner fa-spin"></i> Uploading...
                    </span>
                </form>
                <div id="upload-csv-result" class="alert alert-info mt-3 w-100" style="display:none"></div>
            </div>

                <!-- CSV Uploaded Files Table -->
    <div class="row mt-4">
        <div class="col-12">
            <form id="filter-date-memstat-form" class="form-inline mb-3">
                <label for="filter-date-memstat" class="mr-2">Tampilkan data tanggal (CSV):</label>
                <input type="date" id="filter-date-memstat" name="filter_date_memstat" class="form-control mr-2"
                    value="<?= date('Y-m-d') ?>">
                <button type="submit" class="btn btn-info btn-sm">Tampilkan</button>
            </form>
        </div>
    </div>
    <div class="row mb-4 mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <strong><i class="fal fa-table"></i> Daftar File CSV yang Telah Diupload</strong>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive mb-0">
                        <table class="table table-bordered table-striped mb-0" id="uploaded-csv-files-table">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:5%; cursor:pointer;" data-sort="no"># <span class="sort-indicator"></span></th>
                                    <th style="cursor:pointer;" data-sort="file_name">Nama File <span class="sort-indicator"></span></th>
                                    <th style="cursor:pointer;" data-sort="time">Waktu <span class="sort-indicator"></span></th>
                                    <th style="cursor:pointer;" data-sort="unique_file">Unique Code <span class="sort-indicator"></span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Will be filled by JS -->
                            </tbody>
                        </table>
                        <div id="uploaded-csv-files-pagination" class="mt-2 d-flex justify-content-center"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        </div>

        
    </div>



</div>

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

    // Prevent multiple event bindings
    $area.off('mousedown').on('mousedown', function(e) {
        // Only trigger click if the actual label is clicked, not the input or its children
        if (e.target === $area[0]) {
            $input.trigger('click');
        }
    });

    // Remove the 'click' event binding to prevent double popup
    $area.off('click');

    $area.off('dragover').on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $area.addClass('dragover');
    });

    $area.off('dragleave drop').on('dragleave drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $area.removeClass('dragover');
    });

    $area.off('drop').on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var files = e.originalEvent.dataTransfer.files;
        $input[0].files = files;
        showSelectedFiles($input, $selectedFiles);
    });

    $input.off('change').on('change', function() {
        showSelectedFiles($input, $selectedFiles);
    });

    $form.off('submit').on('submit', function(e) {
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
                let errorMsg = 'Upload failed.';
                if (xhr && xhr.responseText) {
                    try {
                        let resp = JSON.parse(xhr.responseText);
                        if (resp && resp.messages && resp.messages.length) {
                            errorMsg += '<br>' + resp.messages.join('<br>');
                        }
                    } catch (e) {
                        // If not JSON, show raw response
                        errorMsg += '<br>' + $('<div>').text(xhr.responseText).html();
                    }
                }
                $result.html(errorMsg).show();
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

function setupCsvDragAndDrop() {
    var $form = $('#upload-csv-form');
    var $input = $('#csv_files');
    var $area = $form.find('.drag-upload-area');
    var $result = $('#upload-csv-result');
    var $btn = $('#upload-csv-btn');
    var $indicator = $('#uploading-csv-indicator');
    var $selectedFiles = $('#selected-csv-files');

    $area.off('mousedown').on('mousedown', function(e) {
        if (e.target === $area[0]) {
            $input.trigger('click');
        }
    });
    $area.off('click');
    $area.off('dragover').on('dragover', function(e) {
        e.preventDefault(); e.stopPropagation(); $area.addClass('dragover');
    });
    $area.off('dragleave drop').on('dragleave drop', function(e) {
        e.preventDefault(); e.stopPropagation(); $area.removeClass('dragover');
    });
    $area.off('drop').on('drop', function(e) {
        e.preventDefault(); e.stopPropagation();
        var files = e.originalEvent.dataTransfer.files;
        $input[0].files = files;
        showSelectedFiles($input, $selectedFiles);
    });
    $input.off('change').on('change', function() {
        showSelectedFiles($input, $selectedFiles);
    });
    $form.off('submit').on('submit', function(e) {
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
                let errorMsg = 'Upload failed.';
                if (xhr && xhr.responseText) {
                    try {
                        let resp = JSON.parse(xhr.responseText);
                        if (resp && resp.messages && resp.messages.length) {
                            errorMsg += '<br>' + resp.messages.join('<br>');
                        }
                    } catch (e) {
                        errorMsg += '<br>' + $('<div>').text(xhr.responseText).html();
                    }
                }
                $result.html(errorMsg).show();
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
    setupCsvDragAndDrop();
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

<div class="page-bottom-space"></div>

@push('scripts')
<script>
// Simple client-side pagination for uploaded files table
$(function() {
    var rowsPerPage = 10;
    var $table = $('#uploaded-files-table');
    var $tbody = $table.find('tbody');
    var $rows = $tbody.find('tr');
    var $pagination = $('#uploaded-files-pagination');

    function renderPagination() {
        var totalRows = $rows.length;
        var totalPages = Math.ceil(totalRows / rowsPerPage);
        $pagination.empty();
        if (totalPages <= 1) return;

        for (var i = 1; i <= totalPages; i++) {
            var btn = $('<button type="button" class="btn btn-sm btn-light mx-1"></button>');
            btn.text(i);
            btn.data('page', i);
            if (i === 1) btn.addClass('active');
            $pagination.append(btn);
        }
    }

    function showPage(page) {
        $rows.hide();
        var start = (page - 1) * rowsPerPage;
        var end = start + rowsPerPage;
        $rows.slice(start, end).show();
        $pagination.find('button').removeClass('active');
        $pagination.find('button').eq(page - 1).addClass('active');
    }

    $pagination.on('click', 'button', function() {
        var page = $(this).data('page');
        showPage(page);
    });

    // Initialize
    renderPagination();
    showPage(1);
});
</script>
@endpush

@push('scripts')
<script>
var uploadedFilesSort = { field: 'file_title', dir: 'desc' };
var uploadedFilesDate = (function() {
    // Default to today
    var d = new Date();
    return d.toISOString().slice(0, 10);
})();

function fetchUploadedFilesTable(page = 1) {
    var dateParam = uploadedFilesDate ? '&filter_date=' + encodeURIComponent(uploadedFilesDate) : '';
    $.get("<?= route_to('rekon-bifast.rekap') ?>?ajax=1&page=" + page + "&sort=" + uploadedFilesSort.field + "&dir=" + uploadedFilesSort.dir + dateParam, function(resp) {
        if (resp && resp.data) {
            var $tbody = $('#uploaded-files-table tbody');
            $tbody.empty();
            if (resp.data.length === 0) {
                $tbody.append('<tr><td colspan="4" class="text-center text-muted">Belum ada file yang diupload.</td></tr>');
            } else {
                $.each(resp.data, function(i, file) {
                    $tbody.append(
                        '<tr>' +
                        '<td>' + file.no + '</td>' +
                        '<td>' + $('<div>').text(file.file_title).html() + '</td>' +
                        '<td>' + $('<div>').text(file.trx_date).html() + '</td>' +
                        '<td>' + $('<div>').text(file.core_type).html() + '</td>' +
                        '</tr>'
                    );
                });
            }
            renderUploadedFilesPagination(resp.total, resp.perPage, resp.currentPage);
            updateSortIndicators();
        }
    });
}

function renderUploadedFilesPagination(total, perPage, currentPage) {
    var $pagination = $('#uploaded-files-pagination');
    $pagination.empty();
    var totalPages = Math.ceil(total / perPage);
    if (totalPages <= 1) return;
    for (var i = 1; i <= totalPages; i++) {
        var btn = $('<button type="button" class="btn btn-sm btn-light mx-1"></button>');
        btn.text(i);
        btn.data('page', i);
        if (i === currentPage) btn.addClass('active');
        $pagination.append(btn);
    }
    $pagination.off('click').on('click', 'button', function() {
        var page = $(this).data('page');
        fetchUploadedFilesTable(page);
    });
}

function updateSortIndicators() {
    $('#uploaded-files-table thead th').each(function() {
        var $th = $(this);
        var field = $th.data('sort');
        var $indicator = $th.find('.sort-indicator');
        $indicator.html('');
        if (field && field === uploadedFilesSort.field) {
            $indicator.html(uploadedFilesSort.dir === 'asc' ? '&uarr;' : '&darr;');
        }
    });
}

$(function() {
    // ...existing code for setupDragAndDrop...

    // Date filter form
    $('#filter-date-form').on('submit', function(e) {
        e.preventDefault();
        uploadedFilesDate = $('#filter-date').val();
        fetchUploadedFilesTable(1);
    });

    // Set default date to today on page load
    $('#filter-date').val(uploadedFilesDate);

    // Sorting event
    $('#uploaded-files-table thead').on('click', 'th[data-sort]', function() {
        var field = $(this).data('sort');
        if (uploadedFilesSort.field === field) {
            uploadedFilesSort.dir = uploadedFilesSort.dir === 'asc' ? 'desc' : 'asc';
        } else {
            uploadedFilesSort.field = field;
            uploadedFilesSort.dir = 'asc';
        }
        fetchUploadedFilesTable(1);
    });

    // Initial fetch
    fetchUploadedFilesTable();

    // Refresh table after upload
    function refreshTableAfterUpload() {
        fetchUploadedFilesTable();
    }

    // Patch setupDragAndDrop to call refreshTableAfterUpload after upload
    window._origSetupDragAndDrop = setupDragAndDrop;
    setupDragAndDrop = function(formId, inputId, resultId, btnId, indicatorId, selectedFilesId) {
        window._origSetupDragAndDrop(formId, inputId, resultId, btnId, indicatorId, selectedFilesId);
        var $form = $('#' + formId);
        $form.off('submit.ajaxTableRefresh').on('submit.ajaxTableRefresh', function() {
            setTimeout(refreshTableAfterUpload, 500); // Give backend a moment to save
        });
    };

    setupDragAndDrop('upload-xlsx-form-1', 'xlsx_files_1', 'upload-result-1', 'upload-btn-1', 'uploading-indicator-1', 'selected-files-1');
    setupDragAndDrop('upload-xlsx-form-2', 'xlsx_files_2', 'upload-result-2', 'upload-btn-2', 'uploading-indicator-2', 'selected-files-2');
});
</script>
@endpush

@push('scripts')
<script>
var uploadedCsvFilesSort = { field: 'file_name', dir: 'desc' };
var uploadedCsvFilesDate = (function() {
    var d = new Date();
    return d.toISOString().slice(0, 10);
})();

function fetchUploadedCsvFilesTable(page = 1) {
    var dateParam = uploadedCsvFilesDate ? '&filter_date=' + encodeURIComponent(uploadedCsvFilesDate) : '';
    $.get("<?= route_to('rekon-bifast.rekap') ?>?ajax_memstat=1&page=" + page + "&sort=" + uploadedCsvFilesSort.field + "&dir=" + uploadedCsvFilesSort.dir + dateParam, function(resp) {
        if (resp && resp.data) {
            var $tbody = $('#uploaded-csv-files-table tbody');
            $tbody.empty();
            if (resp.data.length === 0) {
                $tbody.append('<tr><td colspan="4" class="text-center text-muted">Belum ada file CSV yang diupload.</td></tr>');
            } else {
                $.each(resp.data, function(i, file) {
                    $tbody.append(
                        '<tr>' +
                        '<td>' + file.no + '</td>' +
                        '<td>' + $('<div>').text(file.file_name).html() + '</td>' +
                        '<td>' + $('<div>').text(file.trx_date).html() + '</td>' +
                        '<td>' + $('<div>').text(file.unique_file).html() + '</td>' +
                        '</tr>'
                    );
                });
            }
            renderUploadedCsvFilesPagination(resp.total, resp.perPage, resp.currentPage);
            updateCsvSortIndicators();
        }
    });
}

function renderUploadedCsvFilesPagination(total, perPage, currentPage) {
    var $pagination = $('#uploaded-csv-files-pagination');
    $pagination.empty();
    var totalPages = Math.ceil(total / perPage);
    if (totalPages <= 1) return;
    for (var i = 1; i <= totalPages; i++) {
        var btn = $('<button type="button" class="btn btn-sm btn-light mx-1"></button>');
        btn.text(i);
        btn.data('page', i);
        if (i === currentPage) btn.addClass('active');
        $pagination.append(btn);
    }
    $pagination.off('click').on('click', 'button', function() {
        var page = $(this).data('page');
        fetchUploadedCsvFilesTable(page);
    });
}

function updateCsvSortIndicators() {
    $('#uploaded-csv-files-table thead th').each(function() {
        var $th = $(this);
        var field = $th.data('sort');
        var $indicator = $th.find('.sort-indicator');
        $indicator.html('');
        if (field && field === uploadedCsvFilesSort.field) {
            $indicator.html(uploadedCsvFilesSort.dir === 'asc' ? '&uarr;' : '&darr;');
        }
    });
}

$(function() {
    // ...existing code...

    // CSV Date filter form
    $('#filter-date-memstat-form').on('submit', function(e) {
        e.preventDefault();
        uploadedCsvFilesDate = $('#filter-date-memstat').val();
        fetchUploadedCsvFilesTable(1);
    });

    // Set default date to today on page load
    $('#filter-date-memstat').val(uploadedCsvFilesDate);

    // Sorting event for CSV table
    $('#uploaded-csv-files-table thead').on('click', 'th[data-sort]', function() {
        var field = $(this).data('sort');
        if (uploadedCsvFilesSort.field === field) {
            uploadedCsvFilesSort.dir = uploadedCsvFilesSort.dir === 'asc' ? 'desc' : 'asc';
        } else {
            uploadedCsvFilesSort.field = field;
            uploadedCsvFilesSort.dir = 'asc';
        }
        fetchUploadedCsvFilesTable(1);
    });

    // Initial fetch
    fetchUploadedCsvFilesTable();

    // Refresh table after CSV upload
    function refreshCsvTableAfterUpload() {
        fetchUploadedCsvFilesTable();
    }

    // Patch setupCsvDragAndDrop to call refreshCsvTableAfterUpload after upload
    window._origSetupCsvDragAndDrop = setupCsvDragAndDrop;
    setupCsvDragAndDrop = function() {
        window._origSetupCsvDragAndDrop();
        var $form = $('#upload-csv-form');
        $form.off('submit.ajaxTableRefresh').on('submit.ajaxTableRefresh', function() {
            setTimeout(refreshCsvTableAfterUpload, 500);
        });
    };

    setupCsvDragAndDrop();
});
</script>
@endpush


