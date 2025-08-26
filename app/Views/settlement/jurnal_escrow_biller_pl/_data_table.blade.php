<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fal fa-table"></i> Data Jurnal Escrow to Biller PL
                    </h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary" onclick="toggleAllRows(true)" title="Expand semua detail">
                            <i class="fal fa-expand-arrows-alt"></i> Expand All
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="toggleAllRows(false)" title="Collapse semua detail">
                            <i class="fal fa-compress-arrows-alt"></i> Collapse All
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="refreshTableData()" title="Refresh data tabel">
                            <i class="fal fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-sm" id="jurnalEscrowBillerPlTable">
                        <thead class="thead-light">
                            <tr>
                                <th width="5%">Detail</th>
                                <th width="40%">Kode Settle</th>
                                <th width="50%">Nama Produk</th>
                                <th width="5%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data akan dimuat via AJAX dengan struktur parent-child -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
