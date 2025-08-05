@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ base_url('css/rekon/process/direct_jurnal_rekap.css') }}">
@endpush

@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        <i class="fal fa-file-invoice"></i> {{ $title }}
        <small>Rekap transaksi direct jurnal untuk tanggal {{ date('d/m/Y', strtotime($tanggalRekon)) }}</small>
    </h1>
</div>
<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fal fa-info-circle"></i>
            <strong>Rekap Tx Direct Jurnal</strong> 
            <br>Menampilkan rekap transaksi yang memerlukan direct jurnal dari sistem.
        </div>
    </div>
</div>
<!-- Filter Section -->
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
                        <div class="col-md-4">
                            <label for="tanggal" class="form-label">Tanggal Rekonsiliasi</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                   value="{{ $tanggalRekon }}" required>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">
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
<!-- Data Table -->
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
@endsection
@push('scripts')
    <script>
    function resetFilters() {
        // Remove 'tanggal' parameter from URL and redirect
        const url = new URL(window.location);
        url.searchParams.delete('tanggal');
        window.location.href = url.pathname + url.search;
    }
    </script>
@endpush
