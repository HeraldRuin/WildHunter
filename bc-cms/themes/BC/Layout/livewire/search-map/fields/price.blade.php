<div x-data='{
    show: false
}' class="filter-item filter-simple dropdown"
x-on:click.outside="show = false"
>
    <div class="form-group" x-on:click="show = !show" >
        <h3 class="filter-title">
            @if($price_range)
                <?php $range = explode(";", $price_range); ?>
                <span>{{format_money($range[0])}} - {{format_money($range[1])}}</span>    
            @else
                <span>{{__('Price filter')}}</span>   
            @endif 
            <i class="fa fa-angle-down"></i>
        </h3>
    </div>
    <div class="filter-dropdown dropdown-menu dropdown-menu-right b-left-2" x-bind:class="{'d-block': show}" >
        <div class="bc-filter-price" wire:ignore>
            <?php
            $price_min = $pri_from = floor ( App\Currency::convertPrice($min_max_price[0]) );
            $price_max = $pri_to = ceil ( App\Currency::convertPrice($min_max_price[1]) );
            if (!empty($price_range)) {
                $pri_from = explode(";", $price_range)[0];
                $pri_to = explode(";", $price_range)[1];
            }
            $currency = App\Currency::getCurrency( App\Currency::getCurrent() );
            ?>
            <input type="hidden" class="filter-price irs-hidden-input" name="price_range"
                   data-symbol=" {{$currency['symbol'] ?? ''}}"
                   data-min="{{$price_min}}"
                   data-max="{{$price_max}}"
                   data-from="{{$pri_from}}"
                   data-to="{{$pri_to}}"
                   readonly="" value="{{$price_range}}">
        </div>
    </div>
</div>