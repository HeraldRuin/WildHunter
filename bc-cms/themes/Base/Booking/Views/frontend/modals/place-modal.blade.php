<div class="modal fade" id="placeBookingModal{{ $booking->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                @if($booking->is_all_places_assigned)
                    <h5 class="modal-title">{{__("Seats are allocated")}}</h5>
                @else
                    <h5 class="modal-title">{{__("Choosing a bed")}}</h5>
                @endif
            </div>

            <div class="modal-body">
                <div id="booking-places-content-{{ $booking->id }}"></div>
            </div>

        </div>
    </div>
</div>
