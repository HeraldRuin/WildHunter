<ul class="nav nav-tabs">
    <li class="nav-item">
        <a class="nav-link active" data-toggle="tab" href="#order-detail">{{__("Order Detail")}}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#order-customer">
            {{__("Customer Information")}}
        </a>
    </li>
</ul>
<div class="tab-content">
    <div id="order-detail" class="tab-pane active">
        <br>
        <div class="booking-review">
            <div class="booking-review-content">
                <div class="review-section">
                    <div class="info-form">
                        <ul>
                            <li>
                                <div class="label">{{__('Order Status')}}</div>
                                <div class="val">{{$order->status_text}}</div>
                            </li>
                            <li>
                                <div class="label">{{__('Order Date')}}</div>
                                <div class="val">{{display_date($order->created_at)}}</div>
                            </li>
                            @if(!empty($order->gateway))
                                    <?php $gateway = get_payment_gateway_obj($order->gateway); ?>
                                @if($gateway)
                                    <li>
                                        <div class="label">{{__('Payment Method')}}</div>
                                        <div class="val">{{$gateway->name}}</div>
                                    </li>
                                @endif
                                @if($gateway and $note = $gateway->getOption('payment_note'))
                                    <li>
                                        <div class="label">{{__('Payment Note')}}</div>
                                        <div class="val">{!! clean($note) !!}</div>
                                    </li>
                                @endif
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="order-box border-top pt-3">
            <h4 class="fs-18">{{__('Order details')}}</h4>
            <table class="table">
                <thead>
                <tr>
                    <th><strong>{{__('Product')}}</strong></th>
                    <th width="20%"><strong>{{__('Subtotal')}}</strong></th>
                </tr>
                </thead>
                <tbody>
                @foreach($order->items as $orderItem)
                        <?php $model = $orderItem->product; ?>
                    <tr class="cart-item">
                        <td class="product-name">{{$model->getBuyableName()}} x{{$orderItem->qty}}
                            <?php
                                // TODO: show variation
                            ?>
                            @if(!empty($orderItem->meta['extra_prices']))
                                <div class="mt-3"><strong>{{__("Extra Prices:")}}</strong></div>
                                <ul class="list-unstyled mt-2">
                                    @foreach($orderItem->meta['extra_prices'] as $extra_price)
                                        <li>{{$extra_price['name'] ?? ''}} : {{format_money($extra_price['price'] ?? 0)}}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </td>
                        <td class="product-total">{{format_money($orderItem->subtotal)}}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                @if(!empty($order->shipping_amount) and $order->shipping_amount > 0)
                    <tr class="shipping-amount">
                        <td>{{__('Shipping Amount')}}</td>
                        <td>
                            <span class="amount">{{format_money($order->shipping_amount )}}</span>
                        </td>
                    </tr>
                @endif
                @if(!empty($order->discount_amount) and $order->discount_amount > 0)
                    <tr class="discount-amount">
                        <td>{{__('Discount Amount')}}</td>
                        <td>
                            <span class="amount">-{{format_money($order->discount_amount )}}</span>
                        </td>
                    </tr>
                @endif
                @if(!empty($order->tax_amount) and $order->tax_amount > 0)
                    <tr class="shipping-amount">
                        <td>
                            {{__('Tax')}} @if($order->getMeta('prices_include_tax') == "yes")
                                <span>({{ __("include") }})</span>
                            @endif
                        </td>
                        <td>
                            <span class="amount">{{format_money($order->tax_amount )}}</span>
                        </td>
                    </tr>
                @endif
                <tr class="order-total">
                    <td>{{__('Total')}}</td>
                    <td>
                        <span class="amount">{{format_money($order->total)}}</span>
                    </td>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div id="order-customer" class="tab-pane fade">
        <br>
        @include('Order::emails.parts.order-address',['order'=>$order])
    </div>
</div>
