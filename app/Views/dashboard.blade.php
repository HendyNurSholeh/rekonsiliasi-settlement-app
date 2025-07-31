@extends('layouts.app')
@section('content')
    <ol class="breadcrumb page-breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ site_url('dashboard') }}">
                <i class="fal fa-home mr-1"></i> Home
            </a>
        </li>
        <li class="breadcrumb-item active">{{ $title }}</li>
        <li class="position-absolute pos-right d-none d-sm-block">{{ $today }}</li>
    </ol>

    <div class="subheader">
        <h1 class="subheader-title">
            <i class='subheader-icon fal fa-balance-scale'></i> Rekonsiliasi <span class='fw-300'>Dashboard</span>
        </h1>
        <div class="subheader-block d-lg-flex align-items-center">
            <div class="d-flex mr-4">
                <div class="mr-2">
                    <span class="peity-donut"
                        data-peity="{ &quot;fill&quot;: [&quot;#28a745&quot;, &quot;#d4edda&quot;],  &quot;innerRadius&quot;: 14, &quot;radius&quot;: 20 }">8/10</span>
                </div>
                <div>
                    <label class="fs-sm mb-0 mt-2 mt-md-0">Data Matched</label>
                    <h4 class="font-weight-bold mb-0">85.5%</h4>
                </div>
            </div>
            <div class="d-flex mr-0">
                <div class="mr-2">
                    <span class="peity-donut"
                        data-peity="{ &quot;fill&quot;: [&quot;#007bff&quot;, &quot;#b3d9ff&quot;],  &quot;innerRadius&quot;: 14, &quot;radius&quot;: 20 }">7/10</span>
                </div>
                <div>
                    <label class="fs-sm mb-0 mt-2 mt-md-0">Total Transaksi</label>
                    <h4 class="font-weight-bold mb-0">2,847</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row">
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-success-300 rounded overflow-hidden position-relative text-white mb-g">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        1,247
                        <small class="m-0 l-h-n">transaksi matched</small>
                    </h3>
                </div>
                <i class="fal fa-check-circle position-absolute pos-right pos-bottom opacity-15 mb-n1 mr-n1"
                    style="font-size:6rem"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-warning-400 rounded overflow-hidden position-relative text-white mb-g">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        Rp 2.8M
                        <small class="m-0 l-h-n">Total Settlement</small>
                    </h3>
                </div>
                <i class="fal fa-money-bill-alt position-absolute pos-right pos-bottom opacity-15  mb-n1 mr-n4"
                    style="font-size: 6rem;"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-danger-200 rounded overflow-hidden position-relative text-white mb-g">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        156
                        <small class="m-0 l-h-n">Unmatched Records</small>
                    </h3>
                </div>
                <i class="fal fa-exclamation-triangle position-absolute pos-right pos-bottom opacity-15 mb-n5 mr-n6"
                    style="font-size: 8rem;"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-info-200 rounded overflow-hidden position-relative text-white mb-g">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        98.5%
                        <small class="m-0 l-h-n">Proses Selesai</small>
                    </h3>
                </div>
                <i class="fal fa-chart-line position-absolute pos-right pos-bottom opacity-15 mb-n1 mr-n4"
                    style="font-size: 6rem;"></i>
            </div>
        </div>
    </div>

    <!-- Dashboard Panels -->
    <div class="row">
        <div class="col-lg-12">
            <div id="panel-1" class="panel">
                <div class="panel-hdr">
                    <h2>Grafik Rekonsiliasi Harian</h2>
                </div>
                <div class="panel-container show">
                    <div class="panel-content bg-subtlelight-fade">
                        <div id="js-checkbox-toggles" class="d-flex mb-3">
                            <div class="custom-control custom-switch mr-2">
                                <input type="checkbox" class="custom-control-input" name="gra-0" id="gra-0" checked="checked">
                                <label class="custom-control-label" for="gra-0">Transaksi Matched</label>
                            </div>
                            <div class="custom-control custom-switch mr-2">
                                <input type="checkbox" class="custom-control-input" name="gra-1" id="gra-1" checked="checked">
                                <label class="custom-control-label" for="gra-1">Transaksi Unmatched</label>
                            </div>
                            <div class="custom-control custom-switch mr-2">
                                <input type="checkbox" class="custom-control-input" name="gra-2" id="gra-2" checked="checked">
                                <label class="custom-control-label" for="gra-2">Total Settlement</label>
                            </div>
                        </div>
                        <div id="flot-toggles" class="w-100 mt-4" style="height: 300px"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div id="panel-2" class="panel panel-locked" data-panel-sortable data-panel-collapsed data-panel-close>
                <div class="panel-hdr">
                    <h2>Status <span class="fw-300"><i>Settlement</i></span></h2>
                </div>
                <div class="panel-container show">
                    <div class="panel-content position-relative">
                        <div class="p-1 position-absolute pos-right pos-top mt-3 mr-3 z-index-cloud d-flex align-items-center justify-content-center">
                            <div class="border-faded border-top-0 border-left-0 border-bottom-0 py-2 pr-4 mr-3 hidden-sm-down">
                                <div class="text-right fw-500 l-h-n d-flex flex-column">
                                    <div class="h3 m-0 d-flex align-items-center justify-content-end">
                                        <div class='icon-stack mr-2'>
                                            <i class="base base-7 icon-stack-3x opacity-100 color-success-600"></i>
                                            <i class="base base-7 icon-stack-2x opacity-100 color-success-500"></i>
                                            <i class="fal fa-check icon-stack-1x opacity-100 color-white"></i>
                                        </div>
                                        98.5% Match
                                    </div>
                                    <span class="m-0 fs-xs text-muted">Tingkat keberhasilan rekonsiliasi settlement hari ini</span>
                                </div>
                            </div>
                            <div class="js-easy-pie-chart color-info-400 position-relative d-inline-flex align-items-center justify-content-center"
                                data-percent="85" data-piesize="95" data-linewidth="10" data-scalelength="5">
                                <div class="js-easy-pie-chart color-success-400 position-relative position-absolute pos-left pos-right pos-top pos-bottom d-flex align-items-center justify-content-center"
                                    data-percent="98" data-piesize="60" data-linewidth="5" data-scalelength="1" data-scalecolor="#fff">
                                    <div class="position-absolute pos-top pos-left pos-right pos-bottom d-flex align-items-center justify-content-center fw-500 fs-xl text-dark">
                                        98%</div>
                                </div>
                            </div>
                        </div>
                        <div id="flot-area" style="width:100%; height:300px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div id="panel-3" class="panel panel-locked" data-panel-sortable data-panel-collapsed data-panel-close>
                <div class="panel-hdr">
                    <h2>Statistik <span class="fw-300"><i>Harian</i></span></h2>
                </div>
                <div class="panel-container show">
                    <div class="panel-content position-relative">
                        <div class="pb-5 pt-3">
                            <div class="row">
                                <div class="col-6 col-xl-3 d-sm-flex align-items-center">
                                    <div class="p-2 mr-3 bg-success-200 rounded">
                                        <span class="peity-bar" data-peity="{&quot;fill&quot;: [&quot;#fff&quot;], &quot;width&quot;: 27, &quot;height&quot;: 27 }">8,9,7,9,8</span>
                                    </div>
                                    <div>
                                        <label class="fs-sm mb-0">Match Rate</label>
                                        <h4 class="font-weight-bold mb-0">85.2%</h4>
                                    </div>
                                </div>
                                <div class="col-6 col-xl-3 d-sm-flex align-items-center">
                                    <div class="p-2 mr-3 bg-info-300 rounded">
                                        <span class="peity-bar" data-peity="{&quot;fill&quot;: [&quot;#fff&quot;], &quot;width&quot;: 27, &quot;height&quot;: 27 }">5,7,9,8,6</span>
                                    </div>
                                    <div>
                                        <label class="fs-sm mb-0">Total Files</label>
                                        <h4 class="font-weight-bold mb-0">12</h4>
                                    </div>
                                </div>
                                <div class="col-6 col-xl-3 d-sm-flex align-items-center">
                                    <div class="p-2 mr-3 bg-warning-300 rounded">
                                        <span class="peity-bar" data-peity="{&quot;fill&quot;: [&quot;#fff&quot;], &quot;width&quot;: 27, &quot;height&quot;: 27 }">3,2,1,2,1</span>
                                    </div>
                                    <div>
                                        <label class="fs-sm mb-0">Pending</label>
                                        <h4 class="font-weight-bold mb-0">3</h4>
                                    </div>
                                </div>
                                <div class="col-6 col-xl-3 d-sm-flex align-items-center">
                                    <div class="p-2 mr-3 bg-primary-500 rounded">
                                        <span class="peity-bar" data-peity="{&quot;fill&quot;: [&quot;#fff&quot;], &quot;width&quot;: 27, &quot;height&quot;: 27 }">6,8,7,9,8</span>
                                    </div>
                                    <div>
                                        <label class="fs-sm mb-0">Processed</label>
                                        <h4 class="font-weight-bold mb-0">9</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="flotVisit" style="width:100%; height:208px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div id="panel-4" class="panel">
                <div class="panel-hdr">
                    <h2>Recent <span class="fw-300"><i>Settlement Records</i></span></h2>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <table id="dt-basic-example" class="table table-bordered table-hover table-striped w-100">
                            <thead class="bg-primary-200">
                                <tr>
                                    <th>Settlement ID</th>
                                    <th>Tanggal</th>
                                    <th>Merchant</th>
                                    <th>Produk</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Match Rate</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>STL-20250131-001</td>
                                    <td>31-01-25</td>
                                    <td>Bank ABC</td>
                                    <td>Education</td>
                                    <td>Rp 150,000</td>
                                    <td><span class="badge badge-success">Matched</span></td>
                                    <td>100%</td>
                                    <td><button class="btn btn-sm btn-primary">Detail</button></td>
                                </tr>
                                <tr>
                                    <td>STL-20250131-002</td>
                                    <td>31-01-25</td>
                                    <td>Bank XYZ</td>
                                    <td>Pajak</td>
                                    <td>Rp 250,000</td>
                                    <td><span class="badge badge-warning">Partial</span></td>
                                    <td>85%</td>
                                    <td><button class="btn btn-sm btn-warning">Review</button></td>
                                </tr>
                                <tr>
                                    <td>STL-20250131-003</td>
                                    <td>31-01-25</td>
                                    <td>M-Gate</td>
                                    <td>Payment</td>
                                    <td>Rp 500,000</td>
                                    <td><span class="badge badge-success">Matched</span></td>
                                    <td>100%</td>
                                    <td><button class="btn btn-sm btn-primary">Detail</button></td>
                                </tr>
                                <tr>
                                    <td>STL-20250131-004</td>
                                    <td>31-01-25</td>
                                    <td>Bank DEF</td>
                                    <td>Education</td>
                                    <td>Rp 175,000</td>
                                    <td><span class="badge badge-danger">Unmatched</span></td>
                                    <td>0%</td>
                                    <td><button class="btn btn-sm btn-danger">Investigate</button></td>
                                </tr>
                                <tr>
                                    <td>STL-20250131-005</td>
                                    <td>31-01-25</td>
                                    <td>Bank GHI</td>
                                    <td>Pajak</td>
                                    <td>Rp 300,000</td>
                                    <td><span class="badge badge-success">Matched</span></td>
                                    <td>100%</td>
                                    <td><button class="btn btn-sm btn-primary">Detail</button></td>
                                </tr>
                                <tr>
                                    <td>STL-20250131-006</td>
                                    <td>31-01-25</td>
                                    <td>Bank JKL</td>
                                    <td>Education</td>
                                    <td>Rp 125,000</td>
                                    <td><span class="badge badge-success">Matched</span></td>
                                    <td>100%</td>
                                    <td><button class="btn btn-sm btn-primary">Detail</button></td>
                                </tr>
                                <tr>
                                    <td>STL-20250131-007</td>
                                    <td>31-01-25</td>
                                    <td>M-Gate</td>
                                    <td>Payment</td>
                                    <td>Rp 750,000</td>
                                    <td><span class="badge badge-warning">Partial</span></td>
                                    <td>92%</td>
                                    <td><button class="btn btn-sm btn-warning">Review</button></td>
                                </tr>
                                <tr>
                                    <td>STL-20250131-008</td>
                                    <td>31-01-25</td>
                                    <td>Bank MNO</td>
                                    <td>Pajak</td>
                                    <td>Rp 200,000</td>
                                    <td><span class="badge badge-success">Matched</span></td>
                                    <td>100%</td>
                                    <td><button class="btn btn-sm btn-primary">Detail</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
