<form id="processForm" action="{{ site_url('rekon/create') }}" method="POST">
    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
    <input type="hidden" id="reset_confirmed" name="reset_confirmed" value="false" />
    
    <div class="form-group">
        <label for="tanggal_rekon" class="form-label">
            <strong>Tanggal Rekonsiliasi</strong> 
            <span class="text-danger">*</span>
        </label>
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fal fa-calendar"></i></span>
            </div>
            <input type="date" 
                   class="form-control" 
                   id="tanggal_rekon" 
                   name="tanggal_rekon" 
                   value="{{ session('existing_date') ?? ($defaultDate ?? date('Y-m-d', strtotime('-1 day'))) }}"
                   max="{{ date('Y-m-d') }}" 
                   required>
        </div>
        <div class="help-block">
            <i class="fal fa-info-circle text-info"></i> 
            Pilih tanggal yang akan direkonsiliasi. Default: <strong>{{ isset($defaultDate) ? date('d/m/Y', strtotime($defaultDate)) : date('d/m/Y', strtotime('-1 day')) }}</strong>
        </div>
        <!-- Date status indicator -->
        <div id="dateStatus" class="mt-2" style="display: none;"></div>
    </div>
    
    <div class="form-group mt-4">
        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
            <i class="fal fa-rocket"></i> Buat Proses Rekonsiliasi
        </button>
        <a href="{{ site_url('dashboard') }}" class="btn btn-secondary btn-lg ml-2">
            <i class="fal fa-arrow-left"></i> Kembali
        </a>
    </div>
</form>