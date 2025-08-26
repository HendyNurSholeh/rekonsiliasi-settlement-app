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
                                   value="{{ $tanggalRekon }}" required>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_selisih" class="form-label">Filter Selisih</label>
                            <select class="form-control" id="filter_selisih" name="filter_selisih">
                                <option value="">Semua Data</option>
                                <option value="ada_selisih" @if(request()->getGet('filter_selisih') == 'ada_selisih') selected @endif>Ada Selisih</option>
                                <option value="tidak_ada_selisih" @if(request()->getGet('filter_selisih') == 'tidak_ada_selisih') selected @endif>Tidak Ada Selisih</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
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
