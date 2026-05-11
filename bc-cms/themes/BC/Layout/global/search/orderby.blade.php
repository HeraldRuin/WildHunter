@if(empty($hideMap))
<div class="item">
    <a class="show-map-link" data-route="{{ route($routeName) }}">
        {{ __("Show on the map") }}
    </a>
</div>
@endif
<div class="item orderby">
    @php
        $param = request()->input();
        $orderby =  request()->input("orderby");
    @endphp
    <div class="item-title">
        {{ __("Sort by:") }}
    </div>
    <input type="hidden" wire:model.live="orderby" name="orderby" value="{{$orderby}}">
    <div class="dropdown " wire:ignore>
        <span class=" dropdown-toggle"  data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            @switch($orderby)
                @case("price_low_high")
                {{ __("Price (Low to high)") }}
                @break
                @case("price_high_low")
                {{ __("Price (High to low)") }}
                @break
                @case("rate_high_low")
                {{ __("Rating (High to low)") }}
                @break
                @default
                {{ __("Recommended") }}
            @endswitch
        </span>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
            <a class="dropdown-item" href="#" data-value="">{{ __("Recommended") }}</a>
            <a class="dropdown-item" href="#" data-value="price_low_high">{{ __("Price (Low to high)") }}</a>
            <a class="dropdown-item" href="#" data-value="price_high_low">{{ __("Price (High to low)") }}</a>
            <a class="dropdown-item" href="#" data-value="rate_high_low">{{ __("Rating (High to low)") }}</a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        document.addEventListener('click', function (e) {

            const link = e.target.closest('.show-map-link');
            if (!link) return;

            e.preventDefault();

            const currentUrl = new URL(window.location.href);
            const baseUrl = link.dataset.route;

            const newUrl = new URL(baseUrl, window.location.origin);

            currentUrl.searchParams.forEach((value, key) => {
                newUrl.searchParams.set(key, value);
            });

            newUrl.searchParams.set('_layout', 'map');

            window.location.href = newUrl.toString();
        });

    });
</script>
