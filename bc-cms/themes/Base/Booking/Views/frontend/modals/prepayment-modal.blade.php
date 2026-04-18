@php use Illuminate\Support\Facades\Auth; @endphp
<div class="modal fade" id="bookingPrepaymentModal{{ $booking->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{__('Prepayment booking')}} #{{ $booking->booking_number }}</h5>
            </div>
            @php
                   $prepaymentPaid = false;

                   if ($booking->bookingHunter) {
                       $bookingHunter = $booking->bookingHunter;
                       $invitation = $bookingHunter->invitations->firstWhere('hunter_id', Auth::id());
                       $prepaymentPaid = $invitation?->prepayment_paid ?? false;
                   }
            @endphp
            <div class="modal-footer">

                <button type="button"
                        class="btn btn-success btn-prepayment"
                        data-booking-id="{{ $booking->id }}"
                        :disabled='prepaymentPaidMap[{{ $booking->id }}] === @json(\Modules\Booking\Models\Payment::PAID)'
                        @click="bookingPrepaymentPaid({{ $booking->id }}, $event)">

                    <span v-if='prepaymentPaidMap[{{ $booking->id }}] === @json(\Modules\Booking\Models\Payment::PROCESSING)'>
                             {{ __('Paid Prepayment') }}
                    </span>
                    <span v-else-if='prepaymentPaidMap[{{ $booking->id }}] === @json(\Modules\Booking\Models\Payment::PAID)'>
                        {{ __('Paid Booking Success') }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
