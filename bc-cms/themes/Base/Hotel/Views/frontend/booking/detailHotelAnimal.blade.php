@php $lang_local = app()->getLocale() @endphp
<div class="booking-review">
	<h4 class="booking-review-title">{{__("Booking Hotel")}}</h4>
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
				@if($booking->start_date)
					<li>
						<div class="label">{{__('Check in:')}}</div>
						<div class="val">
							{{display_date($booking->start_date)}}
						</div>
					</li>
					<li>
						<div class="label">{{__('Check out:')}}</div>
						<div class="val">
							{{display_date($booking->end_date)}}
						</div>
					</li>
					<li>
						<div class="label">{{__('Nights:')}}</div>
						<div class="val">
							{{$booking->duration_nights}}
						</div>
					</li>
				@endif
				@if($meta = $booking->getMeta('adults'))
					<li>
						<div class="label">{{__('Adults:')}}</div>
						<div class="val">
							{{$meta}}
						</div>
					</li>
				@endif
				@if($meta = $booking->getMeta('children'))
					<li>
						<div class="label">{{__('Children:')}}</div>
						<div class="val">
							{{$meta}}
						</div>
					</li>
				@endif
			</ul>
		</div>
		<div class="review-section total-review">

			<ul class="review-list">
				@php $rooms = \Modules\Hotel\Models\HotelRoomBooking::getByBookingId($booking->id) @endphp
				@if(!empty($rooms))
					@foreach($rooms as $room)
						<li class="flex-wrap">
							<div class="label">{{$room->room->title}} * {{$room->number}}</div>
							<div class="val">
								{{format_money($room->price * $room->number)}}
							</div>
						</li>
					@endforeach
						<li class="flex-wrap">

								<div class="flex-grow-0 flex-shrink-0 w-100">
									<p class="text-center">
										<a data-toggle="modal" data-target="#detailBookingDate{{$booking->code}}" aria-expanded="false"
										   aria-controls="detailBookingDate{{$booking->code}}">
											{{__('Detail')}} <i class="icofont-list"></i>
										</a>
									</p>
								</div>
						</li>
				@endif
				@php $hunting = $booking->getJsonMeta('animal') @endphp

				@if(!empty($hunting))
					<li>
						<div class="label-title"><strong>{{__("Hunting list:")}}</strong></div>
					</li>
                    @foreach($hunting as $animal)
                        <li>
                            <div>{{ $animal }}</div>
                        </li>
                    @endforeach

				@endif
				@php
					$list_all_fee = [];
                    if(!empty($booking->buyer_fees)){
                        $buyer_fees = json_decode($booking->buyer_fees , true);
                        $list_all_fee = $buyer_fees;
                    }
                    if(!empty($vendor_service_fee = $booking->vendor_service_fee)){
                        $list_all_fee = array_merge($list_all_fee , $vendor_service_fee);
                    }
				@endphp

				<li class="final-total d-block">
					<div class="d-flex justify-content-between">
						<div class="label">{{__("Total:")}}</div>
						<div class="val">{{format_money($booking->total)}}</div>
					</div>
					@if($booking->status !='draft')
						<div class="d-flex justify-content-between">
							<div class="label">{{__("Paid:")}}</div>
							<div class="val">{{format_money($booking->paid)}}</div>
						</div>
						@if($booking->paid < $booking->total )
							<div class="d-flex justify-content-between">
								<div class="label">{{__("Remain:")}}</div>
								<div class="val">{{format_money($booking->total - $booking->paid)}}</div>
							</div>
						@endif
					@endif
				</li>
                @include ('Booking::frontend/booking/checkout-deposit-amount')
			</ul>
		</div>
	</div>
</div>
<?php
$dateDetail = $service->detailBookingEachDate($booking);
;?>
<div class="modal fade" id="detailBookingDate{{$booking->code}}" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true" style="background-color: #00000060">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title text-center">{{__('Detail')}}</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				@if(!empty($rooms))
					<ul class="review-list list-unstyled">
					@foreach($rooms as $room)
						<li class="mb-3 pb-1 border-bottom">
							<h6 class="label text-center font-weight-bold mb-1">{{$room->room->title}} * {{$room->number}}</h6>
							@if(!empty($dateDetail[$room->room_id]))
								<div>
									@includeIf("Hotel::frontend.booking.detail-room",['roomDate'=>$dateDetail[$room->room_id]])
								</div>
							@endif
							<div class="d-flex justify-content-between font-weight-bold px-2">
								<span>{{__("Total:")}}</span>
								<span>{{format_money($room->price * $room->number)}}</span>
							</div>
						</li>
					@endforeach
					</ul>
				@endif
			</div>
		</div>
	</div>
</div>

@php $lang_local = app()->getLocale() @endphp
<div class="booking-review">
    <h4 class="booking-review-title">{{__("Booking Hunting")}}</h4>
    <div class="booking-review-content">
        <div class="review-section">
            <div class="service-info">
                <div>
                    <h3 class="service-name"><a href="{{$animal_service->getDetailUrl()}}">{!! clean($animal_service->title) !!}</a></h3>
                </div>
                <div>
                    @if($image_url = $animal_service->image_url)
                        @if(!empty($disable_lazyload))
                            <img src="{{$animal_service->image_url}}" class="img-responsive" alt="{!! clean($service_translation->title) !!}">
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
                @if($booking->start_date_animal)
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

                @php $hunting = $booking->getJsonMeta('animal') @endphp

                @if(!empty($hunting))
                    <li>
                        <div class="label-title"><strong>{{__("Hunting list:")}}</strong></div>
                    </li>
                    @foreach($hunting as $animal)
                        <li>
                            <div>{{ $animal }}</div>
                        </li>
                    @endforeach

                @endif
                @php
                    $list_all_fee = [];
                    if(!empty($booking->buyer_fees)){
                        $buyer_fees = json_decode($booking->buyer_fees , true);
                        $list_all_fee = $buyer_fees;
                    }
                    if(!empty($vendor_service_fee = $booking->vendor_service_fee)){
                        $list_all_fee = array_merge($list_all_fee , $vendor_service_fee);
                    }
                @endphp

                <li class="final-total d-block">
                    <div class="d-flex justify-content-between">
                        <div class="label">{{__("Total:")}}</div>
{{--                        <div class="val">{{format_money($booking->total)}}</div>--}}
                    </div>
                    @if($booking->status !='draft')
                        <div class="d-flex justify-content-between">
                            <div class="label">{{__("Paid:")}}</div>
                            <div class="val">{{format_money($booking->paid)}}</div>
                        </div>
                        @if($booking->paid < $booking->total )
                            <div class="d-flex justify-content-between">
                                <div class="label">{{__("Remain:")}}</div>
                                <div class="val">{{format_money($booking->total - $booking->paid)}}</div>
                            </div>
                        @endif
                    @endif
                </li>
{{--                @include ('Booking::frontend/booking/checkout-deposit-amount')--}}
            </ul>
        </div>
    </div>
</div>


