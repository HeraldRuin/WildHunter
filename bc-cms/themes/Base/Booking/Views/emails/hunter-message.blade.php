@extends('Email::layout')
@section('content')

    <div class="b-container">
        <div class="b-panel">
            @php
                $customerName = __('Hunter');
                $inviterName = '';

                if($booking->create_user) {
                    $creator = \App\User::find($booking->create_user);
                    if($creator) {
                        $inviterName = trim(($creator->first_name ?? '') . ' ' . ($creator->last_name ?? ''));
                        if(empty($inviterName)) {
                            $inviterName = $creator->display_name ?? '';
                        }
                    }
                }
            @endphp

            <h3 class="email-headline"><strong>{{__('Hello :name',['name'=>$customerName])}}</strong></h3>

            @if(isset($isInvitation) && $isInvitation)
                <div>{{ __('Booking status has been updated') }}<strong>{{ $inviterName }}</strong>{{ __('invites you to hunt') }}</div>
            @else
                <div>{{ $bodyText ?? __('Booking status has been updated') }}</div>
            @endif

            <div class="b-table-wrap mt20 mb20">
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

            @php
                $service = $booking->service;
                $hasHotel = false;
                $hasAnimal = false;
                $hotelService = null;
                $animalService = null;
                $bookingType = $booking->type ?? null;

                if($bookingType === 'hotel') {
                    $hasHotel = true;
                    if($booking->object_model === 'hotel' && $booking->object_id) {
                        $hotelService = $service;
                    } elseif($booking->hotel_id) {
                        $hotelService = $booking->hotel;
                    }
                } elseif($bookingType === 'animal') {
                    $hasAnimal = true;
                    if($booking->object_model === 'animal' && $booking->object_id) {
                        $animalService = $service;
                    } elseif($booking->animal_id) {
                        $animalService = $booking->animal;
                    }
                } elseif($bookingType === 'hotel_animal') {
                    $hasHotel = true;
                    $hasAnimal = true;

                    // Получаем отель
                    if($booking->object_model === 'hotel' && $booking->object_id) {
                        $hotelService = $service;
                    } elseif($booking->hotel_id) {
                        $hotelService = $booking->hotel;
                    }

                    // Получаем животное
                    if($booking->object_model === 'animal' && $booking->object_id) {
                        $animalService = $service;
                    } elseif($booking->animal_id) {
                        $animalService = $booking->animal;
                    }
                } else {
                    // Fallback: используем старую логику, если type не установлен
                    if($booking->hotel_id) {
                        $hotelService = $booking->hotel;
                        $hasHotel = true;
                    } elseif($booking->object_model === 'hotel' && $booking->object_id) {
                        $hotelService = $service;
                        $hasHotel = true;
                    }

                    if($booking->animal_id) {
                        $animalService = $booking->animal;
                        $hasAnimal = true;
                    } elseif($booking->object_model === 'animal' && $booking->object_id) {
                        $animalService = $service;
                        $hasAnimal = true;
                    }
                }
            @endphp

            {{-- Если есть оба сервиса, показываем их раздельно --}}
            @if($hasHotel && $hasAnimal)

                @php
                    $hotelDetailView = null;
                    if ($hotelService && !empty($hotelService->email_new_booking_file)) {
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
                        $showSeparateServices = true;
                        $hideCollectHotelButton = true;
                        $hideAnimalButton = false;
                    @endphp
                    @include($hotelDetailView)
                @endif

                @php
                    $animalDetailView = null;
                    if ($animalService && !empty($animalService->email_new_booking_file)) {
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
                    @endphp
                    @include($animalDetailView)
                @endif

            @elseif(!empty($service))
                {{-- Если только один сервис, используем стандартную логику --}}
                @if(!empty($service->email_new_booking_file) && view()->exists($service->email_new_booking_file))
                    @include($service->email_new_booking_file)
                @else
                    @php
                        $serviceType = class_basename(get_class($service));
                        $possibleViews = [
                            'Animal' => 'Animals::emails.new_booking_detail',
                            'Hotel' => 'Hotel::emails.new_booking_detail',
                        ];
                        $viewName = $possibleViews[$serviceType] ?? null;
                    @endphp
                    @if($viewName && view()->exists($viewName))
                        <div class="mt20">
                            @include($viewName)
                        </div>
                    @else
                        <p>{{__('Booking details')}}: #{{ $booking->id }}</p>
                        <p>{{__('Status')}}: {{ $booking->status_name }}</p>
                    @endif
                @endif
            @else
                <p>{{__('Booking details')}}: #{{ $booking->id }}</p>
                <p>{{__('Status')}}: {{ $booking->status_name }}</p>
            @endif
        </div>
    </div>
@endsection

