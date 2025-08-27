<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fal fa-table text-primary"></i> Data Product Mapping (v_cek_group_produk)
        </h5>
        <button type="button" class="btn btn-outline-primary btn-sm" id="btnProsesUlang" onclick="prosesUlangPersiapan()" title="Jalankan procedure proses_data_upload untuk merefresh data mapping dan statistik">
            <i class="fal fa-sync-alt"></i> Proses Ulang Persiapan
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-striped table-hover table-sm" id="mappingTable">
                <thead class="thead-light sticky-top">
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th style="width: 100px;">Source</th>
                        <th>Produk</th>
                        <th>Nama Group</th>
                        <th style="width: 120px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @if(!empty($mappingData) && count($mappingData) > 0)
                        @foreach($mappingData as $index => $item)
                        <tr class="{{ empty($item['NAMA_GROUP']) ? 'table-warning' : '' }}">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <span class="badge {{ $item['SOURCE'] === 'DETAIL' ? 'badge-primary' : 'badge-info' }}">
                                    {{ $item['SOURCE'] ?? '' }}
                                </span>
                            </td>
                            <td><code class="font-weight-bold">{{ $item['PRODUK'] ?? '' }}</code></td>
                            <td>
                                @if(!empty($item['NAMA_GROUP']))
                                    <span class="badge badge-success">{{ $item['NAMA_GROUP'] }}</span>
                                @else
                                    <span class="badge badge-danger">Belum Mapping</span>
                                @endif
                            </td>
                            <td>
                                @if(!empty($item['NAMA_GROUP']))
                                    <i class="fal fa-check-circle text-success"></i> Mapped
                                @else
                                    <i class="fal fa-exclamation-triangle text-warning"></i> Not Mapped
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                <i class="fal fa-inbox"></i> Tidak ada data ditemukan
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>