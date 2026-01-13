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
        @if($booking->type === 'animal')
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

                {{__(':total guest',['count'=>$booking->total_hunting])}}
            </div>
        @endif
    </td>
    <td class="{{$booking->status}} a-hidden">
        <div>{{$booking->statusName}}</div>
        @if($booking->status === \Modules\Booking\Models\Booking::START_COLLECTION && $booking->updated_at)
            <div class="text-muted collection-timer" data-start="{{ $booking->updated_at->timestamp * 1000 }}">[0 мин]</div>
        @endif
    </td>
    <td class="price-cell">
        <div>{{ format_money($booking->amount_hunting) }}</div>

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
            @if($booking->status === 'confirmed')
                <button
                    type="button"
                    class="btn btn-primary btn-sm mt-2"
                    @click="startCollection($event, {{ $booking->id }})">
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
    </td>

</tr>

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

<div class="modal fade" id="bookingAddServiceModal{{ $booking->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить услуги для брони #{{ $booking->id }}</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Услуги отеля</h6>
                        <div class="card card-body">
                            <button type="button" class="btn btn-primary btn-sm">Добавить услугу</button>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h6>Услуги охоты</h6>
                        <div class="card card-body">
                            <button type="button" class="btn btn-success btn-sm">Добавить услугу</button>
                            {{-- Можно добавить список услуг здесь --}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
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
