<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ site_url('rekon') }}" class="btn btn-secondary">
                        <i class="fal fa-arrow-left"></i> Kembali ke Daftar
                    </a>
                    
                    <div class="upload-summary">
                        <span class="text-muted">Status Upload:</span>
                        <span class="ml-2">
                            <strong class="text-warning" id="upload-count">0/4 files</strong>
                        </span>
                    </div>
                    
                    <div class="action-buttons">
                        <button class="btn btn-success mr-2" onclick="validateAndProceed()" disabled id="btn-validate">
                            <i class="fal fa-check-circle"></i> Validasi & Lanjutkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>