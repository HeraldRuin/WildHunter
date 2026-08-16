@php
    $customerFirstName = $booking->first_name;
    $customerLastName = $booking->last_name;

    if (empty($customerFirstName) || empty($customerLastName)) {
        if ($booking->create_user) {
            $hunter = \App\User::find($booking->create_user);
            if ($hunter) {
                if (empty($customerFirstName)) {
                    $customerFirstName = $hunter->first_name ?? '';
                }
                if (empty($customerLastName)) {
                    $customerLastName = $hunter->last_name ?? '';
                }
            }
        }
    }
@endphp
<div class="b-panel">
    <div class="b-panel-title">{{__('Customer information')}}</div>
    <div class="b-table-wrap">
        <div class="b-table b-table-div">
            <div class="info-first-name b-tr">
                <div class="label">{{__('First name')}}</div>
                <div class="val">{{$customerFirstName}}</div>
            </div>
            <div class="info-last-name b-tr" style="clear: both">
                <div class="label">{{__('Last name')}}</div>
                <div class="val">{{$customerLastName}}</div>
            </div>

            @if(!empty($booking->customer_notes))
                @include('Booking::emails.parts.notes-customer', ['booking' => $booking])
            @endif

        </div>
    </div>
</div>
