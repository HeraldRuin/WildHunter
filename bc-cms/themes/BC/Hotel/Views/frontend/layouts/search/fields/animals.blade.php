@php($location_search_style = setting_item('hotel_location_search_style'))

<div class="form-group">
    <i class="field-icon icofont-paw"></i>
    <div class="form-content">
        <label> {{ $field['title'] }} </label>
        @if($location_search_style=='autocompletePlace')
            <div class="g-map-place" >
                <input type="text" name="map_place" placeholder="{{__("Who will be hunted")}}"  value="{{request()->input('map_place')}}" class="form-control border-0">
                <div class="map d-none" id="map-{{\Illuminate\Support\Str::random(10)}}"></div>
                <input type="hidden" name="map_lat" value="{{request()->input('map_lat')}}">
                <input type="hidden" name="map_lng" value="{{request()->input('map_lng')}}">
            </div>

        @else
            <button type="button"
                    id="clear-animal"
                    class="clear-animal-btn btn btn-sm btn-light"
                    style="position:absolute; right:2px; top:30%; transform:translateY(-50%)">
                ✕
            </button>
                <?php
                $animal_name = "";
                $list_json = [];
                $traverse = function ($animals, $prefix = '') use (&$traverse, &$list_json, &$animal_name) {
                    foreach ($animals as $animal) {
                        $translate = $animal->translate();
                        if (Request::query('animal_id') == $animal->id) {
                           $animal_name = $translate->name;
                        }
                        $list_json[] = [
                            'id'    => $animal->id,
                            'title' => $animal->title,
                        ];
//                        $traverse($animal->children, $prefix . '-');
                    }
                };

                $traverse($list_animals);
                ?>

        <div class="smart-search">
            <input type="text" class="smart-search-animal parent_text form-control" {{ ( empty(setting_item("hotel_location_search_style")) or setting_item("hotel_location_search_style") == "normal" ) ? "readonly" : ""  }} placeholder="{{__("Who will be hunted")}}" value="{{ $animal_name }}" data-onLoad="{{__("Loading...")}}"
                   data-default="{{ json_encode($list_json) }}">
            <input type="hidden" class="child_id" name="animal_id" value="{{Request::query('animal_id')}}">
        </div>
            @endif
    </div>
</div>
