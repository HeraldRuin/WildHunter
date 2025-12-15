@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center bc-login-form-page bc-login-page">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Reset Password') }}</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('password.update') }}">
                            @include('Layout::admin.message')
                            @csrf
                            <input type="hidden" name="token" value="{{ request()->route('token') }}">
                            <div class="form-group row">
                                <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>
                                <div class="col-md-6">
                                    <input id="email" type="email" class="form-control" name="email" value="{{ old('email',request()->email) }}" required autofocus>

                                </div>
                            </div>
                            <button type="button" class="btn-generate-reset-password" id="generate-reset-password-new">{{__('Generate')}}</button>
                            <div class="form-group row">
                                <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>
                                <div class="col-md-6">
                                    <div class="password-wrapper">
                                        <input id="password" type="password" class="form-control" name="password" required>
                                        <i class="toggle-change-password icofont-eye-blocked" id="toggle-reset-password-icon"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label>
                                <div class="col-md-6">
                                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                                    <i class="toggle-change-password icofont-eye-blocked" id="toggle-reset-password-icon-confirm"></i>
                                </div>
                            </div>
                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Reset Password') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        .form-group {
            position: relative;
        }
        .form-group input.form-control {
            padding-right: 40px;
        }
        .toggle-change-password {
            position: absolute;
            top: 50%;
            right: 30px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            font-size: 28px;
            transition: color 0.2s;
        }
        .toggle-change-password:hover {
            color: #000;
        }
    </style>
@endpush

