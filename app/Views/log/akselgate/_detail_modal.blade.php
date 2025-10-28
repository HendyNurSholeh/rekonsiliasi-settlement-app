<!-- Modal Detail Aksel Gate Response Log -->
<div class="modal fade" id="modalDetailAkselgate" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Akselgate Response Log - <span id="akselgate-log-title">Kode Settle</span></h5>
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