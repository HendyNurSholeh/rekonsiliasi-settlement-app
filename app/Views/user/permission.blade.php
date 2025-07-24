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
                <table id="dt-permission" class="table table-bordered table-hover table-striped w-100">
                    <thead class="bg-primary-500">
                        <tr>
                            <th width="5%" class="d-none">ID</th>
                                        <th width="5%" >No</th>
                            <th>Nama Permission</th>
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
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label" for="name">
                                    Nama Permission
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="name" class="form-control" name="name" required>
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

            function initTable() {
                return table = $('#dt-permission').DataTable({
                    responsive: true,
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ site_url('/dataTables/permissionAPI') }}",
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
                            data: 'name'
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
                $('#modal-title').html('Form Tambah Permission');
                $('#action').val('add');

                $('#id').val(null);
                $('#key').val(null);
                $('#name').val(null);

                $('#form-modal').modal('show');
            }

            function editModal(index) {
                $('#modal-title').html('Form Edit Permission');
                $('#action').val('edit');
                var data = table.row(index).data();

                $('#id').val(data.id);
                $('#key').val(data.key);
                $('#name').val(data.name);

                $('#form-modal').modal('show');
            }

            function submit() {
                var name = $('#name').val();
                if (!name) {
                    return toastr["warning"]("Nama tidak boleh kosong");
                }

                return hitEndPoint();
            }

            function hitEndPoint() {
                var endpoint;
                if ($('#action').val() == 'add') {
                    endpoint = "{{ site_url('/post/permissionAPI') }}";
                } else if ($('#action').val() == 'edit') {
                    endpoint = "{{ site_url('/edit/permissionAPI') }}";

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
                        key: $('#key').val(),
                        name: $('#name').val()
                    },
                    success: function(result) {
                        csrfHash = result.token;

                        if (result.status === 200) {
                            $('#dt-permission').DataTable().ajax.reload();
                            $('#form-modal').modal('hide');

                            $('#btnSubmit').attr('disabled', false);
                            $('#btnSubmit').html('<i class="fal fa-save mr-2"></i>Simpan');
                            return toastr["success"](result.messages);
                        } else {
                            $('#dt-permission').DataTable().ajax.reload();

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
                    url: "{{ site_url('/delete/permissionAPI') }}",
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
                            $('#dt-permission').DataTable().ajax.reload();
                            return toastr["success"](result.messages);
                        } else {
                            $('#dt-permission').DataTable().ajax.reload();
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
