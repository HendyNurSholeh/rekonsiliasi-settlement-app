@extends('layouts.app')
@section('content')
    <ol class="breadcrumb bg-transparent breadcrumb-sm pl-0 pr-0 ml-2">
        <li class="breadcrumb-item">
            <a href="{{ site_url('dashboard') }}">
                <i class="fal fa-home mr-1"></i> Home
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ site_url('user/maker') }}">User</a>
        </li>
        <li class="breadcrumb-item active">{{ $title }}</li>
    </ol>

    <div class="panel">
        <div class="panel-hdr">
            <h2>
                {{ $title }}
            </h2>
            @if (in_array('create request', $permissions))
                <div class="panel-toolbar">
                    <button type="button" class="btn btn-primary btn-sm waves-effect waves-themed" data-toggle='modal'
                        data-target='#form-modal' id="add_button" onclick='open_modal()'>
                        <i class="fal fa-plus mr-1"></i>
                        Tambah
                    </button>
                </div>
            @endif
        </div>
        <div class="panel-container">
            <div class="panel-content">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label" for="status">Status</label>
                        <div class="row">
                            <div class="col-8">
                                <select class="select2 form-control w-100" multiple="multiple" id="status" name="status"
                                    required>
                                    <option></option>
                                    <option value="pending" selected>Dalam Proses</option>
                                    <option value="approved">Disetujui</option>
                                    <option value="rejected">Ditolak</option>
                                    <option value="return">Kembali ke Data Asal</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <button type="button" class="btn btn-primary btn-sm waves-effect waves-themed"
                                    id="btn_refresh">
                                    <i class="fal fa-sync"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <table id="dt-request" class="table table-bordered table-hover table-striped w-100">
                    <thead class="bg-primary-500">
                        <tr>
                            <th>User</th>
                            <th>Kategori</th>
                            <th>Deskripsi</th>
                            <th>File</th>
                            <th>Status</th>
                            <th>Aksi</th>
                            <th class="none">Maker</th>
                            <th class="none">Approval</th>
                            <th class="none">Returner</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="form-modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
        data-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <span id="title-text">Permohonan Update User</span>
                        <small class="m-0 text-muted">
                            Silakan isi data-data berikut ini!
                        </small>
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="fal fa-times"></i></span>
                    </button>
                </div>
                <div class="modal-body pt-0">
                    <input type="hidden" id="id" class="form-control" name="id" value="{{ session()->id }}"
                        required>
                    <div class="form-row my-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="username">Username <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="username" class="form-control" name="username"
                                    value="{{ session()->username }}" required readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="nomor_absen"> Nomor Absen <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="nomor_absen" class="form-control" name="nomor_absen"
                                    value="{{ session()->nomor_absen }}" required readonly>
                            </div>
                        </div>
                    </div>
                    <div class="form-row my-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="role">Hak Akses <span class="text-danger">*</span>
                                </label>
                                <select class="select2 form-control w-100" id="role" name="role" required>
                                    <option></option>
                                    @foreach ($roles as $key => $value)
                                        <option value="{{ $key }}" selected>{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="branch">Unit Kerja <span class="text-danger">*</span>
                                </label>
                                <select class="select2 form-control w-100" id="branch" name="branch" required>
                                    <option></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-row my-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="category">Kategori <span class="text-danger">*</span>
                                </label>
                                <select class="select2 form-control w-100" id="category" name="category">
                                    <option></option>
                                    <option value="PLT">PLT</option>
                                    <option value="PGS">PGS</option>
                                    <option value="Pelimpahan Kekuasaan">Pelimpahan Kekuasaan</option>
                                    <option value="Mutasi">Mutasi</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="masa_aktif">Masa Berlaku <span
                                        class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" id="masa_aktif" class="form-control" name="masa_aktif"
                                    required>
                            </div>
                        </div>
                    </div>
                    <div class="form-row my-3">
                        <div class="col-md-12">
                            <div class="form-group mb-0">
                                <label class="form-label">
                                    Bukti Permohonan
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="file" name="file">
                                    <label class="custom-file-label" for="file">Choose file</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row my-3">
                        <div class="col-md-12">
                            <label class="form-label" for="keterangan">
                                Keterangan
                                <span class="text-danger">*</span>
                            </label>
                            <textarea id="keterangan" class="form-control" name="keterangan" required rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i
                            class="fal fa-times mr-2"></i>Tutup</button>
                    <button type="button" id="btnSubmit" class="btn btn-primary"><i
                            class="fal fa-save mr-2"></i>Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detail-modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
        data-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title">
                        <h4>Detail Permohonan</h4>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="fal fa-times"></i></span>
                    </button>
                </div>
                <div class="modal-body pt-0">
                    <div class="row mt-2">
                        <div class="col">
                            <p class="m-0"><span class="fw-500">Kategori</span></p>
                            <span id="categoryField"></span>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col">
                            <p class="m-0"><span class="fw-500">User</span></p>
                            <span id="userField"></span>
                        </div>
                        <div class="col">
                            <p class="m-0"><span class="fw-500">Unit Kerja</span></p>
                            <span id="unitKerjaField"></span>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col">
                            <p class="m-0"><span class="fw-500">Data Asal</span></p>
                            <span id="originalField"></span>
                        </div>
                        <div class="col">
                            <p class="m-0"><span class="fw-500">Data yang akan di Update</span></p>
                            <span id="updatedField"></span>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col">
                            <p class="m-0"><span class="fw-500">File</span></p>
                            <span id="fileField"></span>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col" id='colMasaAktifField'>
                            <p class="m-0"><span class="fw-500">Masa Berlaku Sampai</span></p>
                            <span id="masaAktifField"></span>
                        </div>
                        <div class="col">
                            <p class="m-0"><span class="fw-500">Catatan</span></p>
                            <span id="catatanField"></span>
                        </div>
                    </div>
                </div>
                <form id="approval-form">
                    <input type="hidden" id="id_permohonan" name="id_permohonan">
                    <input type="hidden" id="id_user" name="id_user">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i
                                class="fal fa-times mr-2"></i>Tutup</button>
                        <button type="button" class="btn btn-danger" onclick="approval_process('reject')"><i
                                class="fal fa-trash-alt mr-2"></i>Tolak</button>
                        <button type="button" class="btn btn-primary" onclick="approval_process('approve')"><i
                                class="fal fa-check mr-2"></i>Setujui</button>
                    </div>
                </form>
                <form id="return-form">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i
                                class="fal fa-times mr-2"></i>Tutup</button>
                        <button type="button" class="btn btn-warning" onclick="approval_process('return')"><i
                                class="fal fa-undo mr-2"></i>Kembalikan ke Data Asal</button>
                    </div>
                </form>
                <form id="maker-cancel-form">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i
                                class="fal fa-times mr-2"></i>Tutup</button>
                        <button type="button" class="btn btn-danger" onclick="approval_process('reject')"><i
                                class="fal fa-trash-alt mr-2"></i>Batalkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="pdf-modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
        data-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title">
                        <h4 id="pdf-modal-title"></h4>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="fal fa-times"></i></span>
                    </button>
                </div>
                <div class="modal-body" id="file-base64">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            var branch;
            var csrfName = $('#txt_csrfname').attr('name');
            var csrfHash = $('#txt_csrfname').val();

            $('#status').select2({
                placeholder: "Pilih Status",
                minimumResultsForSearch: Infinity,
            });

            $('#role').select2({
                placeholder: "Pilih Role",
                minimumResultsForSearch: Infinity,
                dropdownParent: $("#form-modal"),
            });
            $('#category').select2({
                placeholder: "Pilih Kategori",
                minimumResultsForSearch: Infinity,
                dropdownParent: $("#form-modal"),
            });
            getBranches();

            function initTable() {
                return $('#dt-request').DataTable({
                    responsive: true,
                    processing: true,
                    serverSide: true,
                    lengthChange: false,
                    searching: false,
                    "pageLength": 10,
                    ajax: {
                        url: "{{ site_url('/dataTables/requestAPI') }}",
                        type: "GET",
                        dataType: "json",
                        dataSrc: 'data',
                        data: {
                            status: $('#status').val(),
                        },
                        error: function(xhr, status, error) {
                            console.log("An error occurred: " + error);
                            toastr["error"](error);
                        }
                    },
                    columns: [{
                            data: 'user_id',
                            render: function(data, type, row, meta) {
                                return row.applicant + '<br>' +
                                    `<span class='text-muted'>${data.toString().substring(1)}</span>`;
                            }
                        },
                        {
                            data: 'category',
                            render: function(data, type, row, meta) {
                                if (data == 'Mutasi') {
                                    return `<span class='badge badge-warning'>${data}</span>`;
                                } else if (data == 'PLT' ||data == 'PGS') {
                                    return `<span class='badge badge-success'>${data}</span>`;
                                } else if (data == 'Pelimpahan Kekuasaan') {
                                    return `<span class='badge badge-info'>${data}</span>`;
                                } else {
                                    return `<span class='badge badge-secondary'>${data}</span>`;
                                }
                            }
                        },
                        {
                            data: 'note',
                            render: function(data, type, row, meta) {
                                return data;
                            }
                        },
                        {
                            data: 'filename',
                            render: function(data, type, row, meta) {
                                var value = truncateString(data, 25);
                                return `<a href="#" onclick="showPdf(${row.id}, '${data}')">` + value + '</a>';
                            }
                        },
                        {
                            data: 'status',
                            className: 'text-center',
                            render: function(data, type, row, meta) {
                                if (data == 'pending') {
                                    return `<span class="badge badge-warning">Dalam Proses</span>`;
                                } else if (data == 'approved') {
                                    return `<span class="badge badge-success">Disetujui</span>`;
                                } else if (data == 'rejected') {
                                    return `<span class="badge badge-danger">Ditolak</span>`;
                                } else if (data == 'return') {
                                    return `<span class="badge badge-secondary">Kembali ke Data Asal</span>`;
                                }
                            }
                        },
                        {
                            data: 'aksi',
                            className: "text-center"
                        },
                        {
                            data: 'applicant',
                            render: function(data, type, row, meta) {
                                return data + ` (${row.created_at})`;
                            }
                        },
                        {
                            data: 'approval',
                            render: function(data, type, row, meta) {
                                if (data) {
                                    return data + ` (${row.approved_at})`;
                                } else {
                                    return '-';
                                }
                            }
                        },
                        {
                            data: 'returner',
                            render: function(data, type, row, meta) {
                                if (data) {
                                    return data + ` (${row.return_at})`;
                                } else {
                                    return '-';
                                }
                            }
                        },
                    ],
                });
            }

            var table = initTable();

            function reloadTable() {
                table.destroy();
                table = initTable();
            }

            function getBranches() {
                $.ajax({
                    url: "{{ site_url('options/unitKerjaAPI') }}",
                    type: "POST",
                    dataType: "json",
                    dataSrc: '',
                    data: {
                        [csrfName]: csrfHash
                    },
                    success: function(result) {
                        csrfHash = result.token;

                        branch = result.data;
                        $('#branch').select2({
                            placeholder: "Pilih Unit Kerja",
                            data: result.data,
                            dropdownParent: $("#form-modal"),
                        })

                        $('#branch').val(
                                "{{ session()->kode_unit_kerja }}")
                            .change();
                        $('#role').val(
                                "{{ session()->role }}")
                            .change();
                    },
                    error: function(xhr, status, error) {
                        console.log("An error occurred: " + error);
                        toastr["error"](error);
                    }
                });
            }

            function open_modal() {
                // $('#id').val(null);
                // $('#user').val(null);
                $('#usernameNew').val(null);
                $('#role').val('{{ session()->role }}').trigger('change');
                $('#branch').val('{{ session()->kode_unit_kerja }}').trigger('change');
                $('#category').val(null).trigger('change');
                $('#masa_aktif').val(null);
                $('#file').val(null);
                $('#keterangan').val(null);
            }

            function showPdf(id, filename) {
                $('#pdf-modal-title').empty();
                $('#file-base64').empty();

                $.ajax({
                    url: "{{ site_url('/getPdf/requestAPI') }}",
                    type: "POST",
                    dataType: "json",
                    dataSrc: '',
                    data: {
                        [csrfName]: csrfHash,
                        id: id
                    },
                    success: function(result) {
                        csrfHash = result.token;

                        if (result.status === 200) {
                            var dataPdf = result.data;

                            $('#pdf-modal-title').html(filename);
                            $('#file-base64').html(`
								<embed src="data:application/pdf;base64,base64encodedpdf${dataPdf}" height="550" width="100%">
							`);

                            $('#pdf-modal').modal('show');
                        } else {
                            toastr["warning"](result.messages);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("An error occurred: " + error);
                        toastr["error"](error);
                    }
                });
            }

            function truncateString(str, maxLength) {
                if (str.length > maxLength) {
                    return str.substring(0, maxLength) + '...';
                } else {
                    return str;
                }
            }

            $('#category').on('change', function() {
                const value = $(this).val();

                if (value == "Pelimpahan Kekuasaan") {
                    var today = new Date().toISOString().substr(0, 10);
                    $('#masa_aktif').val(today + 'T23:59');
                    $('#masa_aktif').prop('readonly', true);
                } else if (value == "Mutasi") {
                    $('#masa_aktif').val('');
                    $('#masa_aktif').prop('readonly', true);
                } else {
                    $('#masa_aktif').prop('readonly', false);
                }
            })

            $('#btnSubmit').on('click', function() {
                var id = $('#id').val();
                var username = $('#username').val();
                var role = $('#role').val();
                var branch = $('#branch').val();
                var category = $('#category').val();
                var masaAktif = $('#masa_aktif').val();
                var file = $('#file').prop('files')[0];
                var keterangan = $('#keterangan').val();

                if (!id) {
                    return toastr["warning"]("User ID harus diisi");
                }
                if (!username) {
                    return toastr["warning"]("Username harus diisi");
                }
                if (!role) {
                    return toastr["warning"]("Role harus diisi");
                }
                if (!branch) {
                    return toastr["warning"]("Unit Kerja harus diisi");
                }
                if (!category) {
                    return toastr["warning"]("Kategori harus diisi");
                }
                if (!masaAktif && category != "Mutasi") {
                    return toastr["warning"]("Masa Aktif harus diisi");
                }
                if (!file) {
                    return toastr["warning"]("File harus diisi");
                }
                if (file.type !== 'application/pdf') {
                    return toastr["warning"]("Please upload a valid PDF file.");
                }
                if (!keterangan) {
                    return toastr["warning"]("Keterangan harus diisi");
                }

                var formData = new FormData();
                formData.append('file', file);
                formData.append('id', id);
                formData.append('username', username);
                formData.append('role', role);
                formData.append('branch', branch);
                formData.append('category', category);
                formData.append('masa_aktif', masaAktif);
                formData.append('keterangan', keterangan);
                formData.append(csrfName, csrfHash);

                $('#btnSubmit').attr('disabled', true);
                $('#btnSubmit').html('<i class="fal fa-circle-notch fa-spin"></i> Simpan');
                $.ajax({
                    url: "{{ site_url('/post/requestAPI') }}",
                    type: "POST",
                    dataType: "json",
                    // dataSrc: '',
                    // enctype: 'multipart/form-data',
                    processData: false,
                    contentType: false,
                    cache: false,
                    data: formData,
                    success: function(result) {
                        csrfHash = result.token;
                        if (result.status === 200) {
                            reloadTable();
                            $('#form-modal').modal('hide');

                            $('#btnSubmit').attr('disabled', false);
                            $('#btnSubmit').html('<i class="fal fa-save mr-2"></i>Simpan');
                            return toastr["success"](result.message);
                        } else {
                            reloadTable();
                            $('#form-modal').modal('hide');

                            $('#btnSubmit').attr('disabled', false);
                            $('#btnSubmit').html('<i class="fal fa-save mr-2"></i>Simpan');
                            return toastr["warning"](result.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("An error occurred: " + error);
                        $('#form-modal').modal('hide');
                        toastr["error"](error);
                    }
                });
            });

            function detail_modal(buttonElement, index) {
                var approve_button = buttonElement.dataset.disabled;
                var return_button = buttonElement.dataset.return;
                var maker_cancel_button = buttonElement.dataset.cancelMaker;
                var raw = table.rows(index).data()[0];

                if (approve_button) {
                    $('form#approval-form').show();
                } else {
                    $('form#approval-form').hide();
                }

                if (return_button) {
                    $('form#return-form').show();
                } else {
                    $('form#return-form').hide();
                }

                if (maker_cancel_button) {
                    $('form#maker-cancel-form').show();
                } else {
                    $('form#maker-cancel-form').hide();
                }

                $('#id_permohonan').val();
                $('#id_user').val();
                $('#userField').empty();
                $('#unitKerjaField').empty();
                $('#categoryField').empty();
                $('#originalField').empty();
                $('#updatedField').empty();
                $('#masaAktifField').empty();
                $('#fileField').empty();
                $('#catatanField').empty();
                $('#colMasaAktifField').show();

                var originalField = JSON.parse(raw.original_field);
                var updatedField = JSON.parse(raw.updated_field);

                const usernameOri = originalField.username;
                const rolesOri = originalField.role;
                const kodeUKOri = originalField.kode_unit_kerja;

                const usernameUpd = updatedField.username;
                const rolesUpd = updatedField.role;
                const kodeUKUpd = updatedField.kode_unit_kerja;

                $('#id_permohonan').val(raw.id);
                $('#id_user').val(raw.user_id);
                $('#userField').append(raw.applicant);
                $('#unitKerjaField').append(function() {
                    const item = branch.find(element => element.id === raw.cabang);
                    return item ? item.text : null;
                });
                $('#categoryField').append(function() {
                    if (raw.category == 'Mutasi') {
                        return `<span class='badge badge-warning'>${raw.category}</span>`;
                    } else if (raw.category == 'PLT' ||raw.category == 'PGS') {
                        return `<span class='badge badge-success'>${raw.category}</span>`;
                    } else if (raw.category == 'Pelimpahan Kekuasaan') {
                        return `<span class='badge badge-info'>${raw.category}</span>`;
                    } else {
                        return `<span class='badge badge-secondary'>${raw.category}</span>`;
                    }
                });

                $('#originalField').append(function() {
                    var html = '';

                    if (usernameOri !== usernameUpd) {
                        html += `<b>Username</b> : ${usernameOri} <br>`;
                    }
                    if (rolesOri !== rolesUpd) {
                        html += `<b>Hak Akses</b> : ${rolesOri} <br>`;
                    }
                    if (kodeUKOri !== kodeUKUpd) {
                        const item = branch.find(element => element.id === kodeUKOri);
                        html += `<b>Unit Kerja</b> : ${item ? item.text : null} <br>`;
                    }

                    return html;
                });

                $('#updatedField').append(function() {
                    var html = '';

                    if (usernameOri !== usernameUpd) {
                        html += `<b>Username</b> : ${usernameUpd} <br>`;
                    }
                    if (rolesOri !== rolesUpd) {
                        html += `<b>Hak Akses</b> : ${rolesUpd} <br>`;
                    }
                    if (kodeUKOri !== kodeUKUpd) {
                        const item = branch.find(element => element.id === kodeUKUpd);
                        html += `<b>Unit Kerja</b> : ${item ? item.text : null} <br>`;
                    }

                    return html;
                });

                if (raw.category != 'Mutasi') {
                    $('#masaAktifField').append(function() {
                        var date = new Date(updatedField.expired_at);
                        var options = {
                            weekday: 'long',
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                            hour: 'numeric',
                            minute: 'numeric'
                        };

                        return date.toLocaleTimeString("id-ID", options);
                    });
                } else {
                    $('#colMasaAktifField').hide();
                }

                $('#fileField').append(function() {
                    return `<a href="#" onclick="showPdf(${raw.id}, '${raw.filename}')">` + raw.filename + '</a>';
                });
                $('#catatanField').append(raw.note);

                $('#detail-modal').modal('show');
            }

            function approval_process(action) {
                $('#detail-modal .modal-footer button').prop('disabled', true);

                $.ajax({
                    url: "{{ site_url('/approval/requestAPI') }}",
                    type: "POST",
                    dataType: "json",
                    dataSrc: '',
                    data: {
                        [csrfName]: csrfHash,
                        id: $('#id_permohonan').val(),
                        id_user: $('#id_user').val(),
                        action: action
                    },
                    success: function(result) {
                        $('#detail-modal .modal-footer button').prop('disabled', false);
                        csrfHash = result.token;

                        if (result.status === 200) {
                            reloadTable();
                            $('#detail-modal').modal('hide');
                            return toastr["success"](result.messages);
                        } else {
                            reloadTable();
                            $('#detail-modal').modal('hide');
                            return toastr["warning"](result.messages);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("An error occurred: " + error);
                        toastr["error"](error);
                    }
                });
            }

            $('#btn_refresh').click(function() {
                reloadTable();
            });
            $('#status').change(function() {
                reloadTable();
            });
        </script>
    @endpush
@stop
