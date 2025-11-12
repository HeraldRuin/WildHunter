<div class="bc_filter" x-data x-init="window.bcInitFilterJs()">
    <?php
    $scrollIntoViewJsSnippet = <<<JS
       document.querySelector('body').scrollIntoView({
        behavior: 'smooth'
       })
    JS;
    ?>
    <div class="bc_form_filter" >
        <div class="filter-title">
            {{ __('FILTER BY') }}
        </div>
        <div class="g-filter-item">
            <div class="item-title">
                <h3>{{ __('Filter Price') }}</h3>
                <i class="fa fa-angle-up" aria-hidden="true"></i>
            </div>
            <div class="item-content">
                <div class="bc-filter-price" wire:ignore>
                    <?php
                    $price_min = $pri_from = floor(App\Currency::convertPrice($min_max_price[0]));
                    $price_max = $pri_to = ceil(App\Currency::convertPrice($min_max_price[1]));
                    if (!empty(($price_range))) {
                        [$pri_from, $pri_to] = explode(';', $price_range);
                    }
                    $currency = App\Currency::getCurrency(App\Currency::getCurrent());
                    ?>
                    <input type="hidden" class="filter-price irs-hidden-input" name="price_range"
                        data-symbol=" {{ $currency['symbol'] ?? '' }}" data-min="{{ $price_min }}"
                        data-max="{{ $price_max }}" data-from="{{ $pri_from }}" data-to="{{ $pri_to }}"
                        readonly="" value="{{ $price_range }}">
                </div>
            </div>
        </div>
        <div class="g-filter-item">
            <div class="item-title">
                <h3>{{ __('Review Score') }}</h3>
                <i class="fa fa-angle-up" aria-hidden="true"></i>
            </div>
            <div class="item-content">
                <ul>
                    @for ($number = 5; $number >= 1; $number--)
                        <li>
                            <div class="bc-checkbox">
                                <label>
                                    <input wire:model.live="review_score" type="checkbox" value="{{ $number }}"
                                           @if (in_array($number, $this->review_score)) checked @endif>
                                    <span class="checkmark"></span>
                                    @for ($review_score = 1; $review_score <= $number; $review_score++)
                                        <i class="fa fa-star"></i>
                                    @endfor
                                </label>
                            </div>
                        </li>
                    @endfor
                </ul>
            </div>
        </div>
    </div>
</div>
@script
<script>
    window.bcInitFilterJs = function () {
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
                    $wire.set('price_range', data.from + ';' + data.to);
                }
            });
        });
    }
</script>
@endscript
