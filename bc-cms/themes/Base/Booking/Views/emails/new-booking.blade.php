@extends('Email::layout')
@section('content')

    <div class="b-container">
        <div class="b-panel">
            @if($to === 'admin')
                @php
                    $adminName = __('Administrator');
                    if(!empty($baseAdmin)) {
                        $adminName = trim(($baseAdmin->first_name ?? '') . ' ' . ($baseAdmin->last_name ?? ''));
                        if(empty($adminName)) {
                            $adminName = $baseAdmin->display_name ?? $baseAdmin->email ?? __('Administrator');
                        }
                    }
                @endphp
                <h3 class="email-headline"><strong>{{__('Hello :name',['name'=>$adminName])}}</strong></h3>
                <p>{{__('New booking has been made')}}</p>
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
            @endif

            @switch($to)
                @case ('admin')
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
                        if(empty($customerName)) {
                            $customerName = trim(($booking->first_name ?? '') . ' ' . ($booking->last_name ?? ''));
                        }
                    @endphp
                    <h3 class="email-headline"><strong>{{__('Hello :name',['name'=>$customerName])}}</strong></h3>
                    <p>{{__('Thank you for booking with us. Here are your booking information:')}}</p>
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

            @endswitch

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
                        // Проверяем наличие отеля
                        if($booking->hotel_id) {
                            $hotelService = $booking->hotel;
                            $hasHotel = true;
                        } elseif($booking->object_model === 'hotel' && $booking->object_id) {
                            $hotelService = $service;
                            $hasHotel = true;
                        }

                        // Проверяем наличие животного
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
                        $hideCollectionAnimalButton = false;
                    @endphp
                    @include($animalDetailView)
                @endif
            @else
                @php
                    // Определяем, какой сервис использовать в зависимости от типа брони
                    $currentService = null;
                    if ($hasAnimal && $animalService) {
                        $currentService = $animalService;
                    } elseif ($hasHotel && $hotelService) {
                        $currentService = $hotelService;
                    } else {
                        $currentService = $service;
                    }

                    $detailView = null;
                    if ($currentService && $currentService->email_new_booking_file) {
                        $viewPath = str_replace('.blade.php', '', $currentService->email_new_booking_file);
                        if (view()->exists($viewPath)) {
                            $detailView = $viewPath;
                        } else {
                            $moduleName = ucfirst($currentService->object_model);
                            $fallbackViewPath = $moduleName . '::emails.new_booking_detail';
                            if (view()->exists($fallbackViewPath)) {
                                $detailView = $fallbackViewPath;
                            }
                        }
                    } elseif ($hasAnimal) {
                        // Если это животное, но нет кастомного шаблона, используем стандартный
                        $detailView = 'Animals::emails.new_booking_detail';
                    } elseif ($hasHotel) {
                        // Если это отель, но нет кастомного шаблона, используем стандартный
                        $detailView = 'Hotel::emails.new_booking_detail';
                    } elseif ($currentService && $currentService->object_model) {
                        // Если не определили тип, но есть object_model, пробуем использовать его
                        $moduleName = ucfirst($currentService->object_model);
                        $fallbackViewPath = $moduleName . '::emails.new_booking_detail';
                        if (view()->exists($fallbackViewPath)) {
                            $detailView = $fallbackViewPath;
                        }
                    } elseif ($service && $service->object_model) {
                        // Последняя попытка: используем object_model из основного сервиса
                        $moduleName = ucfirst($service->object_model);
                        $fallbackViewPath = $moduleName . '::emails.new_booking_detail';
                        if (view()->exists($fallbackViewPath)) {
                            $detailView = $fallbackViewPath;
                            $currentService = $service;
                        }
                    }
                @endphp

                @if($detailView && view()->exists($detailView))
                    @php
                        $service = $currentService;

                        if (!($hasHotel && $hasAnimal)) {
                            $showSeparateServices = true;
                            if ($hasAnimal) {
                                $hideCollectionAnimalButton = false;
                            }
                            if ($hasHotel) {
                                $hideCollectionHotelButton = false;
                            }
                        }
                    @endphp
                    @include($detailView)
                @else
                    {{-- Fallback: если шаблон деталей не найден, показываем базовую информацию --}}
                    <div class="b-panel-title">{{__('Booking details')}}</div>
                    <div class="b-table-wrap">
                        <table class="b-table" cellspacing="0" cellpadding="0">
                            <tr>
                                <td class="label">{{__('Booking Number')}}</td>
                                <td class="val">#{{$booking->id}}</td>
                            </tr>
                            @if($currentService)
                                <tr>
                                    <td class="label">{{__('Service')}}</td>
                                    <td class="val">{{$currentService->title ?? ''}}</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="label">{{__('Booking Status')}}</td>
                                <td class="val">{{$booking->statusName}}</td>
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
