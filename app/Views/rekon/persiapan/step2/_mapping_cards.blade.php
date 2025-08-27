<div class="row mb-3">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-primary h-100">
            <div class="card-header bg-primary text-white py-2">
                <h6 class="card-title mb-0">
                    <i class="fal fa-cube"></i> Total Produk
                </h6>
            </div>
            <div class="card-body text-center py-3">
                <h3 class="text-primary mb-1" id="totalProducts">{{ $mappingStats['total_products'] ?? 0 }}</h3>
                <small class="text-muted">Produk Ditemukan</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-success h-100">
            <div class="card-header bg-success text-white py-2">
                <h6 class="card-title mb-0">
                    <i class="fal fa-check-circle"></i> Sudah Mapping
                </h6>
            </div>
            <div class="card-body text-center py-3">
                <h3 class="text-success mb-1" id="mappedProducts">{{ $mappingStats['mapped_products'] ?? 0 }}</h3>
                <small class="text-muted">Produk Termapping</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-danger h-100">
            <div class="card-header bg-danger text-white py-2">
                <h6 class="card-title mb-0">
                    <i class="fal fa-exclamation-circle"></i> Belum Mapping
                </h6>
            </div>
            <div class="card-body text-center py-3">
                <h3 class="text-danger mb-1" id="unmappedProducts">{{ $mappingStats['unmapped_products'] ?? 0 }}</h3>
                <small class="text-muted">Produk Belum Mapping</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-info h-100">
            <div class="card-header bg-info text-white py-2">
                <h6 class="card-title mb-0">
                    <i class="fal fa-percentage"></i> Persentase
                </h6>
            </div>
            <div class="card-body text-center py-3">
                <h3 class="text-info mb-1" id="mappingPercentage">{{ number_format($mappingStats['mapping_percentage'] ?? 0, 1) }}%</h3>
                <small class="text-muted">Mapping Selesai</small>
            </div>
        </div>
    </div>
</div>