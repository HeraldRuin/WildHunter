<tr>
    <td class="booking-history-type">
        {{ $booking->service ? $booking->booking_number : $booking->booking_number }}
    </td>

    <td class="a-hidden">{{display_date($booking->created_at)}}</td>
    <td>
       <span
          class="cursor-pointer user-link"
          data-bs-trigger="hover"
          data-bs-html="true"
          data-bs-placement="right"
          data-bs-content="<strong>{{ $booking->creator?->first_name ?? '' }} {{ $booking->creator?->last_name ?? '' }}</strong><br>Email: {{ $booking->creator?->email ?? '' }}<br>Phone: {{ $booking->creator?->phone ?? '' }}"
          @click="{{ $userRole !== 'hunter' && $booking->creator ? "openUserModal({$booking->creator->id}, {$booking->id})" : '' }}">
          {{ $booking->creator ? (!empty($booking->creator->user_name) ? $booking->creator->user_name : $booking->creator->first_name) : 'N/A' }}
       </span>
        @if($booking->status === \Modules\Booking\Models\Booking::FINISHED_PREPAYMENT)
            <button
                type="button"
                class="btn btn-info btn-sm details-btn mt-2"
                data-bs-toggle="popover"
                data-bs-trigger="click"
                data-bs-html="true"
                data-bs-placement="right"
                data-bs-content="<strong>{{ $booking->creator?->first_name ?? '' }} {{ $booking->creator?->last_name ?? '' }}</strong><br>Email: {{ $booking->creator?->email ?? '' }}<br>Phone: {{ $booking->creator?->phone ?? '' }}">
                Контакты
            </button>
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
    <td class="{{$booking->status}} a-hidden">
        <div>
            @php
                // Получаем информацию об охотниках
                if ($booking->type === \Modules\Booking\Models\Booking::BookingTypeAnimal){
                     $totalHuntersNeeded = $booking->total_hunting ?? 0;
                }else{
                     $totalHuntersNeeded = $booking->total_guests ?? 0;
                }
                $allInvitations = $booking->getAllInvitations();
                $acceptedInvitations = $allInvitations->where('status', 'accepted');
                $acceptedCount = $acceptedInvitations->count();
                $paidInvitations = $allInvitations->where('prepayment_paid', true);
                $paidCount = $paidInvitations->count();
            @endphp

            {{$booking->statusName}}

            @if($booking->status === \Modules\Booking\Models\Booking::START_COLLECTION && $booking->hotel && $booking->hotel->collection_timer_hours)
                ({{$booking->hotel->collection_timer_hours}} {{ __('ч') }})
            @endif
            @if($booking->status === \Modules\Booking\Models\Booking::PREPAYMENT_COLLECTION && $booking->hotel && $booking->hotel->paid_timer_hours)
                ({{$booking->hotel->paid_timer_hours}} {{ __('ч') }})
            @endif

            @if($booking->status === \Modules\Booking\Models\Booking::BED_COLLECTION && $booking->hotel && $booking->hotel->bed_timer_hours)
                ({{$booking->hotel->bed_timer_hours}} {{ __('ч') }})
            @endif
        </div>

        @if($booking->status === \Modules\Booking\Models\Booking::START_COLLECTION)
            @php
                $collectionEndAt = $booking->getMeta('collection_end_at');
                $endTimestamp = $collectionEndAt? \Carbon\Carbon::parse($collectionEndAt)->timestamp * 1000: null;
            @endphp

            @if($endTimestamp)
                <div class="text-muted collection-timer" data-end="{{ $endTimestamp }}"
                     data-booking-id="{{ $booking->id }}">[0 мин 00 сек]
                </div>
            @endif

            @if($totalHuntersNeeded > 0)
                <div class="text-muted mt-1" style="font-size: 0.9em;">
                    Собрано {{ $acceptedCount }}/{{ $totalHuntersNeeded }}
                </div>
            @endif
        @endif

        @if($booking->status === \Modules\Booking\Models\Booking::PREPAYMENT_COLLECTION)
            @php
                $endTimestamp = null;
                try {
                    $bedsEndAt = $booking->getMeta('paid_end_at');
                    if ($bedsEndAt) {
                        $endCarbon = \Carbon\Carbon::parse($bedsEndAt);
                        $endTimestamp = $endCarbon->timestamp * 1000;
                    }
                } catch (\Exception $e) {
                    $endTimestamp = null;
                }
            @endphp

            @if($endTimestamp)
                <div class="text-muted paid-timer" data-end="{{ $endTimestamp }}"
                     data-booking-id="{{ $booking->id }}">[0 мин 00 сек]
                </div>
            @endif
        @endif

        @if($booking->status === \Modules\Booking\Models\Booking::BED_COLLECTION)
            @php
                $endTimestamp = null;
                try {
                    $bedsEndAt = $booking->getMeta('beds_end_at');
                    if ($bedsEndAt) {
                        $endCarbon = \Carbon\Carbon::parse($bedsEndAt);
                        $endTimestamp = $endCarbon->timestamp * 1000;
                    }
                } catch (\Exception $e) {
                    $endTimestamp = null;
                }
            @endphp

            @if($endTimestamp)
                <div class="text-muted beds-timer" data-end="{{ $endTimestamp }}"
                     data-booking-id="{{ $booking->id }}">[0 мин 00 сек]
                </div>
            @endif
        @endif

        @if(in_array($booking->status, [\Modules\Booking\Models\Booking::PREPAYMENT_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_PREPAYMENT, \Modules\Booking\Models\Booking::BED_COLLECTION]))
            @if($booking->status === \Modules\Booking\Models\Booking::BED_COLLECTION)
                <div class="mt-3">
                    {{'Предоплата собрана'}}
                    <div class="text-muted mt-1" style="font-size: 0.9em;">
                        Оплачено {{ $paidCount }}/{{ $acceptedCount }}
                    </div>
                </div>
            @else
                <div class="text-muted mt-1" style="font-size: 0.9em;">
                    Оплачено {{ $paidCount }}/{{ $acceptedCount }}
                </div>
            @endif

            <div class="mt-3">
                {{'Сбор завершен'}}
                <div class="text-muted mt-1" style="font-size: 0.9em;">
                    Собрано {{ $acceptedCount }}/{{ $totalHuntersNeeded }}
                </div>
            </div>
        @endif
    </td>

    <td>
        <div>
            @if(in_array($booking->status, [\Modules\Booking\Models\Booking::FINISHED_PREPAYMENT, \Modules\Booking\Models\Booking::BED_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_BED, \Modules\Booking\Models\Booking::PAID, \Modules\Booking\Models\Booking::COMPLETED]))
                Внесена предоплата: {{ format_money($booking->calculation['prepaid_total']?? 0) }} <br>
                Остаток базе:  {{ format_money($booking->calculation['base_total']?? 0) }} <br>
                Всего: {{ format_money($booking->calculation['total']?? 0) }}
            @endif
        </div>
    </td>

    <td>
        @if($userRole === 'baseadmin' && $booking->status === \Modules\Booking\Models\Booking::PROCESSING)
            <button
                type="button"
                class="btn btn-success btn-sm mt-2"
                @click="openConfirmBookingModal({{ $booking->id }}, $event)">
                {{ __("Booking apply") }}
            </button>
        @endif

        @if($booking->status === \Modules\Booking\Models\Booking::PAID)
            <button
                type="button"
                class="btn btn-success btn-sm mt-2"
                @click="openFinalizeBookingModal({{ $booking->id }}, $event)">
                {{__("Complete booking")}}
            </button>
        @endif

        @if($userRole === 'baseadmin' && in_array($booking->status, [ \Modules\Booking\Models\Booking::PROCESSING, \Modules\Booking\Models\Booking::CONFIRMED, \Modules\Booking\Models\Booking::PREPAYMENT_COLLECTION, \Modules\Booking\Models\Booking::START_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_PREPAYMENT, \Modules\Booking\Models\Booking::BED_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_BED]))
            <button
                type="button"
                class="btn btn-danger btn-sm mt-2"
                @click="openCancelBookingModal({{ $booking->id }}, $event)">
                {{__("Cancele booking")}}
            </button>
        @endif

        @if($userRole === 'baseadmin' && in_array($booking->status, [\Modules\Booking\Models\Booking::PREPAYMENT_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_PREPAYMENT, \Modules\Booking\Models\Booking::BED_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_BED]))
            <button
                type="button"
                class="btn btn-primary btn-sm mt-2"
                data-bs-toggle="modal"
                data-bs-target="#bookingAddServiceModal{{ $booking->id }}">
                {{__("Add services")}}
            </button>
        @endif

        @if($userRole === 'baseadmin' && in_array($booking->status, [\Modules\Booking\Models\Booking::PREPAYMENT_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_PREPAYMENT, \Modules\Booking\Models\Booking::BED_COLLECTION, \Modules\Booking\Models\Booking::FINISHED_BED, \Modules\Booking\Models\Booking::PAID, \Modules\Booking\Models\Booking::COMPLETED]))
            <button
                type="button"
                class="btn btn-primary btn-sm mt-2"
                @click="openCalculatingModal({{ $booking }}, $event)">
                {{__("Calculating")}}
            </button>
        @endif

        @if($userRole === 'baseadmin' && $booking->status === \Modules\Booking\Models\Booking::FINISHED_BED)
            <button
                type="button"
                class="btn btn-success btn-sm mt-2"
                @click="openPreFinalizeBookingModal({{ $booking->id }}, $event)">
                {{__("Paid")}}
            </button>
        @endif
    </td>
</tr>

{{-- Модальное окно для добавления услуг --}}
@include('Hotel::.frontend.bookingHistory.addServices.add-services-baseadmin')

{{-- Модальное окно для калькуляции --}}
@include('Booking::frontend.modals.calculating.calculating-baseAdmin-modal', ['booking' => $booking])

{{-- Модальное окно поиска заказчика --}}
@include('User::frontend.modals.search-user-modal')

