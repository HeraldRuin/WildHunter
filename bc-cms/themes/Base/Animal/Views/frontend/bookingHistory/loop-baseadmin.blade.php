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
        <div>
            {{$booking->statusName}}
            @if($booking->status === \Modules\Booking\Models\Booking::START_COLLECTION && $booking->hotel && $booking->hotel->collection_timer_hours)
                ({{$booking->hotel->collection_timer_hours}} {{ __('ч') }})
            @endif
        </div>
        @if($booking->status === \Modules\Booking\Models\Booking::START_COLLECTION)
            @php
                // Получаем данные таймера: время начала и количество часов
                $startRecord = \Illuminate\Support\Facades\DB::table('bc_booking_meta')
                    ->where('booking_id', $booking->id)
                    ->where('name', 'collection_start_at')
                    ->first();
                
                $timerHoursRecord = \Illuminate\Support\Facades\DB::table('bc_booking_meta')
                    ->where('booking_id', $booking->id)
                    ->where('name', 'collection_timer_hours')
                    ->first();
                
                $endTimestamp = null;
                $initialTimerHours = null;
                
                // Вычисляем оставшееся время: таймер в часах - прошедшее время
                if ($startRecord && $timerHoursRecord) {
                    try {
                        $startCarbon = \Carbon\Carbon::parse($startRecord->val);
                        $timerHours = (int)$timerHoursRecord->val;
                        $initialTimerHours = $timerHours; // Начальное значение таймера
                        $now = \Carbon\Carbon::now();
                        
                        // Прошедшее время в секундах, затем переводим в часы с точностью
                        $elapsedSeconds = $now->diffInSeconds($startCarbon, false);
                        $elapsedHours = $elapsedSeconds / 3600; // Точное значение в часах
                        
                        // Оставшееся время в часах
                        $remainingHours = max(0, $timerHours - $elapsedHours);
                        
                        // Вычисляем время окончания для JavaScript (текущее время + оставшееся время)
                        $remainingSeconds = $remainingHours * 3600;
                        $endCarbon = $now->copy()->addSeconds((int)$remainingSeconds);
                        $endTimestamp = $endCarbon->timestamp * 1000;
                    } catch (\Exception $e) {
                        $endTimestamp = null;
                    }
                }
            @endphp
            @if($endTimestamp && $initialTimerHours)
                <div class="text-muted collection-timer" data-end="{{ $endTimestamp }}" data-initial-hours="{{ $initialTimerHours }}">({{ $initialTimerHours }}) [0 мин]</div>
            @endif
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
        @if($userRole === 'baseadmin' && $booking->status === 'processing' && $booking->status != 'completed')
            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                    data-bs-target="#confirmBookingModal{{ $booking->id }}">
                {{ __("Booking apply") }}
            </button>
        @endif
            @if($userRole === 'baseadmin' && !in_array($booking->status, [\Modules\Booking\Models\Booking::CANCELLED, \Modules\Booking\Models\Booking::COMPLETED]))
            <button
                type="button"
                class="btn btn-primary btn-sm mt-2"
                data-bs-toggle="modal"
                data-bs-target="#bookingAddServiceModal{{ $booking->id }}">
                {{__("Add services")}}
            </button>
        @endif
        @if($userRole === 'baseadmin'&& $booking->status !== 'processing' && !in_array($booking->status, [\Modules\Booking\Models\Booking::CANCELLED, \Modules\Booking\Models\Booking::COMPLETED]))
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
