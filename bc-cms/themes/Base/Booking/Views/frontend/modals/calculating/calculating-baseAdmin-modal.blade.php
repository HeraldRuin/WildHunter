<div class="modal fade" id="calculatingBookingModal{{ $booking->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">{{__("Calculating")}}</h5>
            </div>

            <div class="modal-body">
                <div id="calculating-content-{{ $booking->id }}"></div>
            </div>

        </div>
    </div>
</div>
