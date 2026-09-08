@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <div>
                <h1 class="title-bar">{{ __('Add Blog') }}</h1>
            </div>
        </div>
        @include('admin.message')
    </div>
@endsection
