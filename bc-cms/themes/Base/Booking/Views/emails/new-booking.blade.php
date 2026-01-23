@extends('Email::layout')
@section('content')

    <div class="b-container">
        <div class="b-panel">
            @switch($to)
{{--                @case ('admin')--}}
{{--                    @php--}}
{{--                        // Для админа базы используем имя и фамилию из профиля--}}
{{--                        $adminName = __('Administrator');--}}
{{--                        if(isset($baseAdmin) && $baseAdmin) {--}}
{{--                            $adminName = trim(($baseAdmin->first_name ?? '') . ' ' . ($baseAdmin->last_name ?? ''));--}}
{{--                            if(empty($adminName)) {--}}
{{--                                $adminName = $baseAdmin->display_name ?? $baseAdmin->email ?? __('Administrator');--}}
{{--                            }--}}
{{--                        }--}}
{{--                    @endphp--}}
{{--                    <h3 class="email-headline"><strong>{{__('Hello :name',['name'=>$adminName])}}</strong></h3>--}}
{{--                    <p>{{__('New booking has been made')}}</p>--}}
{{--                @break--}}
                @case ('vendor')
                    <h3 class="email-headline">
                        <strong>{{ __('Hello :name', ['name' => $booking->vendor->nameOrEmail ?? '']) }}</strong>
                    </h3>
                <div class="b-table-wrap mb-4">
                    <table class="b-table" cellspacing="0" cellpadding="0">
                        <tr>
                            <td class="label">{{__('Booking Number')}}</td>
                            <td class="val">#{{$booking->id}}</td>
                        </tr>
                        <tr>
                            <td class="label">{{__('Booking Status')}}</td>
                            <td class="val">{{$booking->statusName}}</td>
                        </tr>
                    </table>
                </div>
                @break

                @case ('customer')
                    @php
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
                // Проверяем, есть ли оба сервиса (отель и охота)
                $hasHotel = false;
                $hasAnimal = false;
                $hotelService = null;
                $animalService = null;

                // Определяем основной сервис
                $mainService = $service;

                // Проверяем наличие отеля
                if($booking->hotel_id) {
                    $hotelService = $booking->hotel;
                    $hasHotel = true;
                } elseif($booking->object_model === 'hotel' && $booking->object_id) {
                    $hotelService = $mainService;
                    $hasHotel = true;
                }

                // Проверяем наличие животного
                if($booking->animal_id) {
                    $animalService = $booking->animal;
                    $hasAnimal = true;
                } elseif($booking->object_model === 'animal' && $booking->object_id) {
                    $animalService = $mainService;
                    $hasAnimal = true;
                }

                // Если основной сервис - отель, но есть животное, получаем животное отдельно
                if($hasHotel && $booking->object_model === 'hotel' && $booking->animal_id) {
                    $animalService = $booking->animal;
                    $hasAnimal = true;
                }

                // Если основной сервис - животное, но есть отель, получаем отель отдельно
                if($hasAnimal && $booking->object_model === 'animal' && $booking->hotel_id) {
                    $hotelService = $booking->hotel;
                    $hasHotel = true;
                }
            @endphp

            {{-- Если есть оба сервиса, показываем их раздельно --}}
            @if($hasHotel && $hasAnimal)

                @php
                    $hotelDetailView = null;
                    if ($hotelService && $hotelService->email_new_booking_file) {
                        $viewPath = str_replace('.blade.php', '', $hotelService->email_new_booking_file);
                        if (view()->exists($viewPath)) {
                            $hotelDetailView = $viewPath;
                        } else {
                            $fallbackViewPath = 'Hotel::emails.new_booking_detail';
                            if (view()->exists($fallbackViewPath)) {
                                $hotelDetailView = $fallbackViewPath;
                            }
                        }
                    } else {
                        $hotelDetailView = 'Hotel::emails.new_booking_detail';
                    }
                @endphp
                @if($hotelDetailView && view()->exists($hotelDetailView))
                    @php
                        $service = $hotelService;
                        $showSeparateServices = true; // Флаг для скрытия перекрестной информации
                    @endphp
                    @include($hotelDetailView)
                @endif

                @php
                    $animalDetailView = null;
                    if ($animalService && $animalService->email_new_booking_file) {
                        $viewPath = str_replace('.blade.php', '', $animalService->email_new_booking_file);
                        if (view()->exists($viewPath)) {
                            $animalDetailView = $viewPath;
                        } else {
                            $fallbackViewPath = 'Animals::emails.new_booking_detail';
                            if (view()->exists($fallbackViewPath)) {
                                $animalDetailView = $fallbackViewPath;
                            }
                        }
                    } else {
                        $animalDetailView = 'Animals::emails.new_booking_detail';
                    }
                @endphp
                @if($animalDetailView && view()->exists($animalDetailView))
                    @php
                        $service = $animalService;
                        $showSeparateServices = true; // Флаг для скрытия перекрестной информации
                    @endphp
                    @include($animalDetailView)
                @endif

                {{-- Одна кнопка в конце после обоих блоков --}}
                <div class="text-center mt20">
                    <a href="{{ route('user.booking_history', ['booking_id' => $booking->id]) }}" target="_blank" class="btn btn-primary manage-booking-btn">{{__('Manage Bookings')}}</a>
                </div>
            @else
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
