@php
    $huntersCount = 0;
    $animalMinHunters = 0;
    if ($booking->type === 'hotel') {
        $huntersCount = $booking->total_guests ?? 0;
        $animalMinHunters = $booking->hotelAnimal()->hunters_count ?? 0;
    } elseif ($booking->type === 'animal') {
        $huntersCount = $booking->total_hunting ?? 0;
        $animalMinHunters = $booking->hotelAnimal()->hunters_count ?? 0;
    } elseif ($booking->type === 'hotel_animal') {
        $huntersCount = $booking->total_hunting ?? 0;
        $animalMinHunters = $booking->hotelAnimal()->hunters_count ?? 0;
    }
@endphp

<div class="modal fade" id="collectionModal{{ $booking->id }}" tabindex="-1" aria-hidden="true"
     data-hunters-count="{{ $huntersCount }}"
     data-animal-min-hunters="{{ $animalMinHunters }}"
     data-master-hunter-id="{{ $booking->masterHunterId() }}"
     data-text-paid="{{ __('Paid') }}"
     data-text-awaiting="{{ __('Awaiting prepayment') }}"
     data-booking-id="{{ $booking->id }}">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                @if($booking->status === \Modules\Booking\Models\Booking::START_COLLECTION && !$isInvited)
                    <h5 class="modal-title">{{ __('Open collection for booking') }} #{{ $booking->id }}</h5>
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-primary"
                        @click="copyBookingLink('{{ $booking->invitation_url }}')">
                        {{ __('Link for booking') }}
                    </button>
                @endif
                @if(in_array($booking->status, [\Modules\Booking\Models\Booking::FINISHED_COLLECTION, \Modules\Booking\Models\Booking::PREPAYMENT_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_PREPAYMENT, \Modules\Booking\Models\Booking::BED_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_BED]))
                    <h5 class="modal-title">{{ __('Collection for booking') }} #{{ $booking->id }}</h5>
                @endif
            </div>
            <div class="modal-body">
                @if($isInvited || in_array($booking->status, [\Modules\Booking\Models\Booking::FINISHED_COLLECTION, \Modules\Booking\Models\Booking::PREPAYMENT_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_PREPAYMENT, \Modules\Booking\Models\Booking::BED_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_BED]))
                    @if(in_array($booking->status, [\Modules\Booking\Models\Booking::FINISHED_COLLECTION, \Modules\Booking\Models\Booking::PREPAYMENT_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_PREPAYMENT, \Modules\Booking\Models\Booking::BED_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_BED]))
                        <div class="alert alert-success mb-4">
                            <strong>{{ __('Collection completed') }}</strong>
                        </div>
                    @endif
                    @if($booking->status === \Modules\Booking\Models\Booking::START_COLLECTION)
                        <div class="alert alert-success mb-4">
                            <strong>{{ __('Open collection for hunting') }}</strong>
                        </div>
                    @endif
                    <div class="mb-4">
                        <h6 class="mb-3">Приглашенные охотники</h6>
                        <div v-if="invitedHunters.length > 0">
                            <div class="list-group">
                                <div v-for="hunter in invitedHunters" :key="hunter.id">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-2">
                                                <span v-if="hunter.is_self" class="badge bg-success mr-2">Вы</span>
                                                <strong>@{{ hunter.name }}</strong>
                                                <span v-if="hunter.invitation_status" :class="['badge', getStatusBadge(hunter).class]" class="ml-2 bg-secondary text-white">
                                                @{{ getStatusBadge(hunter).text }}
                                            </span>
                                                {{-- Статус предоплаты --}}
                                                @if($booking->type !== \Modules\Booking\Models\Booking::BookingTypeAnimal && in_array($booking->status, [\Modules\Booking\Models\Booking::PREPAYMENT_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_PREPAYMENT, \Modules\Booking\Models\Booking::BED_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_BED]))
                                                    <div v-if="hunter.status !== 'declined'">
                                                        <span class="badge text-white ml-2" :class="hunter.prepayment_badge.class">
                                                            @{{ hunter.prepayment_badge.text }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="text-muted small mb-1">
                                                <strong>
                                                    ID: @{{ hunter.id }}
                                                    ( ник @{{ hunter.user_name ? hunter.user_name : 'не задан' }} )
                                                </strong>
                                            </div>
                                            <div class="text-muted small mb-2">@{{ hunter.email }}</div>
                                        </div>

                                        @if($booking->status === \Modules\Booking\Models\Booking::PREPAYMENT_COLLECTION && $booking->is_master_hunter)
                                            <div class="d-flex">
                                                <div v-if="!hunter.prepayment_paid && hunter.id !== booking.master_hunter_id">
                                                    <div v-if="hunterToReplace !== hunter.id">
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-primary"
                                                            @click="hunterToReplace = hunter.id">
                                                            Заменить
                                                        </button>

                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-danger"
                                                            @click="removeHunter(hunter.id, {{ $booking->id }})">
                                                            Удалить
                                                        </button>
                                                    </div>
                                                    <div v-else class="d-flex align-items-start mt-2">
                                                        <input
                                                                type="text"
                                                                class="form-control me-2 mr-2"
                                                                :placeholder="'{{ __('Ник / Фамилия / email / ID охотника') }}'"
                                                                v-model="replaceQuery"
                                                                @input="searchReplaceHunter({{ $booking->id }})"
                                                                @focus="showReplaceResults = true"
                                                                @blur="setTimeout(() => showReplaceResults = false, 200)">
                                                            <button
                                                                class="btn btn-sm btn-success me-2 mr-2"
                                                                @click="confirmReplace(hunter.id, {{ $booking->id }})">
                                                                Сохранить
                                                            </button>

                                                            <button
                                                                class="btn btn-sm btn-secondary"
                                                                @click="cancelReplace">
                                                                Отмена
                                                            </button>

                                                        <!-- РЕЗУЛЬТАТЫ -->
                                                        <div v-if="showReplaceResults"
                                                             class="position-absolute w-100 bg-white border mt-1"
                                                             style="top: 100%; left: 0; z-index: 1000; max-height: 300px; overflow-y: auto;">

                                                            <div v-if="isSearchingReplace" class="p-2 text-muted">
                                                                Поиск...
                                                            </div>

                                                            <div v-else-if="replaceResults.length">
                                                                <div v-for="user in replaceResults"
                                                                     :key="user.id"
                                                                     class="p-2 border-bottom cursor-pointer"
                                                                     @click="selectReplaceHunter(user)">

                                                                    <strong>ID: @{{ user.id }}</strong>
                                                                    @{{ user.first_name }} @{{ user.last_name }}
                                                                    <div class="text-muted small">@{{ user.email }}</div>
                                                                </div>
                                                            </div>

                                                            <div v-else class="p-2 text-muted">
                                                                Ничего не найдено
                                                            </div>
                                                        </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else>
                            <p class="text-muted">Охотники еще не приглашены</p>
                        </div>
                        @else
                            {{-- Шаблон для владельца брони: полный функционал управления сбором --}}
                            @include('Booking::frontend.modals.hunter-collection-modal')
                        @endif
                    </div>
                    </div>
        </div>
    </div>
</div>
</div>
