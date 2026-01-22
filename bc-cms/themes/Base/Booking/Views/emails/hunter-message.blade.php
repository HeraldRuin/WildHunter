@extends('Email::layout')

@section('content')
    <div class="b-container">
        <div class="b-panel">
            <h3 class="email-headline">
                <strong>
                    {{ __('Здравствуйте, :name', ['name' => $hunter->name ?? $hunter->first_name ?? $hunter->email]) }}
                </strong>
            </h3>

            <p>{{ $bodyText }}</p>
        </div>
    </div>
@endsection

