<div class="modal fade" id="placeBookingModal{{ $booking->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Выбор койко-места</h5>
            </div>

            <div class="modal-body">
                <div id="booking-places-content-{{ $booking->id }}">
                </div>
            </div>

        </div>
    </div>
</div>
