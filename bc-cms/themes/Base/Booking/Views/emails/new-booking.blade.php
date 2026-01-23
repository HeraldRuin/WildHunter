@extends('Email::layout')
@section('content')

    <div class="b-container">
        <div class="b-panel">
            @switch($to)
                @case ('admin')
                    <h3 class="email-headline"><strong>{{__('Hello Administrator')}}</strong></h3>
                    <p>{{__('New booking has been made')}}</p>
                @break
                @case ('vendor')
                    <h3 class="email-headline"><strong>{{__('Hello :name',['name'=>$booking->vendor->nameOrEmail ?? ''])}}</strong></h3>
                    <p>{{__('Your service has new booking')}}</p>
                @break

                @case ('customer')
                    @php
                        // Для охотника (создателя брони) используем имя и фамилию из профиля пользователя
                        $customerName = $booking->first_name ?? '';
                        if($booking->create_user) {
                            $hunter = \App\User::find($booking->create_user);
                            if($hunter) {
                                $customerName = trim(($hunter->first_name ?? '') . ' ' . ($hunter->last_name ?? ''));
                                if(empty($customerName)) {
                                    $customerName = $hunter->display_name ?? $hunter->email ?? '';
                                }
                            }
                        }
                        // Если имя не найдено, используем из брони
                        if(empty($customerName)) {
                            $customerName = trim(($booking->first_name ?? '') . ' ' . ($booking->last_name ?? ''));
                        }
                    @endphp
                    <h3 class="email-headline"><strong>{{__('Hello :name',['name'=>$customerName])}}</strong></h3>
                    <p>{{__('Thank you for booking with us. Here are your booking information:')}}</p>
                @break

            @endswitch

            @php
                $detailView = null;
                if ($service && $service->email_new_booking_file) {
                    $viewPath = str_replace('.blade.php', '', $service->email_new_booking_file);
                    if (view()->exists($viewPath)) {
                        $detailView = $viewPath;
                    } else {
                        $moduleName = ucfirst($service->object_model);
                        $fallbackViewPath = $moduleName . '::emails.new_booking_detail';
                        if (view()->exists($fallbackViewPath)) {
                            $detailView = $fallbackViewPath;
                        }
                    }
                }
            @endphp

            @if($detailView)
                @include($detailView)
            @else
                {{-- Заголовок блока с деталями бронирования --}}
                <div class="b-panel-title">{{__('Booking details')}}</div>
                <div class="b-table-wrap">
                    <table class="b-table" cellspacing="0" cellpadding="0">
                        <tr>
                            <td class="label">{{__('Booking Number')}}</td>
                            <td class="val">#{{$booking->id}}</td>
                        </tr>
                        <tr>
                            <td class="label">{{__('Service')}}</td>
                            <td class="val">{{$service->title ?? ''}}</td>
                        </tr>
                        <tr>
                            <td class="label">{{__('Booking Status')}}</td>
                            <td class="val">{{$booking->status_name}}</td>
                        </tr>
                        <tr>
                            <td class="label">{{__('Booking Date')}}</td>
                            <td class="val">{{display_datetime($booking->created_at)}}</td>
                        </tr>
                        <tr>
                            <td class="label">{{__('Total')}}</td>
                            <td class="val">{{format_money($booking->total)}}</td>
                        </tr>
                    </table>
                </div>
            @endif
        </div>

        @php
            $showCustomerPanel = true;

            if ($to === 'customer') {
                $userId = $booking->customer_id ?? $booking->create_user ?? null;

                if ($userId) {
                    $emailCustomerUser = \App\User::find($userId);
                    if ($emailCustomerUser && $emailCustomerUser->hasRole('hunter')) {
                        $showCustomerPanel = false;
                    }
                }
            }

            // Для админа и вендора всегда показываем информацию о клиенте
            if ($to === 'admin' || $to === 'vendor') {
                $showCustomerPanel = true;
            }
        @endphp

        @if($showCustomerPanel)
            @include('Booking::emails.parts.panel-customer')
        @endif
{{--        @include('Booking::emails.parts.panel-passengers')--}}
    </div>
@endsection
