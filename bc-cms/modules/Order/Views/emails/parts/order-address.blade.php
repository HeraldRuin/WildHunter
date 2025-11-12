<?php
$billing_address = $order->getJsonMeta('billing');
$shipping_address = $order->getJsonMeta('shipping');
$fields = ['country', 'state_code', 'city', 'zip', 'address', 'address_2'];
?>
<div class="flex flex-col md:flex-row gap-5 relative items-start justify-between">
    <div class="">
        <h3 class="address-title font-medium"> {{ __('Customer Information') }}</h3>
        <address class="address">
            {{ $billing_address['first_name'] ?? '' }} {{ $billing_address['last_name'] ?? '' }}
            @foreach ($fields as $field)
                @if (!empty($billing_address[$field]))
                    @switch($field)
                        @case('country')
                            <br>{{ get_country_name($billing_address[$field] ?? '') }}
                        @break
                        @case('state_code')
                            @if (!empty($billing_address['country']))
                                <br>{{ \Modules\Location\Helpers\AddressHelper::getStateName($billing_address['country'], $billing_address['state_code']) }}
                            @endif
                        @break

                        @default
                            <br>{{ $billing_address[$field] ?? '' }}
                        @break
                    @endswitch
                @endif
            @endforeach
        </address>
    </div>
</div>
<br>
