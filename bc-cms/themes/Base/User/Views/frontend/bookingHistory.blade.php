@extends('layouts.user')
@section('content')
    <div class="pb-2 mb-3">
        <div class="d-flex align-items-center justify-content-between border-bottom" style="height: 90px;">
            <h2 class="m-0">{{ __("Booking History") }}</h2>
            @if($userRole === 'baseadmin')
                <a href="/hotel/{{$hotelSlug}}?userRole=baseadmin" class="btn btn-success text-nowrap" style="margin-right: 220px;" target="_blank">Создать событие</a>
            @endif
        </div>
    </div>


    @include('admin.message')
    <div class="booking-history-manager">
        <div class="tabbable">
            @if(empty($bookingId))
            <ul class="nav nav-tabs ht-nav-tabs">
                <?php $status_type = Request::query('status'); ?>
                <li class="@if(empty($status_type)) active @endif">
                    <a href="{{route("user.booking_history")}}">{{__("All Booking")}}</a>
                </li>
                @if(!empty($statues))
                    @foreach($statues as $status)
                        <li class="@if(!empty($status_type) && $status_type == $status) active @endif">
                            <a href="{{route("user.booking_history",['status'=>$status])}}">{{booking_status_to_text($status)}}</a>
                        </li>
                    @endforeach
                @endif
            </ul>
            @endif

            @if(!empty($bookingId))
                <div class="d-flex align-items-center justify-content-between mt-3 mb-3">
{{--                    <span class="text-muted"><i class="fa fa-filter"></i> {{__("Showing filtered results for booking")}} #{{ $bookingId }}</span>--}}
                    <div style="margin-right: 180px;">
                        @php
                            $clearAllUrl = route('user.booking_history');
                            if (request()->has('user')) {
                                $clearAllUrl .= '?user=' . request()->query('user');
                            }
                        @endphp
                        <a href="{{ $clearAllUrl }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fa fa-times-circle"></i> {{__("Clear filter and show all bookings")}}
                        </a>
                    </div>
                </div>
            @endif

            @if(!empty($bookings) and $bookings->total() > 0)
                <div class="tab-content" id="booking-history">
                    <div class="table-responsive table-width">
                        <table class="table table-bordered  table-booking-history">
                            <thead>
                            @if($userRole === 'baseadmin')
                            <tr>
                                <th class="number-booking">{{__("Number Booking")}}</th>
                                <th class="data-booking">{{__("Date Booking")}}</th>
                                <th class="client-booking">{{__("Client Booking")}}</th>
                                <th class="type-booking">{{__("Type Booking")}}</th>
                                <th class="detail-booking">{{__("Detail Booking")}}</th>
                                <th class="status-booking">{{__("Status Booking")}}</th>
                                <th class="amount-booking">{{__("Amount Booking")}}</th>
                                <th class="paid-booking">{{__("Paid Booking")}}</th>
                                <th class="remnant-booking">{{__("Remnant Booking")}}</th>
                                <th class="event-booking">{{__("Event Booking")}}</th>
                            </tr>
                            @else
                                <th class="number-booking">{{__("Number Booking")}}</th>
                                <th class="data-booking">{{__("Date Booking")}}</th>
                                <th class="client-booking">{{__("Base Name")}}</th>
                                <th class="type-booking">{{__("Type Booking")}}</th>
                                <th class="detail-booking">{{__("Detail Booking")}}</th>
                                <th class="status-booking">{{__("Status Booking")}}</th>
                                <th class="amount-booking">{{__("Amount Booking")}}</th>
                                <th class="paid-booking">{{__("Paid Booking")}}</th>
                                <th class="remnant-booking">{{__("Remnant Booking")}}</th>
                                <th class="event-booking">{{__("Event Booking")}}</th>
                            @endif
                            </thead>
                            <tbody>
                            @foreach($bookings as $booking)
                                @php
                                    $bookingType = $booking->type ?? null;
                                    $objectModel = $booking->object_model ?? null;

                                    $isHotel = in_array($bookingType, ['hotel', 'hotel_animal']) || $objectModel === 'hotel';
                                    $isAnimal = $bookingType === 'animal' || $objectModel === 'animal';

                                    if ($isHotel || $isAnimal) {
                                        $loopFile = 'loop-' . $userRole;
                                        if ($isHotel) {
                                            $moduleName = 'Hotel';
                                        } else {
                                            $moduleName = 'Animal';
                                        }
                                    } else {
                                        $loopFile = 'loop';
                                        if (!empty($objectModel)) {
                                            $moduleName = ucfirst(strtolower($objectModel));
                                        } else {
                                            $moduleName = 'Booking';
                                            $loopFile = 'detail';
                                        }
                                    }
                                @endphp
                                @if(view()->exists($moduleName.'::frontend.bookingHistory.' . $loopFile))
                                    @include($moduleName.'::frontend.bookingHistory.' . $loopFile)
                                @else
                                    <tr>
                                        <td colspan="10" class="text-center text-danger">
                                            {{__("View not found for booking type")}}: {{ $bookingType ?? $objectModel ?? 'unknown' }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="bc-pagination">
                        {{$bookings->appends(request()->query())->links()}}
                    </div>
                </div>
            @else
                {{__("No Booking History")}}
            @endif
        </div>
        <div class="modal" tabindex="-1" id="modal_booking_detail">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{__('Booking ID: #')}} <span class="user_id"></span></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex justify-content-center">{{__("Loading...")}}</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('Close')}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $('.btn-info-booking').on('click',function (e){
            var btn = $(this);
            $(this).find('.user_id').html(btn.data('id'));
            $(this).find('.modal-body').html('<div class="d-flex justify-content-center">{{__("Loading...")}}</div>');
            var modal = $("#modal_booking_detail");
            $.get(btn.data('ajax'), function (html){
                    modal.find('.modal-body').html(html);
                }
            )
        })
    </script>
@endpush

