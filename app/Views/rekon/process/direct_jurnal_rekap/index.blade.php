@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ base_url('css/rekon/process/direct_jurnal_rekap.css') }}">
@endpush

@section('content')
    <div class="subheader">
        <h1 class="subheader-title">
            <i class="fal fa-file-invoice"></i> {{ $title }}
            <small>Rekap transaksi direct jurnal untuk tanggal {{ date('d/m/Y', strtotime($tanggalData)) }}</small>
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
    @include('rekon.process.direct_jurnal_rekap._filter')

    <!-- Data Table -->
    @include('rekon.process.direct_jurnal_rekap._data_table')
@endsection

@push('scripts')
    <script src="{{ base_url('js/rekon/process/direct_jurnal_rekap.js') }}"></script>
@endpush
