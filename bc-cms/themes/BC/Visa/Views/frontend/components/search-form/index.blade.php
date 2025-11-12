<form class="form bc_form" wire:submit.prevent="submit" method="get" >
    <div class="g-field-search">
        <div class="row">
            @php $visa_search_fields = setting_item_array('visa_search_fields');
                $visa_search_fields = array_values(
                    \Illuminate\Support\Arr::sort($visa_search_fields, function ($value) {
                        return $value['position'] ?? 0;
                    }),
                );
            @endphp
            @if (!empty($visa_search_fields))
                @foreach ($visa_search_fields as $field)
                    @php $field['title'] = $field['title_'.app()->getLocale()] ?? $field['title'] ?? "" @endphp
                    <div class="col-md-{{ $field['size'] ?? '6' }} border-right">
                        @switch($field['field'])
                            @case ('visa_type')
                                @include('Visa::frontend.layouts.search.fields.visa_type')
                            @break

                            @case ('to_country')
                                @include('Visa::frontend.layouts.search.fields.to_country')
                            @break

                            @case ('guests')
                                @include('Visa::frontend.layouts.search.fields.guests')
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
        $('[name="to_country"]',$wire.$el).on('change', function () {
            $wire.set('to_country', $(this).val(), false);
        });
        $('[name="visa_type"]',$wire.$el).on('change', function () {
            $wire.set('visa_type', $(this).val(), false);
        });
    </script>
@endscript
