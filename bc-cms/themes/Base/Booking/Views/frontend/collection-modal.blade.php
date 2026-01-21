@php
    // Определяем количество охотников в зависимости от типа бронирования
    $huntersCount = 0;
    if ($booking->type === 'hotel') {
        $huntersCount = $booking->total_guests ?? 0;
    } elseif ($booking->type === 'animal') {
        $huntersCount = $booking->total_hunting ?? 0;
    } elseif ($booking->type === 'hotel_animal') {
        // Для hotel_animal берем только количество охотников из части animal
        $huntersCount = $booking->total_hunting ?? 0;
    }

    // Проверяем, является ли текущий пользователь приглашенным охотником
    $currentUserId = auth()->id();
    $isInvited = false;
    if ($currentUserId) {
        $isInvited = $booking->isInvited($currentUserId);
    }

    // Если пользователь приглашен, получаем список всех приглашенных охотников для просмотра
    $invitedHunters = [];
    if ($isInvited) {
        $invitations = $booking->getAllInvitations();
        foreach ($invitations as $invitation) {
            if ($invitation->hunter) {
                $isCurrentUser = $invitation->hunter->id == $currentUserId;
                $invitedHunters[] = [
                    'id' => $invitation->hunter->id,
                    'name' => $invitation->hunter->first_name . ' ' . $invitation->hunter->last_name,
                    'email' => $invitation->hunter->email,
                    'user_name' => $invitation->hunter->user_name ?? null,
                    'status' => $invitation->status,
                    'invited_at' => $invitation->invited_at,
                    'is_self' => $isCurrentUser,
                ];
            }
        }
    }
@endphp

