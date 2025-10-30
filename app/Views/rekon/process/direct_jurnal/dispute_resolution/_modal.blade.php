<div class="modal fade" id="disputeModal" tabindex="-1" role="dialog" aria-labelledby="disputeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="disputeModalLabel">
                    <i class="fal fa-edit"></i> Proses Data Dispute
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="disputeForm">
                    <input type="hidden" id="dispute_id" name="id">
                    <!-- Data Transaksi -->
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">Data Transaksi</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>ID Partner</label>
                                        <input type="text" class="form-control" id="modal_idpartner" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Terminal ID</label>
                                        <input type="text" class="form-control" id="modal_terminalid" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Is Direct Fee</label>
                                        <input type="text" class="form-control" id="modal_is_direct_fee" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-md-2">
                                    <div class="form-group">
                                        <label>Produk</label>
                                        <input type="text" class="form-control" id="modal_produk" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-md-2">
                                    <div class="form-group">
                                        <label>ID Pelanggan</label>
                                        <input type="text" class="form-control" id="modal_idpel" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Tagihan -->
                    <div class="card mb-3">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">Data Tagihan</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>RP Biller Pokok</label>
                                        <input type="text" class="form-control" id="modal_rp_pokok" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>RP Biller Denda</label>
                                        <input type="text" class="form-control" id="modal_rp_denda" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>RP Fee Struk</label>
                                        <input type="text" class="form-control" id="modal_rp_fee_struk" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-md-2">
                                    <div class="form-group">
                                        <label>RP Amount Struk</label>
                                        <input type="text" class="form-control" id="modal_rp_amount_struk" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-md-2">
                                    <div class="form-group">
                                        <label>RP Biller Tag</label>
                                        <input type="text" class="form-control" id="modal_rp_tag" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Rekonsiliasi -->
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">Status Rekonsiliasi</h6>
                        </div>
                        <div class="card-body">
                            <!-- ID Partner (Channel) -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>ID Partner (Channel) <span class="text-danger">*</span></label>
                                        <select class="form-control" id="modal_channel" name="idpartner" required>
                                            <option value="">Pilih Channel</option>
                                            <option value="CHANNEL KON">CHANNEL KON</option>
                                            <option value="CHANNEL SYA">CHANNEL SYA</option>
                                            <option value="VA DIGITAL KON">VA DIGITAL KON</option>
                                            <option value="VA DIGITAL SYA">VA DIGITAL SYA</option>
                                            <option value="PPOB KON">PPOB KON</option>
                                            <option value="PPOB SYA">PPOB SYA</option>
                                            <option value="MITRACOMM">MITRACOMM</option>
                                            <option value="POS INDONESIA">POS INDONESIA</option>
                                            <option value="GO-PAY">GO-PAY</option>
                                            <option value="ARTAJASA">ARTAJASA</option>
                                            <option value="PDAM BARITO KUALA">PDAM BARITO KUALA</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Status Biller -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Status Biller <span class="text-danger">*</span></label>
                                        <div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input bg-danger" type="radio" name="status_biller" id="biller_sukses" value="1">
                                                <label class="form-check-label " for="biller_sukses">Sukses</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="status_biller" id="biller_pending" value="0">
                                                <label class="form-check-label" for="biller_pending">Pending</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="status_biller" id="biller_gagal" value="2">
                                                <label class="form-check-label" for="biller_gagal">Gagal</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Status Core -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Status Core <span class="text-danger">*</span></label>
                                        <div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="status_core" id="core_terdebet" value="1">
                                                <label class="form-check-label" for="core_terdebet">Terdebet</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="status_core" id="core_tidak_terdebet" value="0">
                                                <label class="form-check-label" for="core_tidak_terdebet">Tidak Terdebet</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Status Settlement -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Status Settlement <span class="text-danger">*</span></label>
                                        <div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="status_settlement" id="settlement_dilimpahkan" value="1">
                                                <label class="form-check-label" for="settlement_dilimpahkan">Dilimpahkan</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="status_settlement" id="settlement_gagal" value="9">
                                                <label class="form-check-label" for="settlement_gagal">Tidak Dilimpahkan</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fal fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-primary" onclick="saveDispute()">
                    <i class="fal fa-save"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>
