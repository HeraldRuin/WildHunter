@php $lang_local = app()->getLocale() @endphp
<div class="booking-review">
    <h4 class="booking-review-title">{{__("Booking Hunting")}}</h4>
    <div class="booking-review-content">
        <div class="review-section">
            <div class="service-info">
                <div>
                    @php
                        $service_translation = $service->translate($lang_local);
                    @endphp
                    <h3 class="service-name"><a href="{{$service->getDetailUrl()}}">{!! clean($service_translation->title) !!}</a></h3>
                    @if($service_translation->address)
                        <p class="address"><i class="fa fa-map-marker"></i>
                            {{$service_translation->address}}
                        </p>
                    @endif
                </div>
                <div>
                    @if($image_url = $service->image_url)
                        @if(!empty($disable_lazyload))
                            <img src="{{$service->image_url}}" class="img-responsive" alt="{!! clean($service_translation->title) !!}">
                        @else
                            {!! get_image_tag($service->image_id,'medium',['class'=>'img-responsive','alt'=>$service_translation->title]) !!}
                        @endif

                    @endif
                </div>
                @php $vendor = $service->author; @endphp
                @if($vendor->hasPermission('dashboard_vendor_access') and !$vendor->hasPermission('dashboard_access'))
                    <div class="mt-2">
                        <i class="icofont-info-circle"></i>
                        {{ __("Vendor") }}: <a href="{{route('user.profile',['id'=>$vendor->id])}}" target="_blank">{{$vendor->getDisplayName()}}</a>
                    </div>
                @endif
            </div>
        </div>
        <div class="review-section">
            <ul class="review-list">
                @if($booking)
                    <li>
                        <div class="label">{{__('Date Hunting:')}}</div>
                        <div class="val">
                            {{display_date($booking->start_date_animal)}}
                        </div>
                    </li>

                    <li>
                        <div class="label">{{__('Aduls Hunting:')}}</div>
                        <div class="val">
                            {{$booking->total_hunting}}
                        </div>
                    </li>
                    <li>
                        <div class="label">{{__('Organization Hunting:')}}</div>
                        <div class="val">
                            {{$booking->amount_hunting}}
                        </div>
                    </li>
                @endif
            </ul>
        </div>
        <div class="review-section total-review">

            <ul class="review-list">
{{--                @includeIf('Coupon::frontend/booking/checkout-coupon')--}}
                <li class="final-total d-block">
                    <div class="d-flex justify-content-between">
                        <div class="label">{{__("Total:")}}</div>
                        <div class="val">{{format_money($booking->amount_hunting)}}</div>
                    </div>
                    @if($booking->status !='draft')
                        <div class="d-flex justify-content-between">
                            <div class="label">{{__("Paid:")}}</div>
                            <div class="val">{{format_money($booking->paid)}}</div>
                        </div>
                        @if($booking->paid < $booking->amount_hunting)
                            <div class="d-flex justify-content-between">
                                <div class="label">{{__("Remain:")}}</div>
                                <div class="val">{{format_money($booking->amount_hunting - $booking->paid)}}</div>
                            </div>
                        @endif
                    @endif
                </li>
{{--                @include ('Booking::frontend/booking/checkout-deposit-amount')--}}
            </ul>
        </div>
    </div>
</div>

