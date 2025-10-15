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
                            <label for="tanggal" class="form-label">Tanggal Settlement</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                   value="{{ $tanggalRekon }}" required>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_status_approve" class="form-label">Status Approval</label>
                            <select class="form-control" id="filter_status_approve" name="status_approve">
                                <option value="">Semua Status</option>
                                <option value="pending" @if($statusApprove === 'pending') selected @endif>Pending</option>
                                <option value="1" @if($statusApprove === '1') selected @endif>Disetujui</option>
                                <option value="9" @if($statusApprove === '9') selected @endif>Ditolak</option>
                                <option value="-1" @if($statusApprove === '-1') selected @endif>Net Amount Beda</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
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