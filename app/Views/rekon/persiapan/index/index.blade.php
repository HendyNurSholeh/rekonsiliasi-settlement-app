@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ base_url('css/rekon/persiapan/index.css') }}">
@endpush

@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        <i class="fal fa-calendar-check"></i> {{ $title }}
        <small>Sistem rekonsiliasi settlement dengan workflow berurutan</small>
    </h1>
</div>
<div class="row">
    <!-- Main Form Card -->
    <div class="col-xl-8 col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fal fa-calendar-plus text-primary"></i>
                    Buat Proses Rekonsiliasi Baru
                </h3>
            </div>
            <div class="card-body">
                <!-- Alert for existing process -->
                @if(session('need_confirmation'))
                    @include('rekon.persiapan.index._alert')
                @endif
                <!-- Form process -->
                @include('rekon.persiapan.index._form')
            </div>
        </div>
        <!-- Workflow Info Card -->
        @include('rekon.persiapan.index._workflow_info')
    </div>
    <!-- Important Notes -->
    @include('rekon.persiapan.index._important_notes')
</div>
@endsection

@push('scripts')
    <script>
    window.appConfig = {
        baseUrl: "{{ base_url() }}",
        defaultDate: "{{ $defaultDate }}",
    };
    </script>
    <script src="{{ base_url('js/rekon/persiapan/index.js') }}"></script>
@endpush