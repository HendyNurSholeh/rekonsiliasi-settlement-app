@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ base_url('css/rekon/process/detail_vs_rekap.css') }}">
@endpush

@section('content')
    <div class="subheader">
        <h1 class="subheader-title">
            <i class="fal fa-chart-line"></i> {{ $title }}
            <small>Perbandingan data detail vs rekap untuk tanggal {{ date('d/m/Y', strtotime($tanggalRekon)) }}</small>
        </h1>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fal fa-info-circle"></i>
                <strong>Laporan Detail vs Rekap</strong> 
                <br>Menampilkan perbandingan data antara detail transaksi dengan data rekap settlement.
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    @include('rekon.process.detail_vs_rekap._filter')

    <!-- Statistics Section -->
    @include('rekon.process.detail_vs_rekap._statistics')

    <!-- Data Table -->
    @include('rekon.process.detail_vs_rekap._data_table')
@endsection

@push('scripts')
    <script>
    window.appConfig = {
        baseUrl: "{{ base_url() }}",
        tanggalData: "{{ $tanggalData }}",
        csrfToken: "{{ csrf_token() }}"
    };
    </script>
    <script src="{{ base_url('js/rekon/process/detail_vs_rekap.js') }}"></script>
@endpush

