@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ base_url('css/rekon/process/indirect_jurnal/dispute_resolution.css') }}">
@endpush

@section('content')
    <div class="subheader">
        <h1 class="subheader-title">
            <i class="fal fa-exclamation-triangle"></i> {{ $title }}
            <small>Penyelesaian dispute indirect jurnal untuk tanggal {{ date('d/m/Y', strtotime($tanggalRekon)) }}</small>
        </h1>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-warning">
                <i class="fal fa-exclamation-triangle"></i>
                <strong>Penyelesaian Dispute</strong> 
                <br>Menampilkan data transaksi indirect jurnal yang memerlukan penyelesaian dispute.
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    @include('rekon.process.indirect_jurnal.dispute_resolution._filter')

    <!-- Data Table -->
    @include('rekon.process.indirect_jurnal.dispute_resolution._data_table')

    <!-- Modal Proses Data Dispute -->
    @include('rekon.process.indirect_jurnal.dispute_resolution._modal')
@endsection

@push('scripts')
    <script>
    window.appConfig = {
        baseUrl: "{{ base_url() }}",
        tanggalData: "{{ $tanggalData }}",
        csrfToken: "{{ csrf_token() }}"
    };
    </script>
    <script src="{{ base_url('js/rekon/process/indirect_jurnal/dispute_resolution.js') }}"></script>
@endpush