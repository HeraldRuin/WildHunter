<tr>
    <td class="booking-history-type">
        @if($service = $booking->service)
            <i class="{{$service->getServiceIconFeatured()}}"></i>
        @endif
        <small>{{$booking->object_model}}</small>
    </td>
    <td>
        @if($service = $booking->service)
            @php
                $translation = $service->translate();
            @endphp
            <a target="_blank" href="{{$service->getDetailUrl()}}">
                {{$translation->title}}
            </a>
        @else
            {{__("[Deleted]")}}
        @endif
    </td>
    <td class="a-hidden">{{display_date($booking->created_at)}}</td>
    <td class="a-hidden">
        {{__("Applications :count",['count'=>$booking->total_guests])}}
    </td>
    <td>{{format_money($booking->total)}}</td>
    <td>{{format_money($booking->paid)}}</td>
    <td>{{format_money($booking->total - $booking->paid)}}</td>
    <td class="{{$booking->status}} a-hidden">{{$booking->statusName}}</td>
    <td width="2%">
        @if($service = $booking->service)
            <a class="btn btn-xs btn-primary btn-info-booking" data-ajax="{{route('booking.modal',['booking'=>$booking])}}" data-toggle="modal" data-id="{{$booking->id}}" data-target="#modal_booking_detail">
                <i class="fa fa-info-circle"></i>{{__("Details")}}
            </a>
        @endif
        <a href="{{route('user.booking.invoice',['code'=>$booking->code])}}" class="btn btn-xs btn-primary btn-info-booking open-new-window mt-1" onclick="window.open(this.href); return false;">
            <i class="fa fa-print"></i>{{__("Invoice")}}
        </a>
        @if($booking->status == 'unpaid')
            <a href="{{route('booking.checkout',['code'=>$booking->code])}}" class="btn btn-xs btn-primary btn-info-booking open-new-window mt-1">
                {{__("Pay now")}}
            </a>
        @endif
        <a href="{{route('visa.user.booking-detail',['code'=>$booking->code])}}" class="btn btn-xs btn-primary mt-1 btn-info-booking px-1 d-inline-flex align-items-center" style="width: auto">
            <i class="fa fa-user mr-1"></i>
            {{__("Applications")}}
        </a>
    </td>
</tr>
