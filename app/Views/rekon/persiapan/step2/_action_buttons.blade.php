<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-2">Proses Selanjutnya</h6>
                        @if(($mappingStats['unmapped_products'] ?? 0) > 0)
                            <p class="text-danger mb-0">
                                <i class="fal fa-exclamation-triangle"></i>
                                Masih ada <strong>{{ $mappingStats['unmapped_products'] ?? 0 }} produk</strong> yang belum termapping. 
                                Pastikan semua produk telah termapping terlebih dahulu.
                            </p>
                        @else
                            <p class="text-success mb-0">
                                <i class="fal fa-check-circle"></i>
                                Semua produk telah termapping dengan benar. Siap untuk memulai rekonsiliasi.
                            </p>
                        @endif
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary me-2" onclick="window.location.href=`{{ base_url('rekon') }}`">
                            <i class="fal fa-arrow-left"></i> Kembali ke Step 1
                        </button>
                        @if(($mappingStats['unmapped_products'] ?? 0) > 0)
                            <button type="button" class="btn btn-primary" disabled id="btnMulaiRekonsiliasi" title="Masih ada produk yang belum mapping">
                                <i class="fal fa-exclamation-triangle"></i> Produk Belum Mapping Semua
                            </button>
                        @else
                            <button type="button" class="btn btn-primary" id="btnMulaiRekonsiliasi" onclick="startReconciliation()">
                                <i class="fal fa-play"></i> Mulai Rekonsiliasi
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>