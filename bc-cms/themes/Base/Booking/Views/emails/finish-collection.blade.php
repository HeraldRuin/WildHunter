@php use App\User; @endphp
@extends('Email::layout')
@section('content')

    <div class="b-container">
        <div class="b-panel">
            @switch($to)
                @case ('BaseAdmin')
                    @php
                        if(!empty($user)) {
                            $adminName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
                            $adminName = $adminName?: $user->user_name?: $user->last_name?: __('BaseAdmin');
                        }
                    @endphp
                    <h3 class="email-headline"><strong>{{__('Hello :name',['name'=>$adminName])}}</strong></h3>
                    <p>{{__('The booking status has been updated')}}</p>
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
                       if(!empty($user)) {
                            $customerName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
                            if(empty($customerName)) {
                                $customerName =  $customerName?: $user->user_name?: $user->last_name?: __('Hunter');
                            }
                        }
                    @endphp
                    <h3 class="email-headline"><strong>{{__('Hello :name',['name'=>$customerName])}}</strong></h3>
                    <p>{{__('Your booking status has been updated')}}</p>

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

                    @if(!empty($customMessage))
                        <hr>
                        <p>{{ $customMessage }}</p>
                    @endif
                    @break

            @endswitch

            @php
                // Проверяем, есть ли оба сервиса (отель и охота)
                $hasHotel = false;
                $hasAnimal = false;
                $hotelService = null;
                $animalService = null;
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
                        $showSeparateServices = false;
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
                        $showSeparateServices = true;
                        $hideCollectionAnimalButton = true;
                    @endphp
                    @include($animalDetailView)
                @endif
            @else
                {{-- Если только один сервис, показываем его --}}
                @if(!empty($service) && !empty($service->email_new_booking_file) && view()->exists($service->email_new_booking_file))
                    @include($service->email_new_booking_file)
                @elseif(!empty($service))
                    @php
                        $serviceType = class_basename(get_class($service));
                        $possibleViews = [
                            'Animal' => 'Animals::emails.new_booking_detail',
                            'Hotel' => 'Hotel::emails.new_booking_detail',
                        ];
                        $viewName = $possibleViews[$serviceType] ?? null;
                    @endphp
                    @if($viewName && view()->exists($viewName))
                        @include($viewName)
                    @else
                        <p>{{__('Booking details')}}: #{{ $booking->id }}</p>
                        <p>{{__('Status')}}: {{ $booking->status_name }}</p>
                    @endif
                @else
                    <p>{{__('Booking details')}}: #{{ $booking->id }}</p>
                    <p>{{__('Status')}}: {{ $booking->status_name }}</p>
                @endif
            @endif
        </div>

        @php
            $showCustomerPanel = false;

            if ($to === 'BaseAdmin') {
                $showCustomerPanel = true;
            }
        @endphp

        @if($showCustomerPanel)
            @include('Booking::emails.parts.panel-customer')
        @endif
    </div>
@endsection
