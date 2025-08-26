<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fal fa-filter"></i> Filter Data
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ current_url() }}">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="tanggal" class="form-label">Tanggal Rekonsiliasi</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                   value="{{ $tanggalData }}" required>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_status_biller" class="form-label">Status Biller</label>
                            <select class="form-control" id="filter_status_biller" name="status_biller">
                                <option value="">Semua Status</option>
                                <option value="0" @if(request()->getGet('status_biller') == '0') selected @endif>Pending</option>
                                <option value="1" @if(request()->getGet('status_biller') == '1') selected @endif>Sukses</option>
                                <option value="2" @if(request()->getGet('status_biller') == '2') selected @endif>Gagal</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_status_core" class="form-label">Status Core</label>
                            <select class="form-control" id="filter_status_core" name="status_core">
                                <option value="">Semua Status</option>
                                <option value="0" @if(request()->getGet('status_core') == '0') selected @endif>Tidak Terdebet</option>
                                <option value="1" @if(request()->getGet('status_core') == '1') selected @endif>Terdebet</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fal fa-search"></i> Tampilkan Data
                            </button>
                            <button type="button" class="btn btn-secondary ml-2" onclick="resetFilters()">
                                <i class="fal fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
