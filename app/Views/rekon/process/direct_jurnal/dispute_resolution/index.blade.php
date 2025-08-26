@extends('layouts.app')
@push('styles') 
    <link rel="stylesheet" href="{{ base_url('css/rekon/process/direct_jurnal/dispute_resolution.css') }}">
@endpush

@section('content')
    <div class="subheader">
        <h1 class="subheader-title">
            <i class="fal fa-gavel"></i> {{ $title }}
            <small>Penyelesaian dispute direct jurnal untuk tanggal {{ date('d/m/Y', strtotime($tanggalData)) }}</small>
        </h1>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fal fa-info-circle"></i>
                <strong>Penyelesaian Dispute</strong> 
                <br>Menampilkan dan mengelola data dispute yang memerlukan penyelesaian manual.
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    @include('rekon.process.direct_jurnal.dispute_resolution._filter')

    <!-- Data Table -->
    @include('rekon.process.direct_jurnal.dispute_resolution._data_table')

    <!-- Modal Proses Data Dispute -->
    @include('rekon.process.direct_jurnal.dispute_resolution._modal')
@endsection

@push('scripts')
    <script>
    window.appConfig = {
        baseUrl: "{{ base_url() }}",
        tanggalData: "{{ $tanggalData }}",
        csrfToken: "{{ csrf_token() }}"
    };
    </script>
    <script src="{{ base_url('js/rekon/process/direct_jurnal/dispute_resolution.js') }}"></script>
@endpush