@extends('layouts.app', ['container_class' => 'container-fluid', 'header_right_menu' => true])
@push('css')
    <link href="{{ asset('themes/bc/dist/frontend/module/hotel/css/hotel.css?_ver=' . config('app.asset_version')) }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('libs/ion_rangeslider/css/ion.rangeSlider.min.css') }}" />
    <style type="text/css">
        .bc_topbar,
        .bc_footer {
            display: none
        }
    </style>
@endpush
@section('content')
    <div class="bc_search_tour bc_search_space">
        <h1 class="d-none">
            {{ setting_item_with_lang('hotel_page_search_title') }}
        </h1>
        <div class="bc_form_search_map">
            @include('Hotel::frontend.layouts.search-map.form-search-map')
        </div>
        <div class="bc_search_map {{ setting_item_with_lang('hotel_layout_map_option', false, 'map_left') }}">
            <div class="results_map">
                <div class="map-loading d-none">
                    <div class="st-loader"></div>
                </div>
                <div id="bc_results_map" class="results_map_inner"></div>
            </div>
            <div class="results_item">
                @include('Hotel::frontend.layouts.search-map.advance-filter')
                <div class="listing_items ajax-search-result">
                    @include('Hotel::frontend.ajax.search-result-map')
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    {!! App\Helpers\MapEngine::scripts() !!}
    <script>
        var bc_map_data = {
            markers: {!! json_encode($markers) !!},
            map_lat_default: {{ setting_item('hotel_map_lat_default', '0') }},
            map_lng_default: {{ setting_item('hotel_map_lng_default', '0') }},
            map_zoom_default: {{ setting_item('hotel_map_zoom_default', '0') }},
        };
    </script>
    <script type="text/javascript" src="{{ asset('libs/ion_rangeslider/js/ion.rangeSlider.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/filter-map.js?_ver=' . config('app.asset_version')) }}"></script>
@endpush
