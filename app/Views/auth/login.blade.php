@extends('layouts.auth')
@section('content')
    <div>
        <div class="blankpage-form-field">
            <div
                class="page-logo m-0 w-100 align-items-center justify-content-center rounded border-bottom-left-radius-0 border-bottom-right-radius-0 px-4 bg-primary-900">
                <a href="{{ site_url('login') }}" class="page-logo-link press-scale-down d-flex align-items-center">
                    <img src="{{ base_url('/img/logo.png') }}" alt="SIRELA" aria-roledescription="logo">
                    <span class="page-logo-text mr-1">SIRELA</span>
                </a>
            </div>
            <div class="card p-4 border-top-left-radius-0 border-top-right-radius-0">
                {!! form_open('', ['id' => 'loginForm']) !!}
                <div class="form-group">
                    <label class="form-label" for="username">Nama Pengguna</label>
                    <input type="text" id="username" class="form-control" name="username"
                        value="{{ set_value('username') }}" autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Kata Sandi</label>
                    <div class="input-group" id="show_hide_password">
                        <input type="password" id="password" class="form-control" name="password"
                            value="{{ set_value('password') }}">
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <a href="#" class="text-dark">
                                    <i class="fal fa-eye-slash"></i>
                                </a>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-6">
                            <span id="captchaImg">
                                {!! $captcha['image'] !!}
                            </span>
                        </div>
                        <div class="col-2 my-auto">
                            <button class="btn btn-primary" type="button" id="btnRefresh"><i
                                    class="fal fa-sync"></i></button>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <input type="captcha" id="captcha" class="form-control" name="captcha"
                        placeholder="Masukkan Captcha..." required autocomplete="off">
                </div>
                <button type="submit" class="btn btn-primary bg-primary-900 btn-block" id="btnSubmit">Masuk</button>
                {!! form_close() !!}

                <div class="text-center text-muted mt-4">
                    <p class="fw-300 mb-0">SIRELA - Sistem Rekonsiliasi dan Pelimpahan Dana</p>
                    <p class="fw-500">Bank Kalsel</p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $("#show_hide_password a").on('click', function(event) {
                    event.preventDefault();
                    if ($('#show_hide_password input').attr("type") == "text") {
                        $('#show_hide_password input').attr('type', 'password');
                        $('#show_hide_password i').addClass("fa-eye-slash");
                        $('#show_hide_password i').removeClass("fa-eye");
                    } else if ($('#show_hide_password input').attr("type") == "password") {
                        $('#show_hide_password input').attr('type', 'text');
                        $('#show_hide_password i').removeClass("fa-eye-slash");
                        $('#show_hide_password i').addClass("fa-eye");
                    }
                });

                $("#loginForm").submit(function() {
                    $("#btnSubmit").attr('disabled', true);
                    $('#btnSubmit').html('<i class="fal fa-circle-notch fa-spin mr-2"></i> Masuk');
                });

                $("#btnRefresh").on('click', function(event) {
                    $("#btnRefresh").attr('disabled', true);
                    $('#btnRefresh').html('<i class="fal fa-circle-notch fa-spin"></i>');

                    $.ajax({
                        url: "{{ site_url('refresh/captcha') }}",
                        type: "GET",
                        dataType: "json",
                        dataSrc: '',
                        success: function(result) {
                            if (result.status == 200) {
                                $('#captchaImg').html(result.data.image);
                            } else {
                                toastr["error"](result.message);
                            }
                            $("#btnRefresh").attr('disabled', false);
                            $('#btnRefresh').html('<i class="fal fa-sync"></i>');
                        },
                        error: function(xhr, status, error) {
                            console.log("An error occurred: " + error);
                            toastr["error"](error);
                        }
                    });
                });
            });
        </script>
    @endpush
@stop