<div class="modal fade" id="collectionModal{{ $booking->id }}" tabindex="-1" aria-hidden="true"
     data-hunters-count="{{ $huntersCount }}"
     data-booking-id="{{ $booking->id }}">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Open collection for booking') }} #{{ $booking->id }}</h5>
{{--                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>--}}
            </div>
            <div class="modal-body">
                @if($isInvited)
                    {{-- Шаблон для приглашенного охотника: только просмотр списка приглашенных --}}
                    @if($booking->status === \Modules\Booking\Models\Booking::FINISHED_COLLECTION)
                        <div class="alert alert-success mb-4">
                            <strong>{{ __('Collection completed') }}</strong>
                        </div>
                    @endif
                    <div class="mb-4">
                        <h6 class="mb-3">Приглашенные охотники</h6>
                        @if(count($invitedHunters) > 0)
                            <div class="list-group">
                                @foreach($invitedHunters as $hunter)
                                    <div class="list-group-item">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-2">
                                                <strong>{{ $hunter['name'] }}</strong>
                                                @if(isset($hunter['is_self']) && $hunter['is_self'])
                                                    <span class="badge bg-secondary ml-3">Вы</span>
                                                @endif
                                            </div>
                                            <div class="text-muted small mb-1">
                                                @if($hunter['user_name'])
                                                    (ник <strong>{{ $hunter['user_name'] }}</strong>)
                                                @else
                                                    (ник не задан)
                                                @endif
                                            </div>
                                            <div class="text-muted small mb-2">{{ $hunter['email'] }}</div>
                                            @if($hunter['status'] === 'accepted')
                                                <span class="badge bg-success">Приглашение принято</span>
                                            @elseif($hunter['status'] === 'pending')
                                                <span class="badge bg-warning">Приглашен</span>
                                            @elseif($hunter['status'] === 'declined')
                                                <span class="badge bg-danger">Приглашение отклонено</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">Охотники еще не приглашены</p>
                        @endif
                    </div>
                @else
                    {{-- Шаблон для владельца брони: полный функционал управления сбором --}}
                    <div class="mb-4">
                        <div v-for="(hunterSlot, index) in hunterSlots" :key="index" class="position-relative mb-3">
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1 position-relative">
                                    <input
                                        type="text"
                                        class="form-control me-2"
                                        :placeholder="'{{ __('Hunter nickname / last_name') }}'"
                                        v-model="hunterSlot.query"
                                        :disabled="hunterSlot.hunter && hunterSlot.hunter.invited && hunterSlot.hunter.invitation_status !== 'declined'"
                                        @input="searchHunterForSlot(index, {{ $booking->id }})"
                                        @change="handleHunterInputChange(index)"
                                        @focus="hunterSlot.showResults = true"
                                        @blur="setTimeout(() => { hunterSlot.showResults = false; }, 200)">
                                    <!-- Результаты поиска для этого слота -->
                                    <div v-if="hunterSlot.showResults"
                                         class="position-absolute w-100 bg-white border rounded shadow-sm mt-1"
                                         style="z-index: 1000; max-height: 300px; overflow-y: auto;">
                                        <!-- Спиннер при поиске -->
                                        <div v-if="hunterSlot.isSearching" class="p-2 text-muted text-center">
                                            {{ __('Searching...') }}
                                        </div>
                                        <!-- Результаты -->
                                        <div v-else-if="hunterSlot.results.length">
                                            <div v-for="hunter in hunterSlot.results"
                                                 :key="hunter.id"
                                                 :class="['p-2 border-bottom', (hunter.invited && hunter.invitation_status !== 'declined') ? 'bg-light text-muted' : 'cursor-pointer hover-bg-light']"
                                                 @click="!(hunter.invited && hunter.invitation_status !== 'declined') && selectHunterForSlot(index, hunter, {{ $booking->id }})"
                                                 @mousedown.prevent>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-grow-1">
                                                        <div>
                                                            <span class="text-muted small">
                                                                <template v-if="hunter.user_name">
                                                                    (ник <strong style="font-size: 14px;">@{{ hunter.user_name }}</strong>)
                                                                </template>
                                                                <template v-else>
                                                                    (ник не задан)
                                                                </template>
                                                            </span>
                                                            <span class="text-muted ms-2">@{{ hunter.first_name }} @{{ hunter.last_name }}</span>
                                                        </div>
                                                        <div class="text-muted small">@{{ hunter.email }}</div>
                                                        <div class="mt-1">
                                                            <span v-if="hunter.invited && hunter.invitation_status !== 'declined'" class="badge bg-success">
                                                                {{ __('Already invited') }}
                                                            </span>
                                                            <span v-else-if="hunter.invitation_status === 'declined'" class="badge bg-danger">
                                                                {{ __('Invitation declined') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Сообщение, если результатов нет -->
                                        <div v-else-if="hunterSlot.noResults && !hunterSlot.isSearching" class="p-2 border-bottom">
                                            <div class="text-muted small mb-2">
                                                {{ __('Hunters not found') }}
                                            </div>
                                            <div class="text-muted small mb-2">
                                                {{ __('You can send a message by email') }}
                                            </div>
                                            <button
                                                type="button"
                                                class="btn btn-outline-primary btn-sm"
                                                @click="toggleEmailInputForSlot(index)">
                                                <i class="fa fa-envelope me-1"></i>
                                                {{ __('Send email') }}
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Информация о выбранном охотнике (показываем только если текст в поле соответствует выбранному охотнику) -->
                                    <div
                                        v-if="hunterSlot.hunter && hunterSlot.query && hunterSlot.query.trim() === ((hunterSlot.hunter.user_name || (hunterSlot.hunter.first_name + ' ' + hunterSlot.hunter.last_name)).trim())"
                                        class="mt-2">
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="text-muted small">@{{ hunterSlot.hunter.email }}</span>
                                        </div>
                                    </div>
                                    <!-- Поле ввода сообщения (показывается при клике на кнопку почты или кнопки в блоке \"Охотники не найдены\") -->
                                    <div v-if="hunterSlot.showEmailInput" class="mt-2">
                                        <div v-if="!hunterSlot.hunter" class="mb-2">
                                            <input
                                                type="email"
                                                class="form-control form-control-sm"
                                                v-model="hunterSlot.emailAddress"
                                                placeholder="Введите email адрес">
                                        </div>
                                        <div class="d-flex align-items-start">
                                            <textarea
                                                class="form-control form-control-sm"
                                                rows="2"
                                                v-model="hunterSlot.emailMessage"
                                                placeholder="Введите сообщение"></textarea>
                                            <button
                                                type="button"
                                                class="btn btn-info btn-sm ms-3 ml-2"
                                                @click="sendEmailForSlot(index, {{ $booking->id }}, $event)">
                                                Отправить
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm ms-2 ml-2" style="min-width: 40px;"
                                                    @click="hunterSlot.showEmailInput = false">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start">
                                    <button
                                        v-if="hunterSlot.hunter && hunterSlot.query && hunterSlot.query.trim() === ((hunterSlot.hunter.user_name || (hunterSlot.hunter.first_name + ' ' + hunterSlot.hunter.last_name)).trim())"
                                        type="button"
                                        class="btn btn-sm me-2 ml-2"
                                        :class="(hunterSlot.hunter && hunterSlot.hunter.invited && hunterSlot.hunter.invitation_status !== 'declined') ? 'btn-success' : 'btn-outline-primary'"
                                        :disabled="!hunterSlot.hunter || (hunterSlot.hunter.invited && hunterSlot.hunter.invitation_status !== 'declined')"
                                        @click="inviteHunterForSlot(index, {{ $booking->id }}, $event)">
                                        <span v-text="(hunterSlot.hunter && hunterSlot.hunter.invited && hunterSlot.hunter.invitation_status !== 'declined') ? invitedText : inviteText"></span>
                                    </button>
                                    <button
                                        v-if="hunterSlot.hunter && hunterSlot.hunter.invited && hunterSlot.query && hunterSlot.query.trim() === ((hunterSlot.hunter.user_name || (hunterSlot.hunter.first_name + ' ' + hunterSlot.hunter.last_name)).trim())"
                                        type="button"
                                        class="btn btn-outline-secondary btn-sm ml-2"
                                        style="min-width: 40px;"
                                        @click="toggleEmailInputForSlot(index)"
                                        title="{{ __('Send email') }}">
                                        <i class="fa fa-envelope"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-3 border rounded">
                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            <button
                                type="button"
                                class="btn btn-info"
                                @if($booking->status === \Modules\Booking\Models\Booking::START_COLLECTION) disabled @endif
                                @click="startCollection($event, {{ $booking->id }})">
                                {{ __('Open collection') }}
                            </button>

                            <button
                                type="button"
                                class="btn btn-info mx-2 btn-extend-collection"
                                data-booking-id="{{ $booking->id }}"
                                disabled
                                @click="startCollection($event, {{ $booking->id }})">
                                {{ __('Extend collection') }}
                            </button>
                            <button
                                type="button"
                                class="btn btn-info"
                                @click="cancelCollection($event, {{ $booking->id }})">
                                {{ __('Cancel collection') }}
                            </button>
                            <button
                                type="button"
                                class="btn btn-info mx-2 btn-finish-collection"
                                data-booking-id="{{ $booking->id }}"
                                @click="finishCollection($event, {{ $booking->id }})">
                                {{ __('Finish collection') }}
                            </button>
                            <button
                                type="button"
                                class="btn btn-info mx-2">
                                {{ __('Opens collection') }}
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
