@extends('layouts.app')
@section('content')
    <ol class="breadcrumb bg-transparent breadcrumb-sm pl-0 pr-0 ml-2">
        <li class="breadcrumb-item">
            <a href="{{ site_url('dashboard') }}">
                <i class="fal fa-home mr-1"></i> Home
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ site_url('log/activity') }}">Log Activity</a>
        </li>
        <li class="breadcrumb-item active">{{ $title }}</li>
    </ol>

    <div class="panel">
        <div class="panel-hdr">
            <h2>
                {{ $title }}
            </h2>
        </div>
        <div class="panel-container">
            <div class="panel-content">
                <table id="dt-log-activity" class="table table-bordered table-hover table-striped w-100">
                    <thead class="bg-primary-500">
                        <tr>
                            <th>Tanggal</th>
                            <th>Kategori</th>
                            <th>User</th>
                            <th>Subjek</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th data-orderable="false">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detail-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title fw-500">Detail Log</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="fal fa-times"></i></span>
                    </button>
                </div>
                <div class="modal-body pt-0">
                    <p class="fs-nano text-monospace" id='log-properties'>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            var csrfName = $('#txt_csrfname').attr('name');
            var csrfHash = $('#txt_csrfname').val();

            var table = $('#dt-log-activity').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                order: [
                    [0, 'desc']
                ],
                "pageLength": 25,
                ajax: {
                    url: "{{ site_url('/dataTables/logActivityAPI') }}",
                    type: "GET",
                    dataType: "json",
                    dataSrc: 'data',
                    error: function(xhr, status, error) {
                        console.log("An error occurred: " + error);
                        toastr["error"](error);
                    }
                },
                columns: [{
                        data: 'created_at',
                        width: '12%'
                    },
                    {
                        data: 'log_name',
                        width: '7%',
                        className: "text-center"
                    },
                    {
                        data: 'causer_name',
                        render: function(data, type, row, meta) {
                            return data + "<div class='fs-sm text-muted'>" + row.ip_address + "</div>";
                        }
                    },
                    {
                        data: 'subject',
						render: function(data, type, row, meta) {
							if (row.subject_id) {
								return data + "<div class='fs-sm text-muted'>" + row.subject_id + "</div>";
							}

							return data;
						}
                    },
                    {
                        data: 'description'
                    },
                    {
                        data: 'event',
                        width: '7%',
                        className: "text-center"
                    },
                    {
                        data: 'aksi',
                        width: '7%',
                        className: "text-center"
                    },
                ],
            });

            function open_modal(id) {
                $('#log-properties').empty();
                $.ajax({
                    url: "{{ site_url('/show/logActivityAPI/') }}" + id,
                    type: "GET",
                    dataType: "json",
                    dataSrc: '',
                    success: function(result) {
                        $('#log-properties').append(result);
                    },
                    error: function(xhr, status, error) {
                        console.log("An error occurred: " + error);
                        $('#detail-modal').modal('hide');
                        toastr["error"](error);
                    }
                });
            }
        </script>
    @endpush
@stop
