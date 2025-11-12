<div class="bc_search_tour">
    <h1 class="d-none">
        {{ setting_item_with_lang('tour_page_search_title') }}
    </h1>
    <div class="bc_form_search_map b-relative">
        @livewire('tour::filter-for-map')
    </div>
    <div class="bc_search_map {{ setting_item_with_lang('tour_layout_map_option', false, 'map_left') }}">
        <div class="results_map">
            <div class="map-loading d-none">
                <div class="st-loader"></div>
            </div>
            <div x-data="BCSearchMap" id="bc_results_map" wire:ignore class="results_map_inner"></div>
        </div>
        <div class="results_item">
            <div class="listing_items ajax-search-result">
                @include('Tour::frontend.ajax.search-result-map')
            </div>
        </div>
    </div>
</div>
@assets
    <link href="{{ asset('themes/bc/dist/frontend/module/tour/css/tour.css?_ver=' . config('app.asset_version')) }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('libs/ion_rangeslider/css/ion.rangeSlider.min.css') }}" />
    
    <style type="text/css">
        .bc_topbar,
        .bc_footer {
            display: none
    }
    </style>
    {!! App\Helpers\MapEngine::scripts([
        'defer' => true,
    ]) !!}
    <script defer type="text/javascript" src="{{ asset('libs/ion_rangeslider/js/ion.rangeSlider.min.js') }}"></script>
@endassets
@script
    <script>
       const bc_map_data = {
            map_lat_default: {{ setting_item('tour_map_lat_default', '0') }},
            map_lng_default: {{ setting_item('tour_map_lng_default', '0') }},
            map_zoom_default: {{ setting_item('tour_map_zoom_default', '6') }},
        };
        Alpine.data('BCSearchMap', () => ({
            markers: {!! json_encode($markers) !!},
            listeners: [],
            init(){
                window.BCMapInstance = new BCMapEngine('bc_results_map',{
                    fitBounds:bookingCore.map_options.map_fit_bounds,
                    center:[bc_map_data.map_lat_default, bc_map_data.map_lng_default ],
                    zoom:bc_map_data.map_zoom_default,
                    disableScripts:true,
                    markerClustering:bookingCore.map_options.map_clustering,
                    ready: (engineMap) => {
                        if(this.markers){
                            engineMap.addMarkers2(this.markers);
                        }
                    }
                });
                
                this.listeners.push(
                    Livewire.on('update-markers', ({markers}) => {
                        this.updateMarkers(markers);
                    })
                );
            },
            updateMarkers(markers){
                window.BCMapInstance.clearMarkers();
                if(markers && markers.length > 0){
                    window.BCMapInstance.addMarkers2(markers);
                }
            },
            destroy() {
                this.listeners.forEach((listener) => {
                    listener();
                });
            }
        }))
    </script>
@endscript