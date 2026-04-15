@php
    $room = $booking->hotelRoom->first();
    $bookingRoom = $booking->roomsBooking->first();
    $isInvited = $booking->isInvited();
    $isCollectionStatus = in_array($booking->status, [\Modules\Booking\Models\Booking::START_COLLECTION, \Modules\Booking\Models\Booking::PREPAYMENT_COLLECTION,
            \Modules\Booking\Models\Booking::FINISHED_PREPAYMENT, \Modules\Booking\Models\Booking::BED_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_BED, \Modules\Booking\Models\Booking::PAID, \Modules\Booking\Models\Booking::COMPLETED]);
    $invitation = $booking->getCurrentUserInvitation();
    $isInvitationAccepted = $invitation && $invitation->status === 'accepted';
@endphp

<tr data-booking-id="{{ $booking->id }}">
    <td class="booking-history-type">
        {{ $booking->service ? $booking->id : $booking->id }}
    </td>

    <td class="a-hidden">{{display_date($booking->created_at)}}</td>

    <td>
        @if($booking->hotel)
            @php
                $hotelTranslation = $booking->hotel->translate();
                $hotelTitle = $hotelTranslation->title ?? $booking->hotel->title ?? 'Отель #' . $booking->hotel_id;
                $hotelUrl = $booking->hotel->getDetailUrl() ?? '#';
            @endphp
            <a href="{{ $hotelUrl }}" target="_blank" class="text-primary text-decoration-none">
                {{ $hotelTitle }}
            </a>
        @else
            <span class="text-muted">Отель #{{ $booking->hotel_id ?? '—' }}</span>
        @endif
    </td>

    <td class="type a-hidden">{{ $booking->typeText }}</td>

    <td class="a-hidden">
        @if($booking->type === 'hotel' && $userRole === 'hunter')
            <strong>Проживание:</strong>
            <div>
                {{__("CheckIn")}} : {{display_date($booking->start_date)}} <br>
                {{__("Exit")}} : {{display_date($booking->end_date)}} <br>
                {{__("Duration")}} :
                @if($booking->duration_days <= 1)
                    {{__(':count nights',['count'=>$booking->duration_days])}} <br>
                @else
                    {{__(':count nights',['count'=>$booking->duration_days])}} <br>
                @endif

                {{__(':total guest',['count'=>$booking->total_guests])}} <br>
                <button
                    type="button"
                    class="btn btn-info btn-sm details-btn mt-2"
                    data-bs-toggle="popover"
                    data-bs-trigger="click"
                    data-bs-html="true"
                    data-bs-placement="right"
                    data-bs-custom-class="popover-width"
                    data-bs-content="
                    {{ __(':count rooms', ['count' => $booking->roomsBooking->count()]) }}<br>
                     @foreach($booking->roomsBooking as $bookingRoom)
                        {{ $bookingRoom->room?->title ?? '—' }},
                        <span>вместимость = </span> {{ $bookingRoom->room?->adults ?? '—' }};
                        <span>кол-во = </span> {{ $bookingRoom->number ?? '—' }};
                        <span>цена = </span> {{ $bookingRoom->room?->price ?? '—' }} р/сут
                        <br>
                    @endforeach">
                    Подробности
                </button>
            </div>
        @endif
        @if($booking->type === 'hotel_animal')
            <strong>Проживание:</strong>
            <div>
                {{__("CheckIn")}} : {{display_date($booking->start_date)}} <br>
                {{__("Exit")}} : {{display_date($booking->end_date)}} <br>
                {{__("Duration")}} :
                @if($booking->duration_days <= 1)
                    {{__(':count nights',['count'=>$booking->duration_days])}} <br>
                @else
                    {{__(':count nights',['count'=>$booking->duration_days])}} <br>
                @endif

                {{__(':total guest',['count'=>$booking->total_guests])}} <br>
                <button
                    type="button"
                    class="btn btn-info btn-sm details-btn mt-2"
                    data-bs-toggle="popover"
                    data-bs-trigger="click"
                    data-bs-html="true"
                    data-bs-placement="right"
                    data-bs-custom-class="popover-width"
                    data-bs-content="
                    {{ __(':count rooms', ['count' => $booking->roomsBooking->count()]) }}<br>
                     @foreach($booking->roomsBooking as $bookingRoom)
                        {{ $bookingRoom->room?->title ?? '—' }},
                        <span>вместимость = </span> {{ $bookingRoom->room?->adults ?? '—' }};
                        <span>кол-во = </span> {{ $bookingRoom->number ?? '—' }};
                        <span>цена = </span> {{ $bookingRoom->room?->price ?? '—' }} р/сут
                        <br>
                    @endforeach">
                    Подробности
                </button>
            </div>
            <strong>Охота:</strong>
            <div>
                {{__("Hunting Date")}} : {{display_date($booking->start_date_animal)}} <br>
                {{ __("Animals") }}:
                @if($booking->animal && $booking->animal->title)
                    {{ $booking->animal->title }}
                @else
                    <span style="color: red;">Удалено админом</span>
                @endif
                <br>
                {{__(':total guest',['count'=>$booking->total_hunting])}} <br>
            </div>
        @endif
    </td>

    <td class="{{$booking->status_for_user}} a-hidden">
        <div>
            @php
                // Получаем информацию об охотниках
                $totalHuntersNeeded = $booking->total_hunting ?? 0;
                $allInvitations = $booking->getAllInvitations();
                $acceptedInvitations = $allInvitations->where('status', 'accepted');
                $acceptedCount = $acceptedInvitations->count();
                $paidInvitations = $allInvitations->where('prepayment_paid', true);
                $paidCount = $paidInvitations->count();

                // Получаем мастера охотника
                $bookingHunter = $booking->bookingHunter;
                $masterHunter = null;
                if ($bookingHunter && $bookingHunter->invitedBy) {
                    $masterHunter = $bookingHunter->invitedBy;
                }

                // Получаем список принявших приглашение
                $acceptedHunters = $acceptedInvitations->map(function($invitation) {
                    if ($invitation->hunter) {
                        return [
                            'name' => trim(($invitation->hunter->first_name ?? '') . ' ' . ($invitation->hunter->last_name ?? '')),
                            'user_name' => $invitation->hunter->user_name ?? '',
                            'email' => $invitation->hunter->email ?? '',
                            'is_external' => false
                        ];
                    } elseif ($invitation->email) {
                        return [
                            'name' => '',
                            'user_name' => '',
                            'email' => $invitation->email,
                            'is_external' => true
                        ];
                    }
                    return null;
                })->filter()->values();
            @endphp

