<!-- Modal Progress Batch Processing -->
<div class="modal fade" id="batchProgressModal" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #6c5190 0%, #553d73 100%); color: white; border-bottom: 1px solid #553d73;">
                <h5 class="modal-title">
                    <i class="fal fa-cloud-upload-alt me-2"></i>Mengirim ke API Gateway
                </h5>
            </div>
            <div class="modal-body">
                <div class="text-center py-4">
                    <div class="mb-4">
                        <i class="fal fa-spinner fa-spin" style="font-size: 3rem; color: #6c5190;"></i>
                    </div>
                    <h5>Memproses Transaksi Batch</h5>
                    <p class="text-muted">Kode Settle: <strong id="batch-kd-settle">-</strong></p>
                    <p class="text-muted">Sedang mengirim semua transaksi ke API Gateway...</p>
                    
                    <div class="mt-4">
                        <div class="alert alert-info">
                            <i class="fal fa-info-circle me-2"></i>
                            Mohon tunggu, proses ini dapat memakan waktu beberapa saat.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
