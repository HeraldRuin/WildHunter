<tr>
    <td class="booking-history-type">
        {{ $booking->service ? $booking->id : $booking->id }}
    </td>

    <td class="a-hidden">{{display_date($booking->created_at)}}</td>

    <td>
        <span
            class="user-popover cursor-pointer user-link"
            data-bs-toggle="popover"
            data-bs-trigger="hover"
            data-bs-html="true"
            data-bs-placement="right"
            data-bs-content="<strong>{{ $booking->creator?->first_name ?? '' }} {{ $booking->creator?->last_name ?? '' }}</strong><br>Email: {{ $booking->creator?->email ?? '' }}<br>Phone: {{ $booking->creator?->phone ?? '' }}"
            @click="{{ $userRole !== 'hunter' && $booking->creator ? "openUserModal({$booking->creator->id}, {$booking->id})" : '' }}">
                {{ $booking->creator ? (!empty($booking->creator->user_name) ? $booking->creator->user_name : $booking->creator->first_name) : 'N/A' }}
        </span>
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
                {{--            <button--}}
                {{--                type="button"--}}
                {{--                class="btn btn-info btn-sm mt-2"--}}
                {{--                data-bs-toggle="modal"--}}
                {{--                data-bs-target="#bookingDetailModal{{ $booking->id }}">--}}
                {{--                Подробности--}}
                {{--            </button>--}}
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
        @if($userRole === 'baseadmin' && $booking->status === 'processing' && $booking->status != 'completed')
            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                    data-bs-target="#confirmBookingModal{{ $booking->id }}">
                {{ __("Booking apply") }}
            </button>
        @endif
            @if($userRole === 'baseadmin' && $booking->status !== 'cancelled')
                <button
                    type="button"
                    class="btn btn-primary btn-sm mt-2"
                    data-bs-toggle="modal"
                    data-bs-target="#bookingAddServiceModal{{ $booking->id }}">
                    {{__("Add services")}}
                </button>
        @endif
        @if($userRole === 'baseadmin' && !in_array($booking->status, [\Modules\Booking\Models\Booking::CANCELLED, \Modules\Booking\Models\Booking::COMPLETED]))
            <button
                type="button"
                class="btn btn-success btn-sm mt-2"
                @click="completeBooking($event, {{ $booking->id }})">
                {{__("Complete booking")}}
            </button>
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

<div class="modal fade" id="confirmBookingModal{{ $booking->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Подтверждение брони #{{ $booking->id }}</h5>

            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите подтвердить эту бронь?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-success" @click="confirmBooking({{$booking->id}})">Подтвердить</button>
            </div>
        </div>
    </div>
</div>

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
                <button type="button" class="btn btn-danger btn-cancel-booking-confirm-vue" data-booking-id="{{ $booking->id }}">{{__('Yes, cancel')}}</button>
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
                                    {{-- <p class="text-muted">Услуги отеля не найдены</p> --}}
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
                                    {{-- <p class="text-muted">Услуги охоты не найдены</p> --}}
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

<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
                <label for="changeUserInput">Найти нового заказчика по нику или фамилии:</label>
                <input
                    type="text"
                    id="changeUserInput"
                    v-model="userSearchQuery"
                    class="form-control mb-2"
                    placeholder="Введите ник пользователя"
                    @input="searchUserDebounced">

                <div v-if="searchResults.length" class="mt-2">
                    <div
                        v-for="user in searchResults"
                        :key="user.id"
                        class="d-flex align-items-center justify-content-between p-2 mb-2 border rounded shadow-sm"
                        style="background-color: #f8f9fa;">
                        <div>
                            <strong class="text-dark">@{{ user.user_name }}</strong><span>@{{ user.user_name ? '(ник)' : '(ник не задан)' }}</span>
                            <strong class="text-dark">@{{ user.first_name }}</strong><span>(фамилия)</span>
                            <br>
                        </div>
                        <button v-if="!selectedUser || selectedUser.id !== user.id" class="btn btn-sm btn-primary" @click="selectUser(user)">
                            Выбрать
                        </button>
                    </div>
                </div>

                <div v-if="isSearching" class="text-muted">
                    Поиск...
                </div>
                <div v-if="noResults" class="text-danger">
                    Пользователь не найден
                </div>

                <button class="btn btn-primary mt-2" @click="saveUserChange">Сохранить</button>
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



