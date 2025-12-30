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
            data-bs-content="<strong>{{ $booking->creator->first_name }} {{ $booking->creator->last_name }}</strong><br>Email: {{ $booking->creator->email }}<br>Phone: {{ $booking->creator->phone }}"
            @click="openUserModal({{ $booking->creator->id }}, {{ $booking->id }})">
          {{ !empty($booking->creator->user_name) ? $booking->creator->user_name : $booking->creator->first_name }}
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
              {{__(':count rooms',['count'=>$booking->hotelRooms->first()->number])}}<br>
              {{__(':type rooms',['type'=>$booking->hotelRooms->first()->title])}}<br>
              {{ 7 }}/{{ $booking->hotelRooms->first()->number }}">
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
              {{__(':count rooms',['count'=>$booking->hotelRooms->first()->number])}}<br>
              {{__(':type rooms',['type'=>$booking->hotelRooms->first()->title])}}<br>
              {{ 7 }}/{{ $booking->hotelRooms->first()->number }}">
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
@if($booking->status === 'processing')
            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                    data-bs-target="#confirmBookingModal{{ $booking->id }}">
                {{ __("Booking apply") }}
            </button>
@endif
        <button
            type="button"
            class="btn btn-info btn-sm mt-2"
            data-bs-toggle="modal"
            data-bs-target="#bookingAddServiceModal{{ $booking->id }}">
            {{__("Add services")}}
        </button>
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

<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
                <label for="changeUserInput">Найти нового заказчика по нику:</label>
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
                            <strong class="text-dark">@{{ user.user_name }}</strong><br>
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


<script>
    document.addEventListener('DOMContentLoaded', function () {
        document
            .querySelectorAll('[data-bs-toggle="popover"]')
            .forEach(el => {
                new bootstrap.Popover(el);
            });
    });
</script>



