<div class="bc_search_tour">
    <div class="bc_banner"
        @if ($bg = setting_item('tour_page_search_banner')) style="background-image: url({{ get_file_url($bg, 'full') }})" @endif>
        <div class="container">
            <h1>
                {{ setting_item_with_lang('tour_page_search_title') }}
            </h1>
        </div>
    </div>
    <div class="bc_form_search">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    @livewire('tour::search-form')
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        @include('Tour::frontend.layouts.search.list-item')
    </div>
</div>

@assets
    <link href="{{ asset('themes/bc/dist/frontend/module/tour/css/tour.css?_ver=' . config('app.asset_version')) }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('libs/ion_rangeslider/css/ion.rangeSlider.min.css') }}" />
    <script defer type="text/javascript" src="{{ asset('libs/ion_rangeslider/js/ion.rangeSlider.min.js') }}"></script>
@endassets

@script
    <script>
        $('[name=orderby]', $wire.$el).on('change', function (e) {
            $wire.set('orderby', $(this).val());
        });
        $('.orderby .dropdown-item').on('click',function (e){
            e.preventDefault();
            $('[name=orderby]').val($(this).data('value')).trigger('change');
            $('.orderby .dropdown-toggle').html($(this).html());
        })
    </script>
@endscript