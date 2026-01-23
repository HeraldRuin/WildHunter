@php
    // Получаем данные клиента из брони или из профиля пользователя (охотника)
    $customerFirstName = $booking->first_name;
    $customerLastName = $booking->last_name;
    $customerEmail = $booking->email;
    $customerPhone = $booking->phone;

    // Если данных нет в брони, берем из профиля пользователя (охотника)
    if(empty($customerFirstName) || empty($customerLastName) || empty($customerEmail) || empty($customerPhone)) {
        if($booking->create_user) {
            $hunter = \App\User::find($booking->create_user);
            if($hunter) {
                if(empty($customerFirstName)) {
                    $customerFirstName = $hunter->first_name ?? '';
                }
                if(empty($customerLastName)) {
                    $customerLastName = $hunter->last_name ?? '';
                }
                if(empty($customerEmail)) {
                    $customerEmail = $hunter->email ?? '';
                }
                if(empty($customerPhone)) {
                    $customerPhone = $hunter->phone ?? '';
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
            <div class="info-email b-tr">
                <div class="label">{{__('Email')}}</div>
                <div class="val">{{$customerEmail}}</div>
            </div>
            <div class="info-phone b-tr">
                <div class="label">{{__('Phone')}}</div>
                <div class="val">{{$customerPhone}}</div>
            </div>
{{--            @if(isset($to) && $to === 'customer')--}}
{{--            <div class="info-address b-tr">--}}
{{--                <div class="label">{{__('Address line 1')}}</div>--}}
{{--                <div class="val">{{$booking->address}}</div>--}}
{{--            </div>--}}
{{--            <div class="info-address2 b-tr">--}}
{{--                <div class="label">{{__('Address line 2')}}</div>--}}
{{--                <div class="val">{{$booking->address2}}</div>--}}
{{--            </div>--}}
{{--            <div class="info-city b-tr">--}}
{{--                <div class="label">{{__('City')}}</div>--}}
{{--                <div class="val">{{$booking->city}}</div>--}}
{{--            </div>--}}
{{--            <div class="info-state b-tr">--}}
{{--                <div class="label">{{__('State/Province/Region')}}</div>--}}
{{--                <div class="val">{{$booking->state}}</div>--}}
{{--            </div>--}}
{{--            <div class="info-zip-code b-tr">--}}
{{--                <div class="label">{{__('ZIP code/Postal code')}}</div>--}}
{{--                <div class="val">{{$booking->zip_code}}</div>--}}
{{--            </div>--}}
{{--            <div class="info-country b-tr">--}}
{{--                <div class="label">{{__('Country')}}</div>--}}
{{--                <div class="val">{{get_country_name($booking->country)}}</div>--}}
{{--            </div>--}}
{{--            <div class="info-notes b-tr">--}}
{{--                <div class="label">{{__('Special Requirements')}}</div>--}}
{{--                <div class="val">{{$booking->customer_notes}}</div>--}}
{{--            </div>--}}
{{--            @endif--}}
        </div>
    </div>
</div>
