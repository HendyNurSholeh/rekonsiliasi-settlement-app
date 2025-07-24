@extends('layouts.app')
@section('content')
    <ol class="breadcrumb bg-transparent breadcrumb-sm pl-0 pr-0 ml-2">
        <li class="breadcrumb-item">
            <a href="{{ site_url('dashboard') }}">
                <i class="fal fa-home mr-1"></i> Home
            </a>
        </li>
        <li class="breadcrumb-item active">{{ $title }}</li>
    </ol>

    <div class="panel">
        <div class="panel-hdr">
            <h2>
                {{ $title }}
            </h2>
        </div>
        <div class="panel-container">
            {!! form_open('', 'id="myform"') !!}
            <div class="panel-content">
                <div class="form-row my-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="name">Nama Lengkap <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="name" class="form-control" name="name"
                                value="{{ set_value('name', $data['name']) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="username">Username <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="username" class="form-control" name="username"
                                value="{{ set_value('username', $data['username']) }}" disabled>
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="panel-content py-2 rounded-bottom border-faded border-left-0 border-right-0 border-bottom-0 text-muted d-flex">
                <a href="{{ base_url('dashboard') }}" class="btn btn-sm btn-default mr-2 ml-auto">
                    <i class="fal fa-times mr-2"></i>
                    Batal
                </a>
                <button type="submit" id="btnSave" class="btn btn-sm btn-primary">
                    <i class="fal fa-save mr-2"></i>
                    Simpan
                </button>
            </div>
            {!! form_close() !!}
        </div>
    </div>

    <div class="panel">
        <div class="panel-hdr">
            <h2>
                Password
            </h2>
        </div>
        <div class="panel-container">
            <div class="panel-content">
                {!! form_open('', 'id="myform"') !!}
                <div class="panel-content">
                    <div class="form-row my-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="old_password">Password lama <span
                                        class="text-danger">*</span>
                                </label>
                                <div class="input-group" id="show_hide_old_password">
                                    <input type="password" id="old_password" class="form-control" name="old_password">
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <a href="#" class="text-dark">
                                                <i class="fal fa-eye-slash"></i>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row my-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="new_password">Password baru <span
                                        class="text-danger">*</span>
                                </label>
                                <div class="input-group" id="show_hide_new_password">
                                    <input type="password" id="new_password" class="form-control" name="new_password">
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <a href="#" class="text-dark">
                                                <i class="fal fa-eye-slash"></i>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                                <p class="help-block m-0">
                                    *password minimal 8 karakter
                                </p>
                                <p class="help-block m-0">
                                    *password harus memuat huruf kecil, huruf besar, angka dan simbol
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="conf_password">Konfirmasi password baru<span
                                        class="text-danger">*</span>
                                </label>
                                <div class="input-group" id="show_hide_conf_password">
                                    <input type="password" id="conf_password" class="form-control" name="conf_password">
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <a href="#" class="text-dark">
                                                <i class="fal fa-eye-slash"></i>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="panel-content py-2 rounded-bottom border-faded border-left-0 border-right-0 border-bottom-0 text-muted d-flex">
                    <a href="{{ base_url('dashboard') }}" class="btn btn-sm btn-default mr-2 ml-auto">
                        <i class="fal fa-times mr-2"></i>
                        Batal
                    </a>
                    <button type="submit" id="btnSave" class="btn btn-sm btn-primary">
                        <i class="fal fa-save mr-2"></i>
                        Simpan
                    </button>
                </div>
                {!! form_close() !!}
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $("#show_hide_new_password a").on('click', function(event) {
                    event.preventDefault();
                    if ($('#show_hide_new_password input').attr("type") == "text") {
                        $('#show_hide_new_password input').attr('type', 'password');
                        $('#show_hide_new_password i').addClass("fa-eye-slash");
                        $('#show_hide_new_password i').removeClass("fa-eye");
                    } else if ($('#show_hide_new_password input').attr("type") == "password") {
                        $('#show_hide_new_password input').attr('type', 'text');
                        $('#show_hide_new_password i').removeClass("fa-eye-slash");
                        $('#show_hide_new_password i').addClass("fa-eye");
                    }
                });

                $("#show_hide_old_password a").on('click', function(event) {
                    event.preventDefault();
                    if ($('#show_hide_old_password input').attr("type") == "text") {
                        $('#show_hide_old_password input').attr('type', 'password');
                        $('#show_hide_old_password i').addClass("fa-eye-slash");
                        $('#show_hide_old_password i').removeClass("fa-eye");
                    } else if ($('#show_hide_old_password input').attr("type") == "password") {
                        $('#show_hide_old_password input').attr('type', 'text');
                        $('#show_hide_old_password i').removeClass("fa-eye-slash");
                        $('#show_hide_old_password i').addClass("fa-eye");
                    }
                });

                $("#show_hide_conf_password a").on('click', function(event) {
                    event.preventDefault();
                    if ($('#show_hide_conf_password input').attr("type") == "text") {
                        $('#show_hide_conf_password input').attr('type', 'password');
                        $('#show_hide_conf_password i').addClass("fa-eye-slash");
                        $('#show_hide_conf_password i').removeClass("fa-eye");
                    } else if ($('#show_hide_conf_password input').attr("type") == "password") {
                        $('#show_hide_conf_password input').attr('type', 'text');
                        $('#show_hide_conf_password i').removeClass("fa-eye-slash");
                        $('#show_hide_conf_password i').addClass("fa-eye");
                    }
                });
            });
        </script>
    @endpush
@stop
