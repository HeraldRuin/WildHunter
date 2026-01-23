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

    $currentUserId = auth()->id();
    $isInvited = false;
    if ($currentUserId) {
        $creatorId = $booking->create_user ?? $booking->customer_id;
        $isCreator = ($currentUserId == $creatorId);

        if (!$isCreator) {
            $isInvited = $booking->isInvited($currentUserId);
        }
    }

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
            } elseif ($invitation->email) {
                $invitedHunters[] = [
                    'id' => null,
                    'name' => $invitation->email,
                    'email' => $invitation->email,
                    'user_name' => null,
                    'status' => $invitation->status,
                    'invited_at' => $invitation->invited_at,
                    'is_self' => false,
                    'is_external' => true,
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
                                        :placeholder="'{{ __('Hunter nickname / last_name / email') }}'"
                                        v-model="hunterSlot.query"
                                        :disabled="hunterSlot.hunter && hunterSlot.hunter.invited"
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
                                        <div v-else-if="hunterSlot.noResults && !hunterSlot.isSearching" class="p-2 border-bottom">
                                            <div class="text-muted small mb-2">
                                                {{ __('Hunters not found') }}
                                            </div>
                                            <button
                                                type="button"
                                                class="btn btn-outline-primary btn-sm"
                                                @click="inviteByEmailForSlot(index, {{ $booking->id }}, $event)">
                                                <i class="fa fa-envelope me-1"></i>
                                                {{ __('Send invitation by email') }}
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Информация о выбранном охотнике (показываем только если текст в поле соответствует выбранному охотнику) -->
                                    <div
                                        v-if="hunterSlot.hunter && hunterSlot.query && ((hunterSlot.hunter.is_external && hunterSlot.query.trim() === hunterSlot.hunter.email) || (!hunterSlot.hunter.is_external && hunterSlot.query.trim() === ((hunterSlot.hunter.user_name || (hunterSlot.hunter.first_name + ' ' + hunterSlot.hunter.last_name)).trim())))"
                                        class="mt-2">
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="text-muted small">@{{ hunterSlot.hunter.email }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start">
                                    <button
                                        v-if="hunterSlot.hunter && hunterSlot.query && ((hunterSlot.hunter.is_external && hunterSlot.query.trim() === hunterSlot.hunter.email) || (!hunterSlot.hunter.is_external && hunterSlot.query.trim() === ((hunterSlot.hunter.user_name || (hunterSlot.hunter.first_name + ' ' + hunterSlot.hunter.last_name)).trim())))"
                                        type="button"
                                        class="btn btn-sm me-2 ml-2"
                                        :class="(hunterSlot.hunter && hunterSlot.hunter.invited && hunterSlot.hunter.invitation_status !== 'declined') ? 'btn-success' : 'btn-outline-primary'"
                                        :disabled="!hunterSlot.hunter || (hunterSlot.hunter.invited && hunterSlot.hunter.invitation_status !== 'declined') || hunterSlot.hunter.is_external"
                                        @click="inviteHunterForSlot(index, {{ $booking->id }}, $event)">
                                        <span v-text="(hunterSlot.hunter && hunterSlot.hunter.invited && hunterSlot.hunter.invitation_status === 'accepted') ? acceptedText : ((hunterSlot.hunter && hunterSlot.hunter.invited && hunterSlot.hunter.invitation_status !== 'declined') ? invitedText : inviteText)"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- История отказавшихся охотников --}}
                    <div v-if="declinedHunters && declinedHunters.length > 0" class="mt-4 mb-4">
                        <h6 class="mb-3 text-muted">История приглашений (отказались)</h6>
                        <div class="list-group">
                            <div v-for="(hunter, index) in declinedHunters" :key="index" class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-1">
                                            <strong>@{{ hunter.first_name }} @{{ hunter.last_name }}</strong>
                                        </div>
                                        <div class="text-muted small mb-1" v-if="hunter.user_name">
                                            (ник <strong>@{{ hunter.user_name }}</strong>)
                                        </div>
                                        <div class="text-muted small">@{{ hunter.email }}</div>
                                    </div>
                                    <span class="badge bg-danger ms-2">{{ __('Declined') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-3 border rounded">
                        <div class="d-flex justify-content-center gap-2 flex-wrap">
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
