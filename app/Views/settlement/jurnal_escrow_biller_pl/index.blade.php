@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ base_url('css/settlement/settlement.css') }}">
    <link rel="stylesheet" href="{{ base_url('css/settlement/jurnal_escrow_biller_pl.css') }}">
@endpush

@section('content')
    <div class="subheader">
        <h1 class="subheader-title">
            <i class="fal fa-exchange-alt"></i> {{ $title }}
            <small>Jurnal Escrow to Biller PL untuk tanggal {{ date('d/m/Y', strtotime($tanggalData)) }}</small>
        </h1>
    </div>

    <!-- Filter Section -->
    @include('settlement.jurnal_escrow_biller_pl._filter')

    <!-- Data Table -->
    @include('settlement.jurnal_escrow_biller_pl._data_table')

    <!-- Batch Progress Modal -->
    @include('settlement.jurnal_escrow_biller_pl._batch_progress_modal')
@endsection

@push('scripts')
    <script>
    window.appConfig = {
        baseUrl: "{{ base_url() }}",
        tanggalData: "{{ $tanggalData }}",
        csrfToken: "{{ csrf_token() }}"
    };
    </script>
    <script src="{{ base_url('js/settlement/jurnal_escrow_biller_pl/index.js') }}"></script>
    <script src="{{ base_url('js/settlement/jurnal_escrow_biller_pl/datatable.js') }}"></script>
    <script src="{{ base_url('js/settlement/jurnal_escrow_biller_pl/batch-process.js') }}"></script>
@endpush

