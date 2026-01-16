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
