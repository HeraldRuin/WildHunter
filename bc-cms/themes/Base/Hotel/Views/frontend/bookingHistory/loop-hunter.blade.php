<tr>
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
        @if($booking->type === 'hotel')
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
                    data-bs-content="
                    {{ __(':count rooms', ['count' => $booking->hotelRoom->first()?->number ?? 0]) }}<br>
                    {{ __(':type rooms', ['type' => $booking->hotelRoom->first()?->title ?? '—']) }}<br>
                    {{ 7 }}/{{ $booking->hotelRoom->first()?->number ?? 0 }}">
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
                    data-bs-content="
                    {{ __(':count rooms', ['count' => $booking->hotelRoom->first()?->number ?? 0]) }}<br>
                    {{ __(':type rooms', ['type' => $booking->hotelRoom->first()?->title ?? '—']) }}<br>
                    {{ 7 }}/{{ $booking->hotelRoom->first()?->number ?? 0 }}">
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
        <div>{{$booking->statusNameForUser}}</div>
        @if($booking->status_for_user === \Modules\Booking\Models\Booking::START_COLLECTION && $booking->updated_at)
            <div class="text-muted collection-timer" data-start="{{ $booking->updated_at->timestamp * 1000 }}">[0 мин]</div>
        @endif
    </td>

    <td class="price-cell">
        @if($booking->type === 'hotel')
            <div>{{ format_money($booking->total) }}</div>
        @endif
        @if($booking->type === 'hotel_animal')
            <div>{{ format_money($booking->total + $booking->amount_hunting) }}</div>
        @endif
        <button
            type="button"
            class="btn btn-info btn-sm details-btn mt-2"
            data-bs-toggle="popover"
            data-bs-trigger="click"
            data-bs-html="true"
            data-bs-placement="right"
            data-bs-content="
            <strong>Start:</strong> {{ display_date($booking->start_date) }}<br>
            <strong>End:</strong> {{ display_date($booking->end_date) }}<br>
            <strong>Duration:</strong> {{ $booking->duration_days }} {{ __('days') }}">
            Подробности
        </button>
    </td>

    <td>{{format_money($booking->paid)}}</td>
    <td>{{format_money($booking->total - $booking->paid)}}</td>
    <td>
        @php
            $isInvited = $booking->isInvited();
            $isCollectionStatus = $booking->status_for_user === \Modules\Booking\Models\Booking::START_COLLECTION;
        @endphp

        @if($isInvited && $isCollectionStatus)
            {{-- Для приглашенного охотника в статусе "сбор охотников" показываем только кнопку "Открыть приглашение" --}}
            <button
                type="button"
                class="btn btn-primary btn-sm mt-2"
                data-bs-toggle="modal"
                data-bs-target="#invitationModal{{ $booking->id }}"
                onclick="openInvitationModal({{ $booking->id }})">
                {{__("Open invitation")}}
            </button>
        @else
            {{-- Обычные кнопки для создателя брони или вендора --}}
            @if($booking->status === 'confirmed')
                <button
                    type="button"
                    class="btn btn-primary btn-sm mt-2"
                    data-bs-toggle="modal"
                    data-bs-target="#collectionModal{{ $booking->id }}"
                    @click="openCollectionModal({{ $booking->id }})">
                    {{__("Open collection")}}
                </button>
            @endif
            @if($userRole === 'hunter' && $booking->status !== 'cancelled')
                <button
                    type="button"
                    class="btn btn-primary btn-sm mt-2"
                    data-bs-toggle="modal"
                    data-bs-target="#bookingAddServiceModal{{ $booking->id }}">
                    {{__("Add services")}}
                </button>
            @endif
            @if($userRole === 'hunter' && $booking->status !== 'cancelled')
                <a href="{{ $booking->getDetailUrl() }}?select_place=1" target="_blank" class="btn btn-primary btn-sm mt-2">
                    {{__("Select bed place")}}
                </a>
            @endif
            @if(!in_array($booking->status, [\Modules\Booking\Models\Booking::CANCELLED, \Modules\Booking\Models\Booking::COMPLETED]))
                <button
                    type="button"
                    class="btn btn-danger btn-sm mt-2"
                    data-bs-toggle="modal"
                    data-bs-target="#cancelBookingModal{{ $booking->id }}">
                    {{__("Cancel")}}
                </button>
            @endif
        @endif
    </td>
</tr>

{{-- Модальное окно для сбора охотников --}}
@include('Booking::frontend.collection-modal', ['booking' => $booking])

{{-- Модальное окно для добавления услуг --}}
@include('Booking::frontend.add-services-modal', ['booking' => $booking])

{{-- Модальное окно для просмотра приглашения --}}
@include('Booking::frontend.invitation-modal', ['booking' => $booking])

{{-- Отмена бронирования --}}
<div class="modal fade" id="cancelBookingModal{{ $booking->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{__('Cancel booking')}} #{{ $booking->id }}</h5>
            </div>
            <div class="modal-body">
                <p>{{__('Are you sure you want to cancel this booking?')}}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('No, keep booking')}}</button>
                <button type="button" class="btn btn-danger" @click="cancelBooking($event, {{ $booking->id }})">{{__('Yes, cancel')}}</button>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
                new bootstrap.Popover(el);
            });
        });
    </script>
@endpush
