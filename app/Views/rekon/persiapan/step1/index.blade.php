@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ base_url('css/rekon/persiapan/step1.css') }}">
@endpush

@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        <i class="fal fa-upload"></i> {{ $title }}
        <small>Upload file settlement untuk tanggal {{ date('d/m/Y', strtotime($tanggalData)) }}</small>
    </h1>
</div>

<!-- Success/Error Messages --> 
@include('rekon.persiapan.step1._alert')

<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fal fa-info-circle"></i>
            <strong>Tanggal Settlement:</strong> {{ date('d/m/Y', strtotime($tanggalData)) }}
        </div>
    </div>
</div>

<!-- Forms Upload -->
@include('rekon.persiapan.step1._upload_forms')

<!-- Navigation Buttons -->
@include('rekon.persiapan.step1._navigation_buttons')

@endsection

@push('scripts')
    <script>
    window.appConfig = {
        baseUrl: "{{ base_url() }}",
        tanggalData: "{{ $tanggalData }}",
        csrfToken: "{{ csrf_token() }}"
    };
    </script>
    <script src="{{ base_url('js/rekon/persiapan/step1.js') }}"></script>
@endpush

