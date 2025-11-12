<p><strong>{{__("Order ID:")}}</strong> #{{$order->id}}</p>
<p><strong>{{__("Order Date:")}}</strong> {{display_datetime($order->created_at)}}</p>
<p><strong>{{__("Gateway:")}}</strong> {{$order->gateway_name}}</p>
<p><strong>{{__("Status:")}}</strong> {{$order->status_text}}</p>
<br>
<table class="b-table" border="1px" cellpadding="0" cellspacing="0">
    <thead>
    <tr style="border-bottom: 1px solid #EAEEF3" class="carttable_row">
        <th style="padding: 10px" class="cartm_title">{{__('Product')}}</th>
        <th style="padding: 10px" class="cartm_title">{{__('Quantity')}}</th>
        <th style="padding: 10px" class="cartm_title">{{__('Price')}}</th>
    </tr>
    </thead>
    <tbody class="table_body">

    @foreach($order->items as $orderItem)
        <?php if($email_to == 'vendor' and $orderItem->vendor_id != $vendor->id) continue; ?>
        <?php $model = $orderItem->model; ?>
        <tr style="border-bottom: 1px solid #EAEEF3">
            <td style="border-bottom: 1px solid #EAEEF3;padding: 10px" scope="row">
                @if($model)
                    {{$model->title}}
                @else
                    {{$orderItem->name}}
                @endif

                @if(!empty($orderItem->meta['package']))
                    <div class="mt-3">{{__('Package: ')}} {{package_key_to_name($orderItem->meta['package'])}} ({{format_money($orderItem->price)}})</div>
                @endif
                @if(!empty($orderItem->meta['extra_prices']))
                    <div><strong>{{__("Extra Prices:")}}</strong></div>
                    <ul class="list-unstyled">
                        @foreach($orderItem->meta['extra_prices'] as $extra_price)
                            <li>{{$extra_price['name'] ?? ''}} : {{format_money($extra_price['price'] ?? 0)}}</li>
                        @endforeach
                    </ul>
                @endif
            </td>
            <td style="border-bottom: 1px solid #EAEEF3;padding: 10px">{{$orderItem->qty}}</td>
            <td style="border-bottom: 1px solid #EAEEF3;padding: 10px">{{format_money($orderItem->subtotal)}}</td>
        </tr>
    @endforeach
    @if(!empty($order->shipping_amount) and $order->shipping_amount > 0)
        <tr class="shipping-amount">
            <td colspan="2">{{__('Shipping Amount')}}</td>
            <td><span class="amount">{{format_money($order->shipping_amount )}}</span></td>
        </tr>
    @endif
    @if(!empty($order->discount_amount) and $order->discount_amount > 0)
        <tr class="discount-amount">
            <td colspan="2">{{__('Discount Amount')}}</td>
            <td><span class="amount">-{{format_money($order->discount_amount )}}</span></td>
        </tr>
    @endif
    @if(!empty($order->tax_amount) and $order->tax_amount > 0)
        <tr class="tax-amount" >
            <td colspan="2">
                {{__('Tax')}} @if($order->getMeta('prices_include_tax') == "yes")<span >({{ __("include") }})</span> @endif
            </td>
            <td><span class="amount">{{format_money($order->tax_amount )}}</span></td>
        </tr>
    @endif
    <tr>
        <td colspan="2"><strong>{{__('Total')}}</strong></td>
        <td>{{format_money($order->total)}}</td>
    </tr>
    </tbody>
</table>
<br>
