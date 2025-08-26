@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ base_url('css/rekon/process/indirect_jurnal/rekap_indirect_jurnal.css') }}">
@endpush

@section('content')
    <div class="subheader">
        <h1 class="subheader-title">
            <i class="fal fa-chart-bar"></i> {{ $title }}
            <small>Rekap transaksi indirect jurnal untuk tanggal {{ date('d/m/Y', strtotime($tanggalData)) }}</small>
        </h1>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fal fa-info-circle"></i>
                <strong>Rekap Tx Indirect Jurnal</strong> 
                <br>Menampilkan rekap transaksi indirect jurnal dengan analisis selisih antara data sukses dan core.
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    @include('rekon.process.indirect_jurnal.rekap_indirect_jurnal._filter')

    <!-- Data Table -->
    @include('rekon.process.indirect_jurnal.rekap_indirect_jurnal._data_table')

    <!-- Modal Konfirmasi -->
    @include('rekon.process.indirect_jurnal.rekap_indirect_jurnal._modal')
@endsection

@push('scripts')
    <script>
    window.appConfig = {
        baseUrl: "{{ base_url() }}",
        tanggalData: "{{ $tanggalData }}",
        csrfToken: "{{ csrf_token() }}"
    };
    </script>
    <script src="{{ base_url('js/rekon/process/indirect_jurnal/rekap_indirect_jurnal.js') }}"></script>
@endpush