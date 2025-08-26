<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fal fa-table"></i> Data Rekap Direct Jurnal
                </h5>
            </div>
            <div class="card-body">
                @if(!empty($rekapData))
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="rekapTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>No</th>
                                    @foreach(array_keys($rekapData[0]) as $column)
                                        @if($column != 'v_tanggal_rekon')
                                            <th>{{ ucwords(str_replace('_', ' ', $column)) }}</th>
                                        @endif
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rekapData as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    @foreach($item as $key => $value)
                                        @if($key != 'v_tanggal_rekon')
                                            <td class="{{ (strpos(strtolower($key), 'selisih') !== false && $value != 0) ? 'text-danger fw-bold' : '' }}">
                                                @if(is_numeric($value))
                                                    {{ number_format($value) }}
                                                @else
                                                    {{ $value }}
                                                @endif
                                            </td>
                                        @endif
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fal fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak ada data ditemukan</h5>
                        <p class="text-muted">Silakan pilih tanggal rekonsiliasi untuk menampilkan data.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>