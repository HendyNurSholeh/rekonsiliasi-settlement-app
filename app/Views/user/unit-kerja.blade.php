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
                <table id="dt-unit-kerja" class="table table-bordered table-hover table-striped w-100">
                    <thead class="bg-primary-500">
                        <tr>
                           <th width="5%" class="d-none">ID</th>
                                        <th width="5%" >No</th>
                            <th>Kode</th>
                            <th>Nama Unit Kerja</th>
                            <th>Tipe</th>
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
                                <label class="form-label" for="kode">
                                    Kode
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="kode" class="form-control" name="kode" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="kode_dept">
                                    Kode Departemen
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="kode_dept" class="form-control" name="kode_dept" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-row my-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="kode_t24">
                                    Kode T24
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="kode_t24" class="form-control" name="kode_t24" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="level">
                                    Level
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="number" id="level" class="form-control" name="level" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-row my-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="type">
                                    Tipe
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="select2 form-control w-100" name="type" id="type">
                                    <option></option>
                                    <option value="cabang">Cabang</option>
                                    <option value="divisi">Divisi</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="name">
                                    Nama Cabang
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="name" class="form-control" name="name" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-row my-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="synonym">
                                    Sinonim
                                </label>
                                <input type="text" id="synonym" class="form-control" name="synonym" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="telp">
                                    Telepon
                                </label>
                                <input type="text" id="telp" class="form-control" name="telp" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-row my-3">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label" for="address">
                                    Alamat
                                </label>
                                <textarea id="address" class="form-control" name="address" required rows="3"></textarea>
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

    @push('scripts')
        <script>
            var csrfName = $('#txt_csrfname').attr('name');
            var csrfHash = $('#txt_csrfname').val();

            $('#type').select2({
                dropdownParent: $("#form-modal"),
                placeholder: "Pilih Tipe Unit Kerja"
            })

            function initTable() {
                return table = $('#dt-unit-kerja').DataTable({
                    responsive: true,
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ site_url('/dataTables/unitKerjaAPI') }}",
                        type: "GET",
                        dataType: "json",
                        dataSrc: 'data',
                        error: function(xhr, status, error) {
                            console.log("An error occurred: " + error);
                            toastr["error"](error);
                        }
                    },
                 columns: [{
                            data: 'id',
                            className: "d-none"
                        },
                        {
                            data: 'no',
                            className: "text-center"
                        },
                        {
                            data: 'kode_t24'
                        },
                        {
                            data: 'name'
                        },
                        {
                            data: 'type',
                            render: function(data, type, row) {
                                if (data == 'cabang') {
                                    return '<span class="badge badge-info">Cabang</span>';
                                } else if (data == 'divisi') {
                                    return '<span class="badge badge-success">Divisi</span>';
                                }
                            },
                            className: "text-center"
                        },
                        {
                            data: 'aksi',
                            className: "text-center"
                        },
                    ]
                });
            }

            var table = initTable();

            function reloadTable() {
                table.destroy();
                table = initTable();
            }

            function openModal() {
                $('#modal-title').html('Form Tambah Unit Kerja');
                $('#action').val('add');

                $('#id').val(null);
                $('#kode').val(null);
                $('#kode_dept').val(null);
                $('#kode_t24').val(null);
                $('#level').val(null);
                $('#type').val(null).trigger('change');
                $('#name').val(null);
                $('#synonym').val(null);
                $('#telp').val(null);
                $('#address').val(null);

                $('#form-modal').modal('show');
            }

            function editModal(index) {
                $('#modal-title').html('Form Edit Unit Kerja');
                $('#action').val('edit');
                var data = table.row(index).data();

                $('#id').val(data.id);
                $('#kode').val(data.kode);
                $('#kode_dept').val(data.kode_dept);
                $('#kode_t24').val(data.kode_t24);
                $('#level').val(data.level);
                $('#type').val(data.type).trigger('change');
                $('#name').val(data.name);
                $('#synonym').val(data.synonym);
                $('#telp').val(data.telp);
                $('#address').val(data.address);

                $('#form-modal').modal('show');
            }

            function submit() {
                var kode = $('#kode').val();
                var kode_dept = $('#kode_dept').val();
                var kode_t24 = $('#kode_t24').val();
                var level = $('#level').val();
                var type = $('#type').val();
                var name = $('#name').val();
                var synonym = $('#synonym').val();
                var telp = $('#telp').val();
                var address = $('#address').val();

                if (!kode) {
                    return toastr["warning"]("Kode tidak boleh kosong");
                } else if (!kode_dept) {
                    return toastr["warning"]("Kode Dept tidak boleh kosong");
                } else if (!kode_t24) {
                    return toastr["warning"]("Kode T24 tidak boleh kosong");
                } else if (!level) {
                    return toastr["warning"]("Level tidak boleh kosong");
                } else if (!type) {
                    return toastr["warning"]("Tipe tidak boleh kosong");
                } else if (!name) {
                    return toastr["warning"]("Nama tidak boleh kosong");
                }

                return hitEndPoint();
            }

            function hitEndPoint() {
                var endpoint;
                if ($('#action').val() == 'add') {
                    endpoint = "{{ site_url('/post/unitKerjaAPI') }}";
                } else if ($('#action').val() == 'edit') {
                    endpoint = "{{ site_url('/edit/unitKerjaAPI') }}";

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
                        kode: $('#kode').val(),
                        kode_dept: $('#kode_dept').val(),
                        kode_t24: $('#kode_t24').val(),
                        level: $('#level').val(),
                        type: $('#type').val(),
                        name: $('#name').val(),
                        synonym: $('#synonym').val(),
                        telp: $('#telp').val(),
                        address: $('#address').val()
                    },
                    success: function(result) {
                        csrfHash = result.token;

                        if (result.status === 200) {
                            $('#dt-unit-kerja').DataTable().ajax.reload();
                            $('#form-modal').modal('hide');

                            $('#btnSubmit').attr('disabled', false);
                            $('#btnSubmit').html('<i class="fal fa-save mr-2"></i>Simpan');
                            return toastr["success"](result.messages);
                        } else {
                            $('#dt-unit-kerja').DataTable().ajax.reload();

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
                    "<button type='button' id='confirmationButtonYes' class='btn btn-light mt-2 ml-2'>Hapus Data</button>",
                    `Apakah anda yakin ingin menghapus data ${data.name}?`, {
                        // closeButton: false,
                        allowHtml: true,
                        onShown: function(toast) {
                            $("#confirmationButtonYes").click(function() {
                                return hitEndPointDelete(data.id);
                            });
                        }
                    });
            }

            function hitEndPointDelete(id) {
                $.ajax({
                    url: "{{ site_url('/delete/unitKerjaAPI') }}",
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
                            $('#dt-unit-kerja').DataTable().ajax.reload();
                            return toastr["success"](result.messages);
                        } else {
                            $('#dt-unit-kerja').DataTable().ajax.reload();
                            return toastr["warning"](result.messages);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("An error occurred: " + error);
                        toastr["error"](error);
                    }
                });
            }
        </script>
    @endpush
@stop
