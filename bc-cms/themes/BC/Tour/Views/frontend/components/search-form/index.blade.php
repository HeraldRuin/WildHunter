<form class="form bc_form" wire:submit.prevent="submit" method="get" >
    <div class="g-field-search">
        <div class="row">
            @php $tour_search_fields = setting_item_array('tour_search_fields');
                $tour_search_fields = array_values(
                    \Illuminate\Support\Arr::sort($tour_search_fields, function ($value) {
                        return $value['position'] ?? 0;
                    }),
                );
            @endphp
            @if (!empty($tour_search_fields))
                @foreach ($tour_search_fields as $field)
                    @php $field['title'] = $field['title_'.app()->getLocale()] ?? $field['title'] ?? "" @endphp
                    <div class="col-md-{{ $field['size'] ?? '6' }} border-right">
                        @switch($field['field'])
                            @case ('service_name')
                                @include('Tour::frontend.layouts.search.fields.service_name')
                            @break

                            @case ('location')
                                @include('Tour::frontend.layouts.search.fields.location')
                            @break

                            @case ('date')
                                @include('Tour::frontend.layouts.search.fields.date')
                            @break

                            @case ('attr')
                                @include('Tour::frontend.layouts.search.fields.attr')
                            @break
                        @endswitch
                    </div>
                @endforeach
            @endif
        </div>
    </div>
    <div class="g-button-submit">
        <button class="btn btn-primary btn-search" type="submit">{{ __('Search') }}</button>
    </div>
</form>
@script
    <script>
        $('.form-date-search',$wire.$el).each(function () {
            var parent = $(this),
                check_in_input = $('.check-in-input', parent),
                check_out_input = $('.check-out-input', parent);

            check_in_input.on('change', function () {
                $wire.set('start', $(this).val(), false);
            });
            check_out_input.on('change', function () {
                $wire.set('end', $(this).val(), false);
            });
        });
        $('[name="location_id"]',$wire.$el).on('change', function () {
            $wire.set('location_id', $(this).val(), false);
        });
    </script>
@endscript
