<div class="modal fade" id="konfirmasiModal" tabindex="-1" role="dialog" aria-labelledby="konfirmasiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="konfirmasiModalLabel">
                    <i class="fal fa-shield-check text-primary"></i>
                    Konfirmasi Saldo Rekening Escrow
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fal fa-info-circle"></i>
                    <strong>Konfirmasi Saldo Escrow!</strong> Pastikan saldo rekening escrow sudah sesuai sebelum melakukan konfirmasi.
                </div>
                <p id="konfirmasiMessage" class="mb-3"></p>
                <p class="text-muted small">
                    <i class="fal fa-lightbulb"></i>
                    Pastikan saldo fisik di rekening escrow sudah sesuai dengan nominal yang ditampilkan.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fal fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-primary" id="confirmUpdateBtn">
                    <i class="fal fa-check"></i> Ya, Saldo Sudah Sesuai
                </button>
            </div>
        </div>
    </div>
</div>