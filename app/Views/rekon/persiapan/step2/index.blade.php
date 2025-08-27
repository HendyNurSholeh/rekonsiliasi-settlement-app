@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ base_url('css/rekon/persiapan/step2.css') }}">
@endpush

@section('content')
    <div class="subheader">
        <h1 class="subheader-title">
            <i class="fal fa-check-square"></i> {{ $title }}
            <small>Verifikasi isi data untuk tanggal {{ date('d/m/Y', strtotime($tanggalData)) }}</small>
        </h1>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fal fa-info-circle"></i>
                <strong>Tahap 2 - Verifikasi Isi Data</strong> 
                <br>Pastikan semua produk telah termapping dengan benar sebelum memulai proses rekonsiliasi.
            </div>
        </div>
    </div>

    <!-- Data Summary Cards -->
    @include('rekon.persiapan.step2._cards')

    <!-- Detailed Validation Results -->
    @include('rekon.persiapan.step2._validation_results')

    <div class="row">
        <div class="col-12">
            <!-- Product Mapping Statistics -->
            @include('rekon.persiapan.step2._mapping_cards')
            <!-- Product Mapping Table -->
            @include('rekon.persiapan.step2._data_table')
        </div>
    </div>

    <!-- Action Buttons -->
    @include('rekon.persiapan.step2._action_buttons')
@endsection

@push('scripts')
    <script>
    window.appConfig = {
        baseUrl: "{{ base_url() }}",
        tanggalData: "{{ $tanggalData }}",
        csrfToken: "{{ csrf_token() }}",
        csrfHash: "{{ csrf_hash() }}"
    };
    </script>
    <script src="{{ base_url('js/rekon/persiapan/step2.js') }}"></script>
@endpush