{{$booking->statusNameForUser}}

@if($booking->status === \Modules\Booking\Models\Booking::START_COLLECTION && $booking->hotel && $booking->hotel->collection_timer_hours)
({{$booking->hotel->collection_timer_hours}} {{ __('ч') }})
@endif

@if($booking->status === \Modules\Booking\Models\Booking::PREPAYMENT_COLLECTION && $booking->hotel && $booking->hotel->paid_timer_hours)
({{$booking->hotel->paid_timer_hours}} {{ __('ч') }})
@endif

@if($booking->status === \Modules\Booking\Models\Booking::BED_COLLECTION && $booking->hotel && $booking->hotel->bed_timer_hours)
({{$booking->hotel->bed_timer_hours}} {{ __('ч') }})
@endif
</div>

        @if($booking->status === \Modules\Booking\Models\Booking::START_COLLECTION)
            @php
                $endTimestamp = null;
                try {
                    $collectionEndAt = $booking->getMeta('collection_end_at');
                    if ($collectionEndAt) {
                        $endCarbon = \Carbon\Carbon::parse($collectionEndAt);
                        $endTimestamp = $endCarbon->timestamp * 1000;
                    }
                } catch (\Exception $e) {
                    $endTimestamp = null;
                }
            @endphp

            @if($endTimestamp)
                <div class="text-muted collection-timer" data-end="{{ $endTimestamp }}"
                     data-booking-id="{{ $booking->id }}">[0 мин 00 сек]
                </div>
            @endif

            @if($totalHuntersNeeded > 0)
                <div class="text-muted mt-3" style="font-size: 0.9em;">
                    Собранно {{ $acceptedCount }}/{{ $totalHuntersNeeded }}
                </div>
            @endif
        @endif

        @if($booking->status === \Modules\Booking\Models\Booking::PREPAYMENT_COLLECTION)
            @php
                $endTimestamp = null;
                try {
                    $bedsEndAt = $booking->getMeta('paid_end_at');
                    if ($bedsEndAt) {
                        $endCarbon = \Carbon\Carbon::parse($bedsEndAt);
                        $endTimestamp = $endCarbon->timestamp * 1000;
                    }
                } catch (\Exception $e) {
                    $endTimestamp = null;
                }
        @endphp

            @if($endTimestamp)
                <div class="text-muted paid-timer" data-end="{{ $endTimestamp }}"
                     data-booking-id="{{ $booking->id }}">[0 мин 00 сек]
                </div>
            @endif
        @endif

        @if($booking->status === \Modules\Booking\Models\Booking::BED_COLLECTION)
            @php
                $endTimestamp = null;
                try {
                    $bedsEndAt = $booking->getMeta('beds_end_at');
                    if ($bedsEndAt) {
                        $endCarbon = \Carbon\Carbon::parse($bedsEndAt);
                        $endTimestamp = $endCarbon->timestamp * 1000;
                    }
                } catch (\Exception $e) {
                    $endTimestamp = null;
                }
        @endphp

            @if($endTimestamp)
                <div class="text-muted beds-timer" data-end="{{ $endTimestamp }}"
                     data-booking-id="{{ $booking->id }}">[0 мин 00 сек]
                </div>
            @endif
        @endif

            @if(in_array($booking->status, [\Modules\Booking\Models\Booking::PREPAYMENT_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_PREPAYMENT, \Modules\Booking\Models\Booking::BED_COLLECTION]))
                    @if($booking->status === \Modules\Booking\Models\Booking::BED_COLLECTION)
                        <div class="mt-3">
                            {{'Предоплата собрана'}}
                            <div class="text-muted mt-1" style="font-size: 0.9em;">
                                Оплачено {{ $paidCount }}/{{ $acceptedCount }}
                            </div>
                        </div>
                    @else
                        <div class="text-muted mt-1" style="font-size: 0.9em;">
                            Оплачено {{ $paidCount }}/{{ $acceptedCount }}
                        </div>
                    @endif

                    <div class="mt-3">
                        {{'Сбор завершен'}}
                        <div class="text-muted mt-1" style="font-size: 0.9em;">
                            Собранно {{ $acceptedCount }}/{{ $totalHuntersNeeded }}
                        </div>
                    </div>
           @endif
    </td>

    <td>
        @if($isInvited && $isCollectionStatus)
            @if($isInvitationAccepted && in_array($booking->type, [\Modules\Booking\Models\Booking::BookingTypeHotel, \Modules\Booking\Models\Booking::BookingTypeHotelAnimal]))
                @if(!$booking->is_master_hunter && in_array($booking->status, [\Modules\Booking\Models\Booking::FINISHED_PREPAYMENT, \Modules\Booking\Models\Booking::BED_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_BED, \Modules\Booking\Models\Booking::PAID, \Modules\Booking\Models\Booking::COMPLETED]))
                    <button
                        type="button"
                        class="btn btn-primary btn-sm mt-2"
                        data-bs-toggle="modal"
                        @click="openCalculatingModal({{ $booking }}, $event)">
                        {{__("Calculating")}}
                    </button>
                @endif
            @endif
        @endif

        @if($booking->is_master_hunter && in_array($booking->status, [\Modules\Booking\Models\Booking::FINISHED_PREPAYMENT, \Modules\Booking\Models\Booking::BED_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_BED, \Modules\Booking\Models\Booking::PAID, \Modules\Booking\Models\Booking::COMPLETED]))
            <button
                type="button"
                class="btn btn-primary btn-sm mt-2"
                data-bs-toggle="modal"
                @click="openCalculatingModal({{ $booking }}, $event)">
                {{__("Calculating")}}
            </button>
        @endif
    </td>

    <td>
        @if($isInvited && $isCollectionStatus)
            @if(!$isInvitationAccepted)
                <button
                    type="button"
                    class="btn btn-primary btn-sm mt-2"
                    onclick="openModal('invitationModal', {{ $booking->id }})">
                    {{__("Open invitation")}}
                </button>
            @endif
            @if($isInvitationAccepted && in_array($booking->type, ['hotel_animal', 'hotel']))
                @if(!$booking->is_master_hunter && in_array($booking->status, [\Modules\Booking\Models\Booking::PREPAYMENT_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_PREPAYMENT, \Modules\Booking\Models\Booking::START_COLLECTION, \Modules\Booking\Models\Booking::BED_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_BED]))
                    <button
                        type="button"
                        class="btn btn-primary btn-sm mt-2"
                        data-bs-toggle="modal"
                        @click="openCollectionAsHunter({{ $booking->id }})">
                        {{__("Open collection")}}
                    </button>
                @endif
                @if(!$booking->is_master_hunter && in_array($booking->status,  [\Modules\Booking\Models\Booking::FINISHED_PREPAYMENT, \Modules\Booking\Models\Booking::BED_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_BED]))
                    <button
                        type="button"
                        class="btn btn-primary btn-sm mt-2"
                        data-bs-toggle="modal"
                        @click="openBookingPlacesModal({{ $booking }}, $event)">
                        {{__("Select bed place")}}
                    </button>
                @endif
                @if(!$booking->is_master_hunter && $booking->status ===  \Modules\Booking\Models\Booking::PREPAYMENT_COLLECTION)
                    <button
                        type="button"
                        class="btn btn-primary btn-sm mt-2"
                        data-bs-toggle="modal"
                        data-booking-id="{{ $booking->id }}"
                        @click="openBookingPrepaymentPaid({{ $booking->id }}, $event)">
                        {{__("Prepayment")}}
                    </button>
                @endif
            @endif
        @else
            @if($booking->is_master_hunter && in_array($booking->status, [\Modules\Booking\Models\Booking::PROCESSING, \Modules\Booking\Models\Booking::CONFIRMED, \Modules\Booking\Models\Booking::START_COLLECTION, \Modules\Booking\Models\Booking::PREPAYMENT_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_PREPAYMENT, \Modules\Booking\Models\Booking::BED_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_BED]))
                <button
                    type="button"
                    class="btn btn-danger btn-sm mt-2"
                    data-bs-toggle="modal"
                    @click="openCancelBookingModal({{ $booking->id }}, $event)">
                    {{__("Cancele booking")}}
                </button>
            @endif

            @if($booking->is_master_hunter)
                @if(in_array($booking->status, [\Modules\Booking\Models\Booking::PREPAYMENT_COLLECTION, \Modules\Booking\Models\Booking::START_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_PREPAYMENT, \Modules\Booking\Models\Booking::BED_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_BED]))
                    <button
                        type="button"
                        class="btn btn-primary btn-sm mt-2"
                        data-bs-toggle="modal"
                        data-bs-target="#collectionModal{{ $booking->id }}">
                        {{__("Open collection")}}
                    </button>
                    @elseif($booking->status === \Modules\Booking\Models\Booking::CONFIRMED)
                    <button
                        type="button"
                        class="btn btn-primary btn-sm mt-2"
                        data-bs-toggle="modal"
                        @click="openCollectionAsMaster({{ $booking->id }}, $event)">
                        {{__("Open collection")}}
                    </button>
                @endif
            @endif

            @if($booking->is_master_hunter && $booking->status === \Modules\Booking\Models\Booking::PREPAYMENT_COLLECTION)
                <button
                    type="button"
                    class="btn btn-primary btn-sm mt-2"
                    data-bs-toggle="modal"
                    data-booking-id="{{ $booking->id }}"
                    @click="openBookingPrepaymentPaid({{ $booking->id }}, $event)">
                    {{__("Prepayment")}}
                </button>
            @endif

            @if($booking->is_master_hunter && in_array($booking->status, [\Modules\Booking\Models\Booking::FINISHED_PREPAYMENT, \Modules\Booking\Models\Booking::BED_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_BED]))
                    <button
                    type="button"
                    class="btn btn-primary btn-sm mt-2"
                    data-bs-toggle="modal"
                    @click="openBookingPlacesModal({{ $booking }}, $event)">
                    {{__("Select bed place")}}
                </button>
            @endif

                @if($booking->is_master_hunter && in_array($booking->status, [\Modules\Booking\Models\Booking::FINISHED_PREPAYMENT, \Modules\Booking\Models\Booking::BED_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_BED]))
                <button
                    type="button"
                    class="btn btn-primary btn-sm mt-2"
                    data-bs-toggle="modal"
                    data-bs-target="#bookingAddServiceModal{{ $booking->id }}">
                    {{__("Add services")}}
                </button>
            @endif
        @endif
    </td>
</tr>

{{-- Модальное окно для сбора охотников --}}
@include('Booking::frontend.modals.collection-modal', ['booking' => $booking])

{{-- Модальное окно для добавления услуг --}}
@include('Hotel::.frontend.bookingHistory.addServices.add-services-hunter')

{{-- Модальное окно для просмотра приглашения --}}
@include('Booking::frontend.modals.invitation-modal', ['booking' => $booking])

{{-- Модальное окно для предоплаты --}}
@include('Booking::frontend.modals.prepayment-modal', ['booking' => $booking])

{{-- Модальное окно для койко место --}}
@include('Booking::frontend.modals.place-modal', ['booking' => $booking])

{{-- Модальное окно для калькуляции --}}
@include('Booking::frontend.modals.calculating.calculating-hunter-modal', ['booking' => $booking])
