@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ base_url('css/settlement/settlement.css') }}">
    <link rel="stylesheet" href="{{ base_url('css/settlement/approve_jurnal.css') }}">
@endpush

@section('content')
    <div class="subheader">
        <h1 class="subheader-title">
            <i class="fal fa-check-circle"></i> {{ $title }}
            <small>Approval jurnal settlement untuk tanggal {{ date('d/m/Y', strtotime($tanggalRekon)) }}</small>
        </h1>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fal fa-info-circle"></i>
                <strong>Informasi Approval</strong> 
                <br>Halaman ini menampilkan daftar jurnal settlement yang perlu disetujui atau ditolak.
                <br>Klik tombol "Approve" untuk melihat detail jurnal dan melakukan proses approval (jika masih pending).
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    @include('settlement.approve_jurnal._cards')

    <!-- Filter Section -->
    @include('settlement.approve_jurnal._filter')

    <!-- Data Table -->
    @include('settlement.approve_jurnal._data_table')

    <!-- Modal Approval -->
    @include('settlement.approve_jurnal._modal')
@endsection

@push('scripts')
    <script>
    window.appConfig = {
        baseUrl: "{{ base_url() }}",
        tanggalRekon: "{{ $tanggalRekon }}",
        csrfToken: "{{ csrf_token() }}"
    };
    </script>
    <script src="{{ base_url('js/settlement/approve-jurnal.js') }}"></script>
@endpush