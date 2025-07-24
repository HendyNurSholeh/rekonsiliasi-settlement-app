@extends('layouts.app')
@section('content')
    <ol class="breadcrumb bg-transparent breadcrumb-sm pl-0 pr-0 ml-2">
        <li class="breadcrumb-item">
            <a href="{{ site_url('dashboard') }}">
                <i class="fal fa-home mr-1"></i> Home
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ site_url('user') }}">User</a>
        </li>
        <li class="breadcrumb-item active">{{ $title }}</li>
    </ol>

    <div class="panel">
        <div class="panel-hdr">
            <h2>
                {{ $title }}
            </h2>
            <div class="panel-toolbar">
                <button type="button" class="btn btn-primary btn-sm waves-effect waves-themed" onclick="openModal()">
                    <i class="fal fa-plus mr-1"></i>
                    Tambah
                </button>
            </div>
        </div>
        <div class="panel-container">
            <div class="panel-content">
                <div class="row mb-4">
                    <div class="col-md-8">
                        <label class="form-label" for="date">Unit Kerja</label>
                        <div class="row">
                            <div class="col-6">
                                <select class="select2 form-control w-100" id="branch" name="branch" required>
                                    <option></option>
                                </select>
                            </div>
                            <div class="col-2">
                                <button type="button" class="btn btn-primary btn-sm waves-effect waves-themed"
                                    id="btn_refresh">
                                    <i class="fal fa-sync"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <table id="dt-user" class="table table-bordered table-hover table-striped w-100">
                    <thead class="bg-primary-500">
                        <tr>
                            <th>Nama Pengguna</th>
                            <th>Nama</th>
                            <th>Hak Akses</th>
                            <th>Status</th>
                            <th data-orderable="false" width="10%">Aksi</th>
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
                    <div class="modal-title">
                        <h4>
                            <span id="modal-title"></span>
                        </h4>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="fal fa-times"></i></span>
                    </button>
                </div>
                <div class="modal-body pt-0">
                    <input type="hidden" id="action" class="form-control" name="action" required>
                    <input type="hidden" id="id" class="form-control" name="id" required>
                    <div class="form-row my-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="nomor_absen">
                                    Nomor Absen
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="nomor_absen" class="form-control" name="nomor_absen" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="username">
                                    Nama Pengguna
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="username" class="form-control" name="username" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-row my-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="email">
                                    Email
                                </label>
                                <input type="email" id="email" class="form-control" name="email" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="name">
                                    Nama
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="name" class="form-control" name="name" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-row my-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="kode_unit_kerja">
                                    Unit Kerja
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="select2 form-control w-100" id="kode_unit_kerja" name="kode_unit_kerja">
                                    <option></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="role">
                                    Role
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="select2 form-control w-100" id="role" name="role">
                                    <option></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-row my-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="kode_unit_kerja">
                                    Masa Berlaku
                                </label>
                                <input class="form-control" type="datetime-local" id="expired_at" name="expired_at">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i
                            class="fal fa-times mr-2"></i>Tutup</button>
                    <button type="button" class="btn btn-primary" id="btnSubmit" onclick="submit()"><i
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
                        <h4>Informasi User</h4>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="fal fa-times"></i></span>
                    </button>
                </div>
                <div class="modal-body pt-0">
                    <ul class="nav nav-tabs nav-tabs-clean" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-data"
                                role="tab" aria-selected="true">Data</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-others" role="tab"
                                aria-selected="false">Lainnya</a></li>
                    </ul>

                    <div class="tab-content p-3">
                        <div class="tab-pane fade active show" id="tab-data" role="tabpanel"
                            aria-labelledby="tab-data">
                            <div class="row mt-2">
                                <div class="col">
                                    <p class="m-0"><span class="fw-500">Nomor Absen</span></p>
                                    <span id="nomorAbsenField"></span>
                                </div>
                                <div class="col">
                                    <p class="m-0"><span class="fw-500">Nama Pengguna</span></p>
                                    <span id="usernameField"></span>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col">
                                    <p class="m-0"><span class="fw-500">Email</span></p>
                                    <span id="emailField"></span>
                                </div>
                                <div class="col">
                                    <p class="m-0"><span class="fw-500">Nama</span></p>
                                    <span id="nameField"></span>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col">
                                    <p class="m-0"><span class="fw-500">Unit Kerja</span></p>
                                    <span id="unitKerjaField"></span>
                                </div>
                                <div class="col">
                                    <p class="m-0"><span class="fw-500">Hak Akses</span></p>
                                    <span id="roleField"></span>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col">
                                    <p class="m-0"><span class="fw-500">Status</span></p>
                                    <span id="statusField"></span>
                                </div>
                                <div class="col" id='temp-field' style="display: none;">
                                    <p class="m-0"><span class="fw-500">Masa Berlaku</span></p>
                                    <span id="tempIdField"></span>
                                </div>
                            </div>
                            <div class="row mt-2" id="original-field">
                                <div class="col">
                                    <p class="m-0"><span class="fw-500">Data Asal</span></p>
                                    <span id="originalField"></span>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab-others" role="tabpanel" aria-labelledby="tab-data">
                            <div class="row mt-2">
                                <div class="col">
                                    <p class="m-0"><span class="fw-500">Terakhir Kali Login</span></p>
                                    <span id="lastLoginField"></span>
                                </div>
                                <div class="col">
                                    <p class="m-0"><span class="fw-500">Masa Berlaku Kata Sandi</span></p>
                                    <span id="passwordExpiredField"></span>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col">
                                    <p class="m-0"><span class="fw-500">Terakhit Kali Reset Kata Sandi</span></p>
                                    <span id="resetAtField"></span>
                                </div>
                                <div class="col">
                                    <p class="m-0"><span class="fw-500">Tanggal Dibuat</span></p>
                                    <span id="createdField"></span>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col">
                                    <p class="m-0"><span class="fw-500">Tanggal Diubah</span></p>
                                    <span id="updatedField"></span>
                                </div>
                                <div class="col">
                                    <p class="m-0"><span class="fw-500">Tanggal Dihapus</span></p>
                                    <span id="deletedField"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i
                            class="fal fa-times mr-2"></i>Tutup</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            var csrfName = $('#txt_csrfname').attr('name');
            var csrfHash = $('#txt_csrfname').val();

            function initTable(kode_unit_kerja) {
                return table = $('#dt-user').DataTable({
                    responsive: true,
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ site_url('/dataTables/userAPI') }}",
                        type: "GET",
                        dataType: "json",
                        dataSrc: 'data',
                        data: {
                            kode_unit_kerja: kode_unit_kerja
                        },
                        error: function(xhr, status, error) {
                            console.log("An error occurred: " + error);
                            toastr["error"](error);
                        }
                    },
                    columns: [{
                            data: 'username',
                            render: function(data, type, row) {
                                var plt = '';
                                if (row.expired_at) {
                                    plt = `<span class='badge badge-warning mr-1'>PLT</span>`
                                }
                                return plt + data + '<br>' + `<span class='text-muted'>${row.email ?? '-'}</span>`
                            }
                        },
                        {
                            data: 'name',
                            render: function(data, type, row) {
                                return data + '<br>' +
                                    `<span class='text-muted'>${row.nomor_absen}</span>`
                            }
                        },
                        {
                            data: 'role',
                            render: function(data, type, row) {
                                return row.unit_kerja + '<br>' + `<span class='text-muted'>${data}</span>`
                            }
                        },
                        {
                            data: 'status',
                            class: 'text-center',
                            render: function(data, type, row) {
                                var status = null;
                                var last_login = null;

                                var in_minutes = row.diffInMinutes;

                                if (data == 'ACTIVE') {
                                    status = "<span class='badge badge-success'>Aktif</span>";
                                } else if (data == 'DISABLE') {
                                    status = "<span class='badge badge-danger'>Non Aktif</span>";
                                } else if (data == 'DISABLE_PLT') {
                                    status =
                                        "<span class='badge badge-warning'>Non Aktif, sedang ada PLT lain</span>";
                                } else {
                                    status = `<span class='badge badge-warning'>${data}</span>`;
                                }

                                if (!row.diffForHumans) {
                                    last_login = `<span class='badge badge-danger'>Offline</span>`;
                                } else if (in_minutes < 10) {
                                    last_login = `<span class='badge badge-success'>Online</span>`;
                                } else {
                                    last_login = `<span class='badge badge-light'>${row.diffForHumans}</span>`;
                                }

                                return status + '<br>' + last_login
                            }
                        },
                        {
                            data: 'aksi',
                            className: "text-center"
                        },
                    ]
                });
            }

            function initBranch() {
                return $.ajax({
                    url: "{{ site_url('/options/unitKerjaAPI') }}",
                    type: "POST",
                    dataType: "json",
                    dataSrc: '',
                    data: {
                        [csrfName]: csrfHash
                    },
                    success: function(result) {
                        csrfHash = result.token;

                        if (result.status === 200) {
                            var data = result.data;

                            $('#kode_unit_kerja').select2({
                                dropdownParent: $("#form-modal"),
                                placeholder: "Pilih Unit Kerja",
                                data: result.data
                            })

                            $('#branch').select2({
                                allowClear: true,
                                placeholder: "Pilih Unit Kerja",
                                data: result.data
                            })
                        } else {
                            return toastr["warning"](result.messages);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("An error occurred: " + error);
                        toastr["error"](error);
                    }
                });
            }

            function initRole() {
                return $.ajax({
                    url: "{{ site_url('/options/roleAPI') }}",
                    type: "POST",
                    dataType: "json",
                    dataSrc: '',
                    data: {
                        [csrfName]: csrfHash
                    },
                    success: function(result) {
                        csrfHash = result.token;

                        if (result.status === 200) {
                            var data = result.data;

                            $('#role').select2({
                                dropdownParent: $("#form-modal"),
                                placeholder: "Pilih Role",
                                data: result.data
                            })
                        } else {
                            return toastr["warning"](result.messages);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("An error occurred: " + error);
                        toastr["error"](error);
                    }
                });
            }

            var table = initTable($("#branch").val());
            initBranch();
            initRole();

            function reloadTable() {
                table.destroy();
                table = initTable($("#branch").val());
            }

            $('#btn_refresh').click(function() {
                reloadTable();
            });

            $('#branch').on('change', function(e) {
                reloadTable();
            });

            function openModal() {
                $('#modal-title').html('Form Tambah User');
                $('#action').val('add');

                $('#id').val(null);
                $('#nomor_absen').val(null);
                $('#username').val(null);
                $('#email').val(null);
                $('#name').val(null);
                $('#kode_unit_kerja').val(null).trigger('change');
                $('#role').val(null).trigger('change');
                $('#expired_at').val('');

                $('#form-modal').modal('show');
            }

            function editModal(index) {
                $('#modal-title').html('Form Edit User');
                $('#action').val('edit');
                var data = table.row(index).data();

                $('#id').val(data.id);
                $('#nomor_absen').val(data.nomor_absen);
                $('#username').val(data.username);
                $('#email').val(data.email);
                $('#name').val(data.name);
                $('#kode_unit_kerja').val(data.kode_unit_kerja).trigger('change');
                $('#role').val(data.role).trigger('change');

				$('#expired_at').val(null); 
                if (data.expired_at) {
                    var dateParts = data.expired_at.split(" ");
                    var date = dateParts[0].split("-");
                    var time = dateParts[1].split(":");
                    var formattedDateTime = date[2] + "-" + date[1] + "-" + date[0] + "T" + time[0] + ":" + time[1];
                    $('#expired_at').val(formattedDateTime); 
                }

                $('#form-modal').modal('show');
            }

            function submit() {
                var nomor_absen = $('#nomor_absen').val();
                var username = $('#username').val();
                var name = $('#name').val();
                var email = $('#email').val();
                var kode_unit_kerja = $('#kode_unit_kerja').val();
                var role = $('#role').val();

                if (!nomor_absen) {
                    return toastr["warning"]("Nomor Absen tidak boleh kosong");
                } else if (!username) {
                    return toastr["warning"]("Nama Pengguna tidak boleh kosong");
                } else if (!name) {
                    return toastr["warning"]("Nama tidak boleh kosong");
                } else if (!kode_unit_kerja) {
                    return toastr["warning"]("Unit Kerja tidak boleh kosong");
                } else if (!role) {
                    return toastr["warning"]("Role tidak boleh kosong");
                }

                return hitEndPoint();
            }

            function hitEndPoint() {
                var endpoint;
                if ($('#action').val() == 'add') {
                    endpoint = "{{ site_url('/post/userAPI') }}";
                } else if ($('#action').val() == 'edit') {
                    endpoint = "{{ site_url('/edit/userAPI') }}";

                    if (!$('#id').val()) {
                        return toastr["warning"]("Data tidak ditemukan");
                    }
                }

                $('#btnSubmit').attr('disabled', true);
                $('#btnSubmit').html('<i class="fal fa-circle-notch fa-spin"></i> Simpan');
                $.ajax({
                    url: endpoint,
                    type: "POST",
                    dataType: "json",
                    dataSrc: '',
                    data: {
                        [csrfName]: csrfHash,
                        id: $('#id').val(),
                        nomor_absen: $('#nomor_absen').val(),
                        username: $('#username').val(),
                        name: $('#name').val(),
                        email: $('#email').val(),
                        kode_unit_kerja: $('#kode_unit_kerja').val(),
                        role: $('#role').val(),
                        expired_at: $('#expired_at').val(),
                    },
                    success: function(result) {
                        csrfHash = result.token;

                        if (result.status === 200) {
                            $('#dt-user').DataTable().ajax.reload();
                            $('#form-modal').modal('hide');

                            $('#btnSubmit').attr('disabled', false);
                            $('#btnSubmit').html('<i class="fal fa-save mr-2"></i>Simpan');
                            return toastr["success"](result.messages);
                        } else {
                            $('#dt-user').DataTable().ajax.reload();

                            $('#form-modal').modal('hide');

                            $('#btnSubmit').attr('disabled', false);
                            $('#btnSubmit').html('<i class="fal fa-save mr-2"></i>Simpan');
                            return toastr["warning"](result.messages);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("An error occurred: " + error);
                        $('#form-modal').modal('hide');
                        $('#btnSubmit').attr('disabled', false);
                        $('#btnSubmit').html('<i class="fal fa-save mr-2"></i>Simpan');
                        toastr["error"](error);
                    }
                });
            }

            function deleteData(index) {
                var data = table.row(index).data();
                return toastr.warning(
                    "<button type='button' id='confirmationBtnDelete' class='btn btn-light mt-2 ml-2'>Hapus Data</button>",
                    `Apakah anda yakin ingin menghapus data ${data.name}?`, {
                        // closeButton: false,
                        allowHtml: true,
                        onShown: function(toast) {
                            $("#confirmationBtnDelete").click(function() {
                                return hitEndPointDelete(data.id);
                            });
                        }
                    });
            }

            function hitEndPointDelete(id) {
                $.ajax({
                    url: "{{ site_url('/delete/userAPI') }}",
                    type: "POST",
                    dataType: "json",
                    dataSrc: '',
                    data: {
                        [csrfName]: csrfHash,
                        id: id,
                    },
                    success: function(result) {
                        csrfHash = result.token;

                        if (result.status === 200) {
                            $('#dt-user').DataTable().ajax.reload();
                            return toastr["success"](result.messages);
                        } else {
                            $('#dt-user').DataTable().ajax.reload();
                            return toastr["warning"](result.messages);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("An error occurred: " + error);
                        toastr["error"](error);
                    }
                });
            }

            function activeUser(index) {
                var data = table.row(index).data();
                return toastr.success(
                    "<button type='button' id='confirmationBtnActive' class='btn btn-light mt-2 ml-2'>Aktifkan</button>",
                    `Apakah anda yakin ingin mengaktifkan user ${data.name}?`, {
                        // closeButton: false,
                        allowHtml: true,
                        onShown: function(toast) {
                            $("#confirmationBtnActive").click(function() {
                                return hitEndPointStatus(data.id, 'ACTIVE');
                            });
                        }
                    });
            }

            function disableUser(index) {
                var data = table.row(index).data();
                return toastr.warning(
                    "<button type='button' id='confirmationBtnDisable' class='btn btn-light mt-2 ml-2'>Nonaktifkan</button>",
                    `Apakah anda yakin ingin menonaktifkan user ${data.name}?`, {
                        // closeButton: false,
                        allowHtml: true,
                        onShown: function(toast) {
                            $("#confirmationBtnDisable").click(function() {
                                return hitEndPointStatus(data.id, 'DISABLE');
                            });
                        }
                    });
            }

            function hitEndPointStatus(id, status) {
                $.ajax({
                    url: "{{ site_url('/updateStatus/userAPI') }}",
                    type: "POST",
                    dataType: "json",
                    dataSrc: '',
                    data: {
                        [csrfName]: csrfHash,
                        id: id,
                        status: status,
                    },
                    success: function(result) {
                        csrfHash = result.token;

                        if (result.status === 200) {
                            $('#dt-user').DataTable().ajax.reload();
                            return toastr["success"](result.messages);
                        } else {
                            $('#dt-user').DataTable().ajax.reload();
                            return toastr["warning"](result.messages);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("An error occurred: " + error);
                        toastr["error"](error);
                    }
                });
            }

            function resetPassword(index) {
                var data = table.row(index).data();
                return toastr.warning(
                    "<button type='button' id='confirmationBtnReset' class='btn btn-light mt-2 ml-2'>Reset Password</button>",
                    `Apakah anda yakin ingin mereset user ${data.name}?`, {
                        // closeButton: false,
                        allowHtml: true,
                        onShown: function(toast) {
                            $("#confirmationBtnReset").click(function() {
                                return hitEndPointResetPasswoed(data.id);
                            });
                        }
                    });
            }

            function hitEndPointResetPasswoed(id) {
                $.ajax({
                    url: "{{ site_url('/resetPassword/userAPI') }}",
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
                            $('#dt-user').DataTable().ajax.reload();
                            return toastr["success"](result.messages);
                        } else {
                            $('#dt-user').DataTable().ajax.reload();
                            return toastr["warning"](result.messages);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("An error occurred: " + error);
                        toastr["error"](error);
                    }
                });
            }

            function detailModal(index) {
                var data = table.row(index).data();

                $('#temp-field').hide();
                $('#original-field').hide();
                $('#nomorAbsenField').html(data.nomor_absen);
                $('#usernameField').html(data.username);
                $('#nameField').html(data.name);
                $('#emailField').html(data.email ?? '');
                $('#unitKerjaField').html(data.unit_kerja);
                $('#roleField').html(data.role);
                $('#statusField').html(function() {
                    if (data.status == 'ACTIVE') {
                        return "<span class='badge badge-success'>Aktif</span>";
                    } else if (data.status == 'DISABLE') {
                        return "<span class='badge badge-danger'>Non Aktif</span>";
                    } else if (data.status == 'DISABLE_PLT') {
                        return "<span class='badge badge-warning'>Non Aktif, sedang ada PLT lain</span>";
                    } else {
                        return `<span class='badge badge-warning'>${data}</span>`;
                    }
                });

                if (data.expired_at) {
                    $('#temp-field').show();
                    $('#original-field').show();
                    $('#tempIdField').html(data.expired_at);
                    $('#originalField').html(function() {
                        var array = JSON.parse(data.req_update);
                        var html = '';

                        html += `<b>Hak Akses</b> : ${array.role} <br>`;
                        html += `<b>Unit Kerja</b> : ${array.kode_unit_kerja} <br>`;
                        return html;
                    });
                }

                $('#lastLoginField').html(`${data.last_login} (${data.login_attempt})`);
                $('#passwordExpiredField').html(data.password_expired);
                $('#resetAtField').html(data.reset_at);
                $('#createdField').html(`${data.created_at} (${data.created_by})`);
                $('#updatedField').html(`${data.updated_at} (${data.updated_by})`);
                $('#deletedField').html(`${data.deleted_at} (${data.deleted_by})`);

                $('#detail-modal').modal('show');
            }
        </script>
    @endpush
@stop
