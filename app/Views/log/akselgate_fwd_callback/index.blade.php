@extends('layouts.app')

@section('content')
<ol class="breadcrumb page-breadcrumb">
    <li class="breadcrumb-item"><a href="{{ site_url('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item">Log</li>
    <li class="breadcrumb-item active">{{ $title }}</li>
    <li class="position-absolute pos-top pos-right d-none d-sm-block"><span class="js-get-date"></span></li>
</ol>

<div class="subheader">
    <h1 class="subheader-title">
        <i class='subheader-icon fal fa-server'></i> {{ $title }}
        <small>Riwayat callback dari Akselgate FWD Gateway</small>
    </h1>
</div>

<!-- Filter Panel -->
<div class="row">
    <div class="col-xl-12">
        <div id="panel-1" class="panel">
            <div class="panel-hdr">
                <h2>Filter <span class="fw-300"><i>Data</i></span></h2>
                <div class="panel-toolbar">
                    <button class="btn btn-panel" data-action="panel-collapse" data-toggle="tooltip" data-offset="0,10"
                        data-original-title="Collapse"></button>
                </div>
            </div>
            <div class="panel-container show">
                <div class="panel-content">
                    <form id="filterForm">
                        <div class="form-row">
                            <!-- Tanggal -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="tanggal">Tanggal</label>
                                <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                       value="{{ $tanggalData }}" required>
                            </div>

                            <!-- Kode Settle -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="kd_settle">Kode Settle</label>
                                <input type="text" class="form-control" id="kd_settle" name="kd_settle" 
                                       placeholder="Cari kode settle...">
                            </div>

                            <!-- Status -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="status">Status</label>
                                <select class="form-control select2" id="status" name="status">
                                    <option value="">Semua</option>
                                    <option value="SUCCESS">Success</option>
                                    <option value="FAILED">Failed</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fal fa-filter"></i> Filter
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" id="btnReset">
                                    <i class="fal fa-redo"></i> Reset
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DataTable -->
<div class="row">
    <div class="col-xl-12">
        <div id="panel-2" class="panel">
            <div class="panel-hdr">
                <h2>Data <span class="fw-300"><i>Log Callback</i></span></h2>
                <div class="panel-toolbar">
                    <button class="btn btn-panel" data-action="panel-collapse" data-toggle="tooltip" data-offset="0,10"
                        data-original-title="Collapse"></button>
                </div>
            </div>
            <div class="panel-container show">
                <div class="panel-content">
                    <div class="table-responsive">
                        <table id="dt-callback-log" class="table table-bordered table-hover table-striped w-100">
                            <thead class="bg-primary-600 text-white">
                                <tr>
                                    <th>No</th>
                                    <th>Waktu Diterima</th>
                                    <th>REF Number</th>
                                    <th>Kode Settle</th>
                                    <th>Response Code</th>
                                    <th>Core Ref</th>
                                    <th>Status</th>
                                    <th>Processed</th>
                                    <th>IP Address</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="modalDetail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Callback Log</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">ID:</dt>
                            <dd class="col-sm-8" id="detail_id">-</dd>

                            <dt class="col-sm-4">REF Number:</dt>
                            <dd class="col-sm-8 fw-700" id="detail_ref_number">-</dd>

                            <dt class="col-sm-4">Kode Settle:</dt>
                            <dd class="col-sm-8 fw-700" id="detail_kd_settle">-</dd>

                            <dt class="col-sm-4">Response Code:</dt>
                            <dd class="col-sm-8" id="detail_res_code">-</dd>

                            <dt class="col-sm-4">Core Reference:</dt>
                            <dd class="col-sm-8" id="detail_res_coreref">-</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">Status:</dt>
                            <dd class="col-sm-8" id="detail_status">-</dd>

                            <dt class="col-sm-4">Processed:</dt>
                            <dd class="col-sm-8" id="detail_is_processed">-</dd>

                            <dt class="col-sm-4">IP Address:</dt>
                            <dd class="col-sm-8" id="detail_ip_address">-</dd>

                            <dt class="col-sm-4">Waktu Diterima:</dt>
                            <dd class="col-sm-8" id="detail_created_at">-</dd>

                            <dt class="col-sm-4">Waktu Diproses:</dt>
                            <dd class="col-sm-8" id="detail_processed_at">-</dd>
                        </dl>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="fw-700">Raw Callback Data (JSON):</h6>
                        <pre class="bg-dark p-3" style="max-height: 400px; overflow-y: auto;"><code id="detail_callback_data" style="color: #d4d4d4;">-</code></pre>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ base_url('/js/log/akselgate_fwd_callback/index.js') }}"></script>
<script>
    $(document).ready(function()
    {
        $(function()
        {
            $('.select2').select2();
        });
    });
</script>
@endpush
