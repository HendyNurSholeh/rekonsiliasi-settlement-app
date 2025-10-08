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
        <i class='subheader-icon fal fa-database'></i> {{ $title }}
        <small>Riwayat transaksi ke Akselgate API</small>
    </h1>
</div>

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
                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="tanggal">Tanggal</label>
                                <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                       value="{{ $tanggalData }}" required>
                            </div>

                            <!-- Transaction Type -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="transaction_type">Tipe Transaksi</label>
                                <select class="form-control select2" id="transaction_type" name="transaction_type">
                                    <option value="">Semua</option>
                                    <option value="CA_ESCROW">CA to Escrow</option>
                                    <option value="ESCROW_BILLER_PL">Escrow to Biller PL</option>
                                </select>
                            </div>

                            <!-- Status -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="status">Status</label>
                                <select class="form-control select2" id="status" name="status">
                                    <option value="">Semua</option>
                                    <option value="success">Sukses</option>
                                    <option value="failed">Gagal</option>
                                </select>
                            </div>

                            <!-- Kode Settle -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="kd_settle">Kode Settle</label>
                                <input type="text" class="form-control" id="kd_settle" name="kd_settle" 
                                       placeholder="Cari kode settle...">
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

<div class="row">
    <div class="col-xl-12">
        <div id="panel-2" class="panel">
            <div class="panel-hdr">
                <h2>Data <span class="fw-300"><i>Log Transaksi</i></span></h2>
                <div class="panel-toolbar">
                    <button class="btn btn-panel" data-action="panel-collapse" data-toggle="tooltip" data-offset="0,10"
                        data-original-title="Collapse"></button>
                </div>
            </div>
            <div class="panel-container show">
                <div class="panel-content">
                    <div class="table-responsive">
                        <table id="dt-akselgate-log" class="table table-bordered table-hover table-striped w-100">
                            <thead class="bg-primary-600 text-white">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Tipe Transaksi</th>
                                    <th>Kode Settle</th>
                                    <th>Request ID</th>
                                    <th>Attempt</th>
                                    <th>Total Tx</th>
                                    <th>Status Code</th>
                                    <th>Status</th>
                                    <th>Latest</th>
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
                <h5 class="modal-title">Detail Log Transaksi</h5>
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

                            <dt class="col-sm-4">Tanggal:</dt>
                            <dd class="col-sm-8" id="detail_created_at">-</dd>

                            <dt class="col-sm-4">Tipe Transaksi:</dt>
                            <dd class="col-sm-8" id="detail_transaction_type">-</dd>

                            <dt class="col-sm-4">Kode Settle:</dt>
                            <dd class="col-sm-8" id="detail_kd_settle">-</dd>

                            <dt class="col-sm-4">Request ID:</dt>
                            <dd class="col-sm-8" id="detail_request_id">-</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">Attempt:</dt>
                            <dd class="col-sm-8" id="detail_attempt_number">-</dd>

                            <dt class="col-sm-4">Total Transaksi:</dt>
                            <dd class="col-sm-8" id="detail_total_transaksi">-</dd>

                            <dt class="col-sm-4">Status Code:</dt>
                            <dd class="col-sm-8" id="detail_status_code_res">-</dd>

                            <dt class="col-sm-4">Response Code:</dt>
                            <dd class="col-sm-8" id="detail_response_code">-</dd>

                            <dt class="col-sm-4">Status:</dt>
                            <dd class="col-sm-8" id="detail_is_success">-</dd>

                            <dt class="col-sm-4">Latest:</dt>
                            <dd class="col-sm-8" id="detail_is_latest">-</dd>
                        </dl>
                    </div>
                </div>

                <div class="row mt-3" id="response_message_wrapper">
                    <div class="col-12">
                        <h6 class="fw-700">Response Message:</h6>
                        <div class="alert alert-info" id="detail_response_message">-</div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6 class="fw-700">Request Payload:</h6>
                        <pre class="p-3 border" style="max-height: 400px; overflow-y: auto;"><code id="detail_request_payload" class="bg-white" style="color: #029c02;">-</code></pre>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-700">Response Payload:</h6>
                        <pre class="p-3 border" style="max-height: 400px; overflow-y: auto;"><code id="detail_response_payload" class="bg-white" style="color: #029c02;">-</code></pre>
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
<script src="{{ base_url('/js/log/akselgate/index.js') }}"></script>
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
