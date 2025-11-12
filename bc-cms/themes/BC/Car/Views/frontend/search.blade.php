@push('css')
    <link href="{{ asset('themes/bc/dist/frontend/module/car/css/car.css?_ver=' . config('app.asset_version')) }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('libs/ion_rangeslider/css/ion.rangeSlider.min.css') }}" />
@endpush
    <div class="bc_search_car">
        <div class="bc_banner"
            @if ($bg = setting_item('car_page_search_banner')) style="background-image: url({{ get_file_url($bg, 'full') }})" @endif>
            <div class="container">
                <h1>
                    {{ setting_item_with_lang('car_page_search_title') }}
                </h1>
            </div>
        </div>
        <div class="bc_form_search">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 col-md-12">
                        @livewire('car::search-form')
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            @include('Car::frontend.layouts.search.list-item')
        </div>
    </div>

    @push('js')
    <script type="text/javascript" src="{{ asset('libs/ion_rangeslider/js/ion.rangeSlider.min.js') }}"></script>
    <script>
        $('.orderby .dropdown-item').on('click',function (e){
            e.preventDefault();
            $('[name=orderby]').val($(this).data('value')).trigger('change');
            $('.orderby .dropdown-toggle').html($(this).html());
        })
    </script>
@endpush
@script
    <script>
        $('[name=orderby]', $wire.$el).on('change', function (e) {
            $wire.set('orderby', $(this).val());
        });
    </script>
@endscript
