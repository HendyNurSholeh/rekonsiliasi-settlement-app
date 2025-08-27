<div class="row">
    <!-- Upload Data Agregator Detail -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-warning">
            <div class="card-header bg-warning-200">
                <h5 class="card-title">
                    <i class="fal fa-upload text-warning"></i>
                    Data Agregator Detail
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Upload file data transaksi detail dari agregator untuk tanggal {{ date('d/m/Y', strtotime($tanggalData ?? date('Y-m-d', strtotime('-1 day')))) }}
                    <br><span class="text-info"><strong>Format:</strong> .txt dengan delimiter ;</span>
                </p>
                <form id="form-agn-detail" enctype="multipart/form-data">
                    <input type="hidden" name="{{ csrf_token() }}" value="{{ csrf_hash() }}" />
                    <input type="hidden" name="tanggal_rekon" value="{{ $tanggalData }}" />
                    <input type="hidden" name="file_type" value="agn_detail" />
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="file-agn-detail" name="file" accept=".txt" required>
                        <label class="custom-file-label" for="file-agn-detail">Pilih file .txt...</label>
                    </div>
                    <button type="button" class="btn btn-primary btn-block mt-2" onclick="uploadFile('agn_detail')">
                        <i class="fal fa-upload"></i> Upload
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Upload Data Settlement Education -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-warning">
            <div class="card-header bg-warning-200">
                <h5 class="card-title">
                    <i class="fal fa-upload text-warning"></i>
                    Settlement Education
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Upload file settlement education untuk tanggal {{ date('d/m/Y', strtotime($tanggalData ?? date('Y-m-d', strtotime('-1 day')))) }}
                    <br><span class="text-info"><strong>Format:</strong> .txt dengan delimiter ;</span>
                </p>
                <form id="form-settle-edu" enctype="multipart/form-data">
                    <input type="hidden" name="{{ csrf_token() }}" value="{{ csrf_hash() }}" />
                    <input type="hidden" name="tanggal_rekon" value="{{ $tanggalData }}" />
                    <input type="hidden" name="file_type" value="settle_edu" />
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="file-settle-edu" name="file" accept=".txt" required>
                        <label class="custom-file-label" for="file-settle-edu">Pilih file .txt...</label>
                    </div>
                    <button type="button" class="btn btn-primary btn-block mt-2" onclick="uploadFile('settle_edu')">
                        <i class="fal fa-upload"></i> Upload
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Upload Data Settlement Pajak -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-warning">
            <div class="card-header bg-warning-200">
                <h5 class="card-title">
                    <i class="fal fa-upload text-warning"></i>
                    Settlement Pajak
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Upload file data settlement pajak untuk tanggal {{ date('d/m/Y', strtotime($tanggalData ?? date('Y-m-d', strtotime('-1 day')))) }}
                    <br><span class="text-info"><strong>Format:</strong> .txt dengan delimiter |</span>
                </p>    
                <form id="form-settle-pajak" enctype="multipart/form-data">
                    <input type="hidden" name="{{ csrf_token() }}" value="{{ csrf_hash() }}" />
                    <input type="hidden" name="tanggal_rekon" value="{{ $tanggalData }}" />
                    <input type="hidden" name="file_type" value="settle_pajak" />
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="file-settle-pajak" name="file" accept=".txt" required>
                        <label class="custom-file-label" for="file-settle-pajak">Pilih file .txt...</label>
                    </div>
                    <button type="button" class="btn btn-primary btn-block mt-2" onclick="uploadFile('settle_pajak')">
                        <i class="fal fa-upload"></i> Upload
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Upload Data M-Gate -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-warning">
            <div class="card-header bg-warning-200">
                <h5 class="card-title">
                    <i class="fal fa-upload text-warning"></i>
                    Data M-Gate
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Upload file transaksi M-Gate (Payment Gateway) untuk tanggal {{ date('d/m/Y', strtotime($tanggalData ?? date('Y-m-d', strtotime('-1 day')))) }} <span class="text-danger">*Wajib</span>
                    <br><span class="text-info"><strong>Format:</strong> .csv dengan delimiter ;</span>
                </p>
                <form id="form-mgate" enctype="multipart/form-data">
                    <input type="hidden" name="{{ csrf_token() }}" value="{{ csrf_hash() }}" />
                    <input type="hidden" name="tanggal_rekon" value="{{ $tanggalData }}" />
                    <input type="hidden" name="file_type" value="mgate" />
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="file-mgate" name="file" accept=".csv" required>
                        <label class="custom-file-label" for="file-mgate">Pilih file .csv...</label>
                    </div>
                    <button type="button" class="btn btn-primary btn-block mt-2" onclick="uploadFile('mgate')">
                        <i class="fal fa-upload"></i> Upload
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
