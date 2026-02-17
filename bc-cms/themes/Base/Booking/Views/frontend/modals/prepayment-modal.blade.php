@php use Illuminate\Support\Facades\Auth; @endphp
<div class="modal fade" id="bookingPrepaymentModal{{ $booking->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{__('Prepayment booking')}} #{{ $booking->id }}</h5>
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
                        :disabled="prepaymentPaidMap[{{ $booking->id }}]"
                        :data-booking-id="{{ $booking->id }}"
                        :data-prepayment-paid="{{ $prepaymentPaid ? 'true' : 'false' }}"
                        @click="bookingPrepaymentPaid($event)">
                    <span v-if="prepaymentPaidMap[{{ $booking->id }}] || {{ $prepaymentPaid ? 'true' : 'false' }}">
                        {{ __('Paid Booking') }}
                    </span>
                    <span v-else>
                        {{ __('Paid Prepayment') }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
