<div class="modal fade" id="createJurnalModal" tabindex="-1" role="dialog" aria-labelledby="createJurnalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createJurnalModalLabel">
                    <i class="fal fa-plus-circle"></i> Konfirmasi Buat Jurnal Settlement
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fal fa-exclamation-triangle"></i>
                    <strong>Perhatian!</strong> Pastikan data sudah benar sebelum membuat jurnal settlement.
                </div>
                
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">Detail Produk</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Produk</label>
                                    <input type="text" class="form-control" id="modal_nama_produk" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Rekonsiliasi</label>
                                    <input type="text" class="form-control" id="modal_tanggal_rekon" readonly>
                                </div>
                            </div>
                            <div class="col-md-4 mt-2">
                                <div class="form-group">
                                    <label>File Settle</label>
                                    <input type="text" class="form-control" id="modal_file_settle" readonly>
                                </div>
                            </div>
                            <div class="col-md-4 mt-2">
                                <div class="form-group">
                                    <label>Amount Detail</label>
                                    <input type="text" class="form-control" id="modal_amount_detail" readonly>
                                </div>
                            </div>
                            <div class="col-md-4 mt-2">
                                <div class="form-group">
                                    <label>Amount Rekap</label>
                                    <input type="text" class="form-control" id="modal_amount_rekap" readonly>
                                </div>
                            </div>
                            <div class="col-md-4 mt-2">
                                <div class="form-group">
                                    <label>Selisih</label>
                                    <input type="text" class="form-control" id="modal_selisih" readonly>
                                </div>
                            </div>
                            <div class="col-md-4 mt-2">
                                <div class="form-group">
                                    <label>Jum TX Dispute</label>
                                    <input type="text" class="form-control" id="modal_jum_tx_dispute" readonly>
                                </div>
                            </div>
                            <div class="col-md-4 mt-2">
                                <div class="form-group">
                                    <label>Amount TX Dispute</label>
                                    <input type="text" class="form-control" id="modal_amount_tx_dispute" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="fal fa-info-circle"></i>
                            <strong>Syarat Validasi:</strong>
                            <ul class="mb-0 mt-2">
                                <li>SELISIH harus = 0 (Amount Detail - Amount Rekap = 0)</li>
                                <li>JUM_TX_DISPUTE harus = 0 (Tidak ada transaksi yang dispute)</li>
                                <li>AMOUNT_TX_DISPUTE harus = 0 (Total amount dispute = 0)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fal fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-primary" id="confirmCreateJurnalBtn" onclick="confirmCreateJurnal()">
                    <i class="fal fa-check"></i> Ya, Buat Jurnal
                </button>
            </div>
        </div>
    </div>
</div>