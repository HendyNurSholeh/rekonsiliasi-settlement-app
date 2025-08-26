<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fal fa-filter"></i> Filter Data
                </h5>
            </div>
            <div class="card-body">
                <form id="form-filter" method="GET" action="{{ current_url() }}">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="tanggal" class="form-label">Tanggal Rekonsiliasi <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                   value="{{ $tanggalRekon }}" required>
                        </div>
                        <div class="col-md-3">
                            <label for="status_biller" class="form-label">Status Biller</label>
                            <select class="form-control" id="status_biller" name="status_biller">
                                <option value="">Semua Status</option>
                                <option value="0">Pending</option>
                                <option value="1">Sukses</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="status_core" class="form-label">Status Core</label>
                            <select class="form-control" id="status_core" name="status_core">
                                <option value="">Semua Status</option>
                                <option value="0">Tidak Terdebet</option>
                                <option value="1">Terdebet</option>
                                <option value="2">Belum Verif</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fal fa-search"></i> Tampilkan Data
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                                <i class="fal fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>