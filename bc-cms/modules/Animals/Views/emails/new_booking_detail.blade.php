<?php
$translation = $service->translate();
$lang_local = app()->getLocale();
?>
<div class="b-panel-title mt-4">{{__('Animal details')}}</div>
<div class="b-table-wrap">
    <table class="b-table" cellspacing="0" cellpadding="0">
        <tr>
            <td class="label">{{__('Animals')}}</td>
            <td class="val">
                <a href="{{$service->getDetailUrl()}}">{!! clean($translation->title) !!}</a>
            </td>
        </tr>

        @if(!empty($booking->total_hunting))
            <tr>
                <td class="label">{{__('Hunters count')}}</td>
                <td class="val"><strong>{{$booking->total_hunting}}</strong></td>
            </tr>
        @endif

        @if($booking->start_date_animal)
            <tr>
                <td class="label">{{__('Hunting date')}}</td>
                <td class="val">{{display_date($booking->start_date_animal)}}</td>
            </tr>
        @endif

            @if(!empty($booking->amount_hunting))
                <tr>
                    <td class="label">{{__('Hunting amount')}}</td>
                    <td class="val"><strong>{{format_money($booking->amount_hunting)}}</strong></td>
                </tr>
            @endif

        {{-- Итоги по оплате --}}
        <tr>
            <td class="label fsz21">{{__('Total')}}</td>
            <td class="val fsz21"><strong style="color: #FA5636">{{format_money($booking->total)}}</strong></td>
        </tr>
        <tr>
            <td class="label fsz21">{{__('Paid')}}</td>
            <td class="val fsz21"><strong style="color: #FA5636">{{format_money($booking->paid)}}</strong></td>
        </tr>
        @if($booking->total > $booking->paid)
            <tr>
                <td class="label fsz21">{{__('Remain')}}</td>
                <td class="val fsz21"><strong style="color: #FA5636">{{format_money($booking->total - $booking->paid)}}</strong></td>
            </tr>
        @endif
    </table>
</div>
@if(empty($hideButton))
    @if(empty($showSeparateServices) && isset($isInvitation) && !$isInvitation)
    <div class="text-center mt20">
        <a href="{{ route("user.booking_history") }}" target="_blank" class="btn btn-primary manage-booking-btn">{{__('Manage Bookings')}}</a>
    </div>
    @else
        <div class="text-center mt20">
            <a href="{{ route("user.booking_history") }}" target="_blank" class="btn btn-primary manage-booking-btn">{{__('Go to collection')}}</a>
        </div>
    @endif
@endif
