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

    $masterHunter = $bookingHunter->invitedBy()->first();

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
    if ($isInvited || in_array($booking->status, [\Modules\Booking\Models\Booking::FINISHED_COLLECTION, \Modules\Booking\Models\Booking::PREPAYMENT_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_PREPAYMENT, \Modules\Booking\Models\Booking::BED_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_BED])) {
//        $invitations = $booking->getAllInvitations();
//        foreach ($invitations as $invitation) {
//            if ($invitation->hunter) {
//                $isCurrentUser = $invitation->hunter->id == $currentUserId;
//                $invitedHunters[] = [
//                    'id' => $invitation->hunter->id,
//                    'name' => $invitation->hunter->first_name . ' ' . $invitation->hunter->last_name,
//                    'email' => $invitation->hunter->email,
//                    'user_name' => $invitation->hunter->user_name ?? null,
//                    'status' => $invitation->status,
//                    'invited_at' => $invitation->invited_at,
//                    'is_self' => $isCurrentUser,
//                    'prepayment_paid' => (bool) ($invitation->prepayment_paid ?? false),
//                ];
//            } elseif ($invitation->email) {
//                $invitedHunters[] = [
//                    'id' => null,
//                    'name' => $invitation->email,
//                    'email' => $invitation->email,
//                    'user_name' => null,
//                    'status' => $invitation->status,
//                    'invited_at' => $invitation->invited_at,
//                    'is_self' => false,
//                    'is_external' => true,
//                    'prepayment_paid' => (bool) ($invitation->prepayment_paid ?? false),
//                ];
//            }
//        }
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
                                                <strong>@{{ hunter.name }}</strong>
                                                <span v-if="hunter.is_self" class="badge bg-secondary ml-3">Вы</span>
                                            </div>
                                            <div class="text-muted small mb-1">
                                                <strong>
                                                    ID: @{{ hunter.id }}
                                                    ( ник @{{ hunter.user_name ? hunter.user_name : 'не задан' }} )
                                                </strong>
                                            </div>
                                            <div class="text-muted small mb-2">@{{ hunter.email }}</div>
                                            <span v-if="getStatusBadge(hunter).text"
                                                  :class="['badge', getStatusBadge(hunter).class]">
                                                @{{ getStatusBadge(hunter).text }}
                                            </span>

                                            {{-- Статус предоплаты --}}
                                            @if($booking->type !== \Modules\Booking\Models\Booking::BookingTypeAnimal)

                                                {{--                                                @if($hunter['status']  != 'declined' && in_array($booking->status, [\Modules\Booking\Models\Booking::FINISHED_COLLECTION, \Modules\Booking\Models\Booking::PREPAYMENT_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_PREPAYMENT, \Modules\Booking\Models\Booking::BED_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_BED]))--}}
{{--                                                <span>@{{ hunter.prepayment_paid ? textPaid : textAwaiting }}</span>--}}
                                                {{--                                                    <div v-if="hunter.status !== 'declined' && allowedBookingStatuses.includes(booking.status)">--}}
                                                {{--                                                        <span>@{{ hunter.prepayment_paid ? __('Paid') : __('Awaiting prepayment') }}</span>--}}
                                                {{--                                                    </div>--}}

                                                {{--                                                    @if($endTimestamp)--}}
                                                {{--                                                        <span class="paid-timer" @class(['text-danger' => !$hunter['prepayment_paid']])>--}}
                                                {{--                                                                {{ $hunter['prepayment_paid'] ? __('Paid') : __('Awaiting prepayment') }}--}}
                                                {{--                                                            </span>--}}
                                                {{--                                                    @else--}}
                                                {{--                                                        <span>{{ $hunter['prepayment_paid'] ? __('Paid') : __('Awaiting prepayment') }}</span>--}}
                                                {{--                                                    @endif--}}
                                                {{--                                                    @if($booking->status === \Modules\Booking\Models\Booking::PREPAYMENT_COLLECTION)--}}
                                                {{--                                                        @php--}}
                                                {{--                                                            $endTimestamp = null;--}}
                                                {{--                                                            try {--}}
                                                {{--                                                                $bedsEndAt = $booking->getMeta('paid_end_at');--}}
                                                {{--                                                                if ($bedsEndAt) {--}}
                                                {{--                                                                    $endCarbon = \Carbon\Carbon::parse($bedsEndAt);--}}
                                                {{--                                                                    $endTimestamp = $endCarbon->timestamp * 1000;--}}
                                                {{--                                                                }--}}
                                                {{--                                                            } catch (\Exception $e) {--}}
                                                {{--                                                                $endTimestamp = null;--}}
                                                {{--                                                            }--}}
                                                {{--                                                        @endphp--}}

                                                {{--                                                        @if($endTimestamp)--}}
                                                {{--                                                            <span class="paid-timer" @class(['text-danger' => !$hunter['prepayment_paid']])>--}}
                                                {{--                                                                {{ $hunter['prepayment_paid'] ? __('Paid') : __('Awaiting prepayment') }}--}}
                                                {{--                                                            </span>--}}
                                                {{--                                                        @else--}}
                                                {{--                                                            <span>{{ $hunter['prepayment_paid'] ? __('Paid') : __('Awaiting prepayment') }}</span>--}}
                                                {{--                                                        @endif--}}
                                                {{--                                                    @endif--}}
                                                {{--                                                @endif--}}
                                            @endif
                                        </div>

                                        @if($booking->status === \Modules\Booking\Models\Booking::PREPAYMENT_COLLECTION)

                                            <div v-if="!hunter.prepayment_paid && hunter.id !== booking.master_hunter_id" class="d-flex">
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    @click="replaceHunter( hunter.id, {{ $booking->id }})">
                                                    Заменить
                                                </button>

                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    @click="removeHunter( hunter.id, {{ $booking->id }})">
                                                    Удалить
                                                </button>
                                            <div/>
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
