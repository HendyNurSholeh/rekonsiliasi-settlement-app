<div class="modal fade" id="approvalModal" tabindex="-1" role="dialog" aria-labelledby="approvalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-extra-large" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approvalModalLabel">
                    <i class="fal fa-check-circle"></i> Detail Jurnal Settlement
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Settlement Info -->
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0" id="modalTitle">Jurnal Settlement</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Kode Settle</label>
                                    <input type="text" class="form-control" id="modal_kd_settle" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Nama Produk</label>
                                    <input type="text" class="form-control" id="modal_nama_produk" readonly>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Total Jurnal (KR)</label>
                                    <input type="text" class="form-control" id="modal_total_amount" readonly>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Net Amount Debet</label>
                                    <input type="text" class="form-control" id="modal_net_debet" readonly>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Net Amount Credit</label>
                                    <input type="text" class="form-control" id="modal_net_credit" readonly>
                                </div>
                            </div>
                        </div>
                        <!-- Warning for net mismatch -->
                        <div class="row">
                            <div class="col-12">
                                <div id="netWarning" style="display: none;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Jurnal -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">Detail Jurnal</h6>
                    </div>
                    <div class="card-body p-1">
                        <div class="table-responsive">
                            <table class="table table-striped table-sm table-compact" id="detailJurnalTable">
                                <thead>
                                    <tr>
                                        <th class="text-xs">Jenis Settle</th>
                                        <th class="text-xs">ID Partner</th>
                                        <th class="text-xs">Core</th>
                                        <th class="text-xs">Debit Account</th>
                                        <th class="text-xs">Debit Name</th>
                                        <th class="text-xs">Credit Core</th>
                                        <th class="text-xs">Credit Account</th>
                                        <th class="text-xs">Credit Name</th>
                                        <th class="text-xs">Amount</th>
                                        <th class="text-xs">Description</th>
                                        <th class="text-xs">Ref Number</th>
                                    </tr>
                                </thead>
                                <tbody id="detailJurnalBody">
                                    <!-- Data detail akan dimuat via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fal fa-times"></i> Tutup
                </button>
                <div id="approvalButtons">
                    <button type="button" class="btn btn-danger" id="rejectBtn" onclick="processApproval('reject')">
                        <i class="fal fa-times-circle"></i> Tolak
                    </button>
                    <button type="button" class="btn btn-success" id="approveBtn" onclick="processApproval('approve')">
                        <i class="fal fa-check-circle"></i> Setujui
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>