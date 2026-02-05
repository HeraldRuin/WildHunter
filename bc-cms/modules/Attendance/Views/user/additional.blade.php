@extends('layouts.user')

@section('content')
    <h2 class="title-bar">{{ __('Additionals services') }}</h2>

    <div id="addetional-app" class="row mt-4 row-width">
        <div class="col-md-9">
            <div class="tab-content">
                <div class="d-flex justify-content-end mb-3">
                    <button class="btn btn-primary" @click="addAdditional">
                        {{ __("Add addition") }}
                    </button>
                </div>

                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>{{ __("Name") }}</th>
                        <th>{{ __("Amount") }}</th>
                        <th>{{ __("Actions") }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($additionals as $additional)
                        @include('Additional::frontend.partials.additional-row', ['additional' => $additional])
                    @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
@endsection
