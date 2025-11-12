<form action="{{ route('visa.search') }}" class="form bc_form" method="get">
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
