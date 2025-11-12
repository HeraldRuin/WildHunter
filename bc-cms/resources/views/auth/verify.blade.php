@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center bc-login-form-page bc-login-page">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Verify Your Email Address') }}</div>
                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('A fresh verification link has been sent to your email address.') }}
                        </div>
                    @endif
                    <p>
                        {{ __('Before proceeding, please check your email for a verification link.') }}
                        {{ __('If you did not receive the email') }},
                    </p>
                        <form action="{{ route('verification.send') }}" method="post">
                            @csrf
                            <button class="btn btn-primary" type="submit">{{ __('click here to request another') }}.</button>
                        </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
