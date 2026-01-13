<tr>
    <td class="booking-history-type">
        {{ $booking->service ? $booking->id : $booking->id }}
    </td>

    <td class="a-hidden">{{display_date($booking->created_at)}}</td>

    <td>
        {{-- Для hunter показываем данные отеля без popover, но кликабельно --}}
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
    <td class="{{$booking->status}} a-hidden">{{$booking->statusName}}</td>

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
        <a href="{{ $booking->getDetailUrl() }}" target="_blank" class="btn btn-primary btn-sm mt-2">
            {{__("Open collection")}}
        </a>
        <button
            type="button"
            class="btn btn-primary btn-sm mt-2"
            data-bs-toggle="modal"
            data-bs-target="#bookingAddServiceModal{{ $booking->id }}">
            {{__("Add services")}}
        </button>
        <a href="{{ $booking->getDetailUrl() }}?select_place=1" target="_blank" class="btn btn-primary btn-sm mt-2">
            {{__("Select bed place")}}
        </a>
    </td>
</tr>

<div class="modal fade" id="bookingAddServiceModal{{ $booking->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить услуги для брони #{{ $booking->id }}</h5>
            </div>
            <div class="modal-body">
                <form id="addServicesForm{{ $booking->id }}">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Услуги отеля</h6>
                            <div class="card card-body">
                                @if(isset($hotelServices) && count($hotelServices) > 0)
                                    @foreach($hotelServices as $service)
                                        <div class="mb-2 d-flex align-items-center">
                                            <input
                                                class="me-3"
                                                type="checkbox"
                                                name="hotel_services[]"
                                                value="{{ $service->id }}"
                                                id="hotel_service_{{ $booking->id }}_{{ $service->id }}"
                                                style="width: 18px; height: 18px; flex-shrink: 0;"
                                                @if(isset($selectedServices) && in_array($service->id, $selectedServices)) checked @endif>
                                            <label class="mb-0" for="hotel_service_{{ $booking->id }}_{{ $service->id }}" style="cursor: pointer;">
                                                {{ $service->title ?? $service->name }}
                                                @if(isset($service->price))
                                                    <span class="text-muted">({{ format_money($service->price) }})</span>
                                                @endif
                                            </label>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="mb-2 d-flex align-items-center">
                                        <input class="me-3" type="checkbox" name="hotel_services[]" value="1" id="hotel_service_{{ $booking->id }}_1" style="width: 18px; height: 18px; flex-shrink: 0;">
                                        <label class="mb-0" for="hotel_service_{{ $booking->id }}_1" style="cursor: pointer;">
                                            Завтрак
                                        </label>
                                    </div>
                                    <div class="mb-2 d-flex align-items-center">
                                        <input class="me-3" type="checkbox" name="hotel_services[]" value="2" id="hotel_service_{{ $booking->id }}_2" style="width: 18px; height: 18px; flex-shrink: 0;">
                                        <label class="mb-0" for="hotel_service_{{ $booking->id }}_2" style="cursor: pointer;">
                                            Ужин
                                        </label>
                                    </div>
                                    <div class="mb-2 d-flex align-items-center">
                                        <input class="me-3" type="checkbox" name="hotel_services[]" value="3" id="hotel_service_{{ $booking->id }}_3" style="width: 18px; height: 18px; flex-shrink: 0;">
                                        <label class="mb-0" for="hotel_service_{{ $booking->id }}_3" style="cursor: pointer;">
                                            Трансфер
                                        </label>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="mb-3">Услуги охоты</h6>
                            <div class="card card-body">
                                @if(isset($huntingServices) && count($huntingServices) > 0)
                                    @foreach($huntingServices as $service)
                                        <div class="mb-2 d-flex align-items-center">
                                            <input
                                                class="me-3"
                                                type="checkbox"
                                                name="hunting_services[]"
                                                value="{{ $service->id }}"
                                                id="hunting_service_{{ $booking->id }}_{{ $service->id }}"
                                                style="width: 18px; height: 18px; flex-shrink: 0;"
                                                @if(isset($selectedServices) && in_array($service->id, $selectedServices)) checked @endif>
                                            <label class="mb-0" for="hunting_service_{{ $booking->id }}_{{ $service->id }}" style="cursor: pointer;">
                                                {{ $service->title ?? $service->name }}
                                                @if(isset($service->price))
                                                    <span class="text-muted">({{ format_money($service->price) }})</span>
                                                @endif
                                            </label>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="mb-2 d-flex align-items-center">
                                        <input class="me-2" type="checkbox" name="hunting_services[]" value="1" id="hunting_service_{{ $booking->id }}_1" style="width: 18px; height: 18px; flex-shrink: 0;">
                                        <label class="mb-0" for="hunting_service_{{ $booking->id }}_1" style="cursor: pointer;">
                                            Гид
                                        </label>
                                    </div>
                                    <div class="mb-2 d-flex align-items-center">
                                        <input class="me-2" type="checkbox" name="hunting_services[]" value="2" id="hunting_service_{{ $booking->id }}_2" style="width: 18px; height: 18px; flex-shrink: 0;">
                                        <label class="mb-0" for="hunting_service_{{ $booking->id }}_2" style="cursor: pointer;">
                                            Оружие
                                        </label>
                                    </div>
                                    <div class="mb-2 d-flex align-items-center">
                                        <input class="me-2" type="checkbox" name="hunting_services[]" value="3" id="hunting_service_{{ $booking->id }}_3" style="width: 18px; height: 18px; flex-shrink: 0;">
                                        <label class="mb-0" for="hunting_service_{{ $booking->id }}_3" style="cursor: pointer;">
                                            Лицензия
                                        </label>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="saveServices({{ $booking->id }})">Сохранить</button>
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
