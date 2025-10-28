<!-- Modal Detail Callback Log -->
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
                        <pre class="p-3" style="max-height:400px; overflow:auto; background:#ffffff; border:1px solid #e6e7e8; border-left:4px solid #28a745; border-radius:6px;">
                            <code id="detail_callback_data" style="color:#1b5e20; font-family: Menlo, Monaco, 'Courier New', monospace; font-size:0.95rem; white-space:pre-wrap; word-break:break-word;">-</code>
                        </pre>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>