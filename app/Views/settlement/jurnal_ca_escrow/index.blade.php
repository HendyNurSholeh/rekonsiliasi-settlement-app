@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ base_url('css/settlement/settlement.css') }}">
    <link rel="stylesheet" href="{{ base_url('css/settlement/jurnal_ca_escrow.css') }}">
@endpush

@section('content')
    <div class="subheader">
        <h1 class="subheader-title">
            <i class="fal fa-exchange-alt"></i> {{ $title }}
            <small>Jurnal CA to Escrow untuk tanggal {{ date('d/m/Y', strtotime($tanggalData)) }}</small>
        </h1>
    </div>

    <!-- Filter Section -->
    @include('settlement.jurnal_ca_escrow._filter')

    <!-- Data Table -->
    @include('settlement.jurnal_ca_escrow._data_table')

    <!-- Batch Progress Modal -->
    @include('settlement.jurnal_ca_escrow._batch_progress_modal')

    <!-- Callback Log Modal -->
    @include('settlement.jurnal_ca_escrow._callback_log_modal')
@endsection

@push('scripts')
    <script>
    window.appConfig = {
        baseUrl: "{{ base_url() }}",
        tanggalData: "{{ $tanggalData }}",
        statusFilter: "{{ $statusFilter }}",
        csrfToken: "{{ csrf_token() }}"
    };
    </script>
    <script src="{{ base_url('js/settlement/jurnal_ca_escrow/index.js') }}"></script>
    <script src="{{ base_url('js/settlement/jurnal_ca_escrow/helpers.js') }}"></script>
    <script src="{{ base_url('js/settlement/jurnal_ca_escrow/datatable.js') }}"></script>
    <script src="{{ base_url('js/settlement/jurnal_ca_escrow/callback_log_modal.js') }}"></script>
    <script src="{{ base_url('js/settlement/jurnal_ca_escrow/batch-process.js') }}"></script>
@endpush

