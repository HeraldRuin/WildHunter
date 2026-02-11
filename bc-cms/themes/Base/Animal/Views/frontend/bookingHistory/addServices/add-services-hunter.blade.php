<div class="modal fade" id="bookingAddServiceModal{{ $booking->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" id="bookingServiceApp{{ $booking->id }}">
            <div class="modal-header">
                <h5 class="modal-title">Добавить услуги для брони #{{ $booking->id }}</h5>
            </div>

            <div class="modal-body">
                <div>

                    <!-- Доп. услуги -->
                    <div class="service-block mb-3">
                        <h6>Доп. услуги:</h6>

                        <!-- Разделка -->
                        <div class="service-block mb-3 p-3 border rounded bg-light shadow-sm"
                             id="preparations-block-{{ $booking->id }}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6>Разделка:</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary add-preparation-btn" data-booking="{{ $booking->id }}">+</button>
                            </div>

                            <div class="preparations-list"></div>
                        </div>
                    </div>

                </div>

                <!-- Питание -->
                <div class="service-block mb-3 p-3 border rounded bg-light shadow-sm"
                     id="foods-block-{{ $booking->id }}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6>Питание:</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary add-food-btn"
                                data-booking="{{ $booking->id }}">+
                        </button>
                    </div>

                    <div class="foods-list"></div>
                </div>

                <!-- Другое -->
                <div class="service-block mb-3 p-3 border rounded bg-light shadow-sm"
                     id="others-block-{{ $booking->id }}">

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6>Другое:</h6>
                        <button type="button"
                                class="btn btn-sm btn-outline-primary add-other-btn"
                                data-booking="{{ $booking->id }}">+
                        </button>
                    </div>

                    <div class="others-list"></div>
                </div>

            </div>
        </div>
    </div>
</div>
