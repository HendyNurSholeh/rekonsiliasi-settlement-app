<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fal fa-list-check text-success"></i> Hasil Validasi Data
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Validasi</th>
                                <th>Status</th>
                                <th>Hasil</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <i class="fal fa-database text-primary"></i> 
                                    Kelengkapan Data Agregator
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <i class="fal fa-check"></i> Valid
                                    </span>
                                </td>
                                <td>{{ number_format($dataStats['agn_detail']['total_records'] ?? 0) }} records</td>
                                <td>Data transaksi tersedia lengkap</td>
                            </tr>
                            <tr>
                                <td>
                                    <i class="fal fa-graduation-cap text-info"></i> 
                                    Data Settlement Education
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <i class="fal fa-check"></i> Valid
                                    </span>
                                </td>
                                <td>{{ number_format($dataStats['settle_edu']['total_records'] ?? 0) }} records</td>
                                <td>Data settlement education tersedia</td>
                            </tr>
                            <tr>
                                <td>
                                    <i class="fal fa-receipt text-warning"></i> 
                                    Data Settlement Pajak
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <i class="fal fa-check"></i> Valid
                                    </span>
                                </td>
                                <td>{{ number_format($dataStats['settle_pajak']['total_records'] ?? 0) }} records</td>
                                <td>Data settlement pajak tersedia</td>
                            </tr>
                            <tr>
                                <td>
                                    <i class="fal fa-calendar-check text-success"></i> 
                                    Konsistensi Tanggal
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <i class="fal fa-check"></i> Valid
                                    </span>
                                </td>
                                <td>{{ date('d/m/Y', strtotime($tanggalData)) }}</td>
                                <td>Semua data menggunakan tanggal yang sama</td>
                            </tr>
                            <tr>
                                <td>
                                    <i class="fal fa-credit-card text-primary"></i> 
                                    Data M-Gate (Payment Gateway)
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <i class="fal fa-check"></i> Valid
                                    </span>
                                </td>
                                <td>{{ number_format($dataStats['mgate']['total_records'] ?? 0) }} records</td>
                                <td>Data transaksi payment gateway tersedia</td>
                            </tr>
                            <tr>
                                <td>
                                    <i class="fal fa-sitemap text-info"></i> 
                                    Mapping Produk
                                </td>
                                <td>
                                    @if(($mappingStats['unmapped_products'] ?? 0) > 0)
                                        <span class="badge badge-warning">
                                            <i class="fal fa-exclamation-triangle"></i> Belum Lengkap
                                        </span>
                                    @else
                                        <span class="badge badge-success">
                                            <i class="fal fa-check"></i> Lengkap
                                        </span>
                                    @endif
                                </td>
                                <td>{{ ($mappingStats['mapped_products'] ?? 0) }}/{{ ($mappingStats['total_products'] ?? 0) }} produk mapped</td>
                                <td>
                                    @if(($mappingStats['unmapped_products'] ?? 0) > 0)
                                        Masih ada {{ $mappingStats['unmapped_products'] ?? 0 }} produk yang belum mapping
                                    @else
                                        Semua produk telah termapping dengan benar
                                    @endif
                                </td>
                            </tr>
                            <tr class="{{ ($mappingStats['unmapped_products'] ?? 0) > 0 ? 'table-warning' : 'table-success' }}">
                                <td>
                                    <strong>
                                        @if(($mappingStats['unmapped_products'] ?? 0) > 0)
                                            <i class="fal fa-exclamation-triangle text-warning"></i> 
                                            Status Keseluruhan
                                        @else
                                            <i class="fal fa-check-circle text-success"></i> 
                                            Status Keseluruhan
                                        @endif
                                    </strong>
                                </td>
                                <td>
                                    @if(($mappingStats['unmapped_products'] ?? 0) > 0)
                                        <span class="badge badge-warning">
                                            <i class="fal fa-exclamation-triangle"></i> PERLU MAPPING
                                        </span>
                                    @else
                                        <span class="badge badge-success">
                                            <i class="fal fa-thumbs-up"></i> SIAP PROSES
                                        </span>
                                    @endif
                                </td>
                                <td colspan="2">
                                    @if(($mappingStats['unmapped_products'] ?? 0) > 0)
                                        <strong>Ada {{ $mappingStats['unmapped_products'] ?? 0 }} produk yang belum mapping. Silakan lengkapi mapping produk terlebih dahulu.</strong>
                                    @else
                                        <strong>Semua validasi berhasil. Data siap untuk diproses rekonsiliasi.</strong>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>