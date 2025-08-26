@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ base_url('css/settlement/settlement.css') }}">
    <link rel="stylesheet" href="{{ base_url('css/settlement/buat_jurnal.css') }}">
@endpush

@section('content')
    <div class="subheader">
        <h1 class="subheader-title">
            <i class="fal fa-file-invoice-dollar"></i> {{ $title }}
            <small>Membuat jurnal settlement untuk tanggal {{ date('d/m/Y', strtotime($tanggalRekon)) }}</small>
        </h1>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fal fa-info-circle"></i>
                <strong>Informasi Settlement</strong> 
                <br>Modul ini berfungsi untuk membuat jurnal transaksi settlement yang kemudian akan diproses di sistem core banking. 
                <br>Produk yang dapat diproses adalah produk yang tidak memiliki dispute atau status settle verifikasinya adalah 1 (dilimpahkan) atau 9 (tidak dilimpahkan).
            
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    @include('settlement.buat_jurnal._filter')

    <!-- Data Table -->
    @include('settlement.buat_jurnal._data_table')

    <!-- Modal Konfirmasi Create Jurnal -->
    @include('settlement.buat_jurnal._modal')
@endsection

@push('scripts')
    <script>
    window.appConfig = {
        baseUrl: "{{ base_url() }}",
        tanggalRekon: "{{ $tanggalRekon }}",
        csrfToken: "{{ csrf_token() }}"
    };
    </script>
    <script src="{{ base_url('js/settlement/buat-jurnal.js') }}"></script>
@endpush