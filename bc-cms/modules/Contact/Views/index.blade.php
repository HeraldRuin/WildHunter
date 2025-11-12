@extends('layouts.app')
@push('css')
    <style type="text/css">
        .bc-contact-block .section {
            padding: 80px 0 !important;
        }
    </style>
@endpush
@section('content')
    <div id="bc_content-wrapper">
        @include('Contact::frontend.blocks.contact.index')
    </div>
@endsection
