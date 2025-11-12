<p><strong>{{__("Order ID:")}}</strong> #{{$order->id}}</p>
<p><strong>{{__("Order Item ID:")}}</strong> #{{$orderItem->id}}</p>
<p><strong>{{__("Order Date:")}}</strong> {{display_datetime($order->created_at)}}</p>
<p><strong>{{__("Gateway:")}}</strong> {{$order->gateway_name}}</p>
<p><strong>{{__("Status:")}}</strong> {{$orderItem->status_text}}</p>
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

    </tbody>
</table>
<br>
