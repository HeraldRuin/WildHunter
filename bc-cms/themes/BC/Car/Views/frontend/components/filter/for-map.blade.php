<form wire:submit.prevent="search"
    class="form bc_form d-flex justify-content-start" 
    method="get" 
    x-data='{ 
        openAdvanceFilters: false, 
        attrs: {!! json_encode((object) $attrs) !!},
        toggleAttr(itemId, slug, checked){
            this.attrs[itemId] = this.attrs[itemId] || [];
            if (checked) {
                this.attrs[itemId].push(slug);
            } else {
                this.attrs[itemId] = this.attrs[itemId].filter(item => item !== slug);
            }
        },
        setAttrs(){
            $wire.call("setFilter", "attrs", this.attrs);
            this.openAdvanceFilters = false;
        },
        get count(){
            return Object.values(this.attrs).flat().length;
        }
    }'>
    @php $car_map_search_fields = setting_item_array('car_map_search_fields');

        $car_map_search_fields = array_values(
            \Illuminate\Support\Arr::sort($car_map_search_fields, function ($value) {
                return $value['position'] ?? 0;
            }),
        );

        $usedAttrs = [];
        foreach ($car_map_search_fields as $field){
            if($field['field'] == 'attr' and !empty($field['attr']))
            {
                $usedAttrs[] = $field['attr'];
            }
        }

    @endphp
    @if (!empty($car_map_search_fields))
        @foreach ($car_map_search_fields as $field)
            @switch($field['field'])
                @case ('location')  
                    @include('Layout::livewire.search-map.fields.location', 
                    [
                        'locationsList' => $this->locationsList, 
                        'location_search_style' => setting_item("boat_location_search_style")
                    ])
                @break

                @case ('attr')
                    @include('Layout::livewire.search-map.fields.attr', ['attrsList' => $this->attrsList,'attr_icon' => 'icofont-car'])
                @break

                @case ('date')
                    @include('Layout::livewire.search-map.fields.date')
                @break

                @case ('price')
                    @include('Layout::livewire.search-map.fields.price', ['min_max_price' => $this->min_max_price])
                @break

                @case ('advance')
                    <div class="filter-item filter-simple">
                        <div class="form-group">
                            <span class="filter-title toggle-advance-filter"
                            x-on:click="openAdvanceFilters = !openAdvanceFilters"
                                data-target="#advance_filters">
                                <span class="b-flex b-items-center b-gap-2">
                                    <span class="badge badge-danger" x-show="count" x-text="count"></span>
                                    {{ __('More filters') }} 
                                </span>
                                <i class="fa fa-angle-down"></i>
                            </span>
                        </div>
                    </div>
                @break
            @endswitch
        @endforeach
    @endif
    <div id="advance_filters" 
    x-show="openAdvanceFilters" 
    style="display: none;"
    x-on:click.outside="openAdvanceFilters = false"
    class="md:b-absolute b-top-full b-right-0 b-w-[60%] b-bg-white b-flex b-z-[1000] b-flex-col b-fixed b-bottom-0 md:b-bottom-auto">
        <div class="ad-filter-b b-p-8 b-overflow-y-auto b-flex-grow">
            @include('Layout::livewire.search-map.fields.attrs',['attrsList'=>$this->attrsList])
        </div>
        <div class="b-text-right b-border-0 b-bg-neutral-50 b-shrink-0 b-px-4 b-py-2.5 b-border-t-[#ececec] b-border-b-[#dad8d8] b-border-t b-border-solid b-border-b">
            <span x-on:click="setAttrs()" class="btn btn-primary btn-apply-advances">{{__("Apply Filters")}}</span>
        </div>
    </div>
</form>
@script
    <script>
        $('.form-date-search',$wire.$el).each(function () {
            var parent = $(this),
                check_in_input = $('.check-in-input', parent),
                check_out_input = $('.check-out-input', parent);

            check_in_input.on('change', function () {
                $wire.setFilters({
                    'start': $(this).val(),
                    'end': check_out_input.val()
                });
            });
        });
        $('[name="location_id"]',$wire.$el).on('change', function () {
            $wire.setFilter('location_id', $(this).val());
        });
        $('.attr_id_hidden_input',$wire.$el).on('change', function () {
            $wire.setAttr($(this).data('attr-id'), $(this).val());
        });

        // Initialize ion range slider
        $(".bc-filter-price").each(function () {
            var input_price = $(this).find(".filter-price");
            var min = input_price.data("min");
            var max = input_price.data("max");
            var from = input_price.data("from");
            var to = input_price.data("to");
            var symbol = input_price.data("symbol");
            input_price.ionRangeSlider({
                type: "double",
                grid: true,
                min: min,
                max: max,
                from: from,
                to: to,
                prefix: symbol,
                onFinish: function (data) {
                    $wire.setFilter('price_range', data.from + ';' + data.to);
                }
            });
        });
    </script>
@endscript
