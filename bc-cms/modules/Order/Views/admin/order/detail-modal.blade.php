<div class="modal fade" id="modal-order-{{$row->id}}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">{{__('Order ID: ')}} #{{$row->id}}</h4>
            </div>

            <div class="modal-body">
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#order-detail-{{$row->id}}">{{__('Order Detail')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#order-customer-{{$row->id}}">
                            {{ __('Customer Information') }}
                        </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div id="order-detail-{{$row->id}}" class="tab-pane active">
                        <br>
                        <div class="booking-review">
                            <div class="booking-review-content">
                                <div class="review-section">
                                    <div class="info-form">
                                        <ul>
                                            <li class="text-uppercase font-weight-bold info-header">
                                                <div class="label">{{ __('Items') }}</div>
                                                <div class="val">{{ __('Total') }}</div>
                                            </li>
                                            @if(!empty($items = $row->items))
                                                @foreach($items as $item)
                                                    <li class="info-content">
                                                        <div class="label">
                                                            <div class="name">{{ $item->product->getBuyableName() ?? '' }} x {{ $item->qty }}</div>
                                                            <div class="sold-by"><span style="font-weight: 600">{{ __('Sold by:') }}</span> {{$model->author->display_name}}</div>
                                                        </div>
                                                        <div class="val" style="color: red">{{format_money($item->qty * $item->price)}}</div>
                                                    </li>
                                                @endforeach
                                            @endif
                                            @if($row->coupons)
                                                @foreach($row->coupons as $coupon)
                                                    <li class="info-content info-coupon">
                                                        <div class="label text-uppercase">{{ __('Coupon: :coupon',['coupon'=>$coupon->code]) }}</div>
                                                        <div class="val" style="color: red">-{{ format_money($row->items->sum('discount_amount'))}}</div>
                                                    </li>
                                                @endforeach
                                            @endif
                                            <li class="info-total">
                                                <div class="label font-weight-bold text-uppercase">{{__('Total')}}</div>
                                                <div class="val">{{ format_money($row->total) }}</div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="order-customer-{{$row->id}}" class="tab-pane fade">
                        <br>
                        <div class="booking-review">
                            <h4 class="booking-review-title">{{ __('Your Information') }}</h4>
                            <div class="booking-review-content">
                                <div class="review-section">
                                    <div class="info-form">
                                        @if(!empty($billing))
                                            <ul>
                                                <li class="info-first-name">
                                                    <div class="label">{{ __('First name') }}</div>
                                                    <div class="val">{{ $billing['first_name'] ?? '' }}</div>
                                                </li>
                                                <li class="info-last-name">
                                                    <div class="label">{{ __('Last name') }}</div>
                                                    <div class="val">{{  $billing['last_name'] ?? '' }}</div>
                                                </li>
                                                <li class="info-email">
                                                    <div class="label">{{ __('Email') }}</div>
                                                    <div class="val">{{  $billing['email'] ?? '' }}</div>
                                                </li>
                                                <li class="info-phone">
                                                    <div class="label">{{ __('Phone') }}</div>
                                                    <div class="val">{{  $billing['phone']  ?? '' }}</div>
                                                </li>
                                                <li class="info-company">
                                                    <div class="label">{{ __('Company name') }}</div>
                                                    <div class="val">{{  $billing['company'] ?? '' }}</div>
                                                </li>
                                                <li class="info-address">
                                                    <div class="label">{{ __('Address line 1') }}</div>
                                                    <div class="val">{{  $billing['address']  ?? '' }}</div>
                                                </li>
                                                <li class="info-address2">
                                                    <div class="label">{{ __('Address line 2') }}</div>
                                                    <div class="val">{{  $billing['address2']  ?? '' }}</div>
                                                </li>
                                                <li class="info-city">
                                                    <div class="label">{{ __('City') }}</div>
                                                    <div class="val">{{  $billing['city']  ?? '' }}</div>
                                                </li>
                                                <li class="info-state">
                                                    <div class="label">{{ __('State/Province/Region') }}</div>
                                                    <div class="val">{{  $billing['state'] ?? '' }}</div>
                                                </li>
                                                <li class="info-zip-code">
                                                    <div class="label">{{ __('ZIP code/Postal code') }}</div>
                                                    <div class="val">{{  $billing['postcode']  ?? '' }}</div>
                                                </li>
                                                <li class="info-country">
                                                    <div class="label">{{ __('Country') }}</div>
                                                    <div class="val">{{ get_country_name($billing['country']) ?? '' }}</div>
                                                </li>
                                            </ul>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <span class="btn btn-secondary" data-dismiss="modal">{{__('Close')}}</span>
            </div>
        </div>
    </div>
</div>

