<div class="modal fade" id="bookingAddServiceModal{{ $booking->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="bookingServiceApp{{ $booking->id }}">
            <div class="modal-header">
                <h5 class="modal-title">Добавить услуги для брони #{{ $booking->id }}</h5>
            </div>

            <div class="modal-body">
                <div>

                    <!-- Трофеи -->
                    <div class="service-block mb-3 p-3 border rounded bg-light shadow-sm" id="trophies-block-{{ $booking->id }}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6>Трофеи:</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary add-trophy-btn" data-booking="{{ $booking->id }}">+</button>
                        </div>

                        <div class="trophies-list"></div>
                        <div class="all-trophies-overlay" style="display:none; position:absolute; z-index:1050; background:#fff; border:1px solid #ccc;
                        padding:10px; border-radius:6px; box-shadow:0 0 10px rgba(0,0,0,0.2);
                        max-height:400px; overflow-y:auto; width:400px;">
                        </div>
                    </div>

                    <!-- Штрафы -->
                    <div class="service-block mb-3 p-3 border rounded bg-light shadow-sm" id="penalties-block-{{ $booking->id }}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6>Штрафы:</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary add-penalty-btn" data-booking="{{ $booking->id }}">+</button>
                        </div>

                        <div class="penalties-list"></div>
                        <div class="all-penalties-overlay" style="display:none; position:absolute; z-index:1050; background:#fff; border:1px solid #ccc;
        padding:10px; border-radius:6px; box-shadow:0 0 10px rgba(0,0,0,0.2);
        max-height:400px; overflow-y:auto; width:400px;">
                        </div>
                    </div>




                    <!-- Доп. услуги -->
                    <div class="service-block mb-3">
                        <h6>Доп. услуги:</h6>

                        <!-- Разделка -->
                        <div class="service-block mb-3 p-3 border rounded bg-light shadow-sm" id="preparations-block-{{ $booking->id }}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6>Разделка:</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary add-preparation-btn" data-booking="{{ $booking->id }}">+</button>
                            </div>

                            <div class="preparations-list"></div>

                            <div class="all-preparations-overlay" style="display:none; position:absolute; z-index:1050; background:#fff; border:1px solid #ccc;
        padding:10px; border-radius:6px; box-shadow:0 0 10px rgba(0,0,0,0.2);
        max-height:400px; overflow-y:auto; width:400px;">
                            </div>
                        </div>

                    </div>

                    <!-- Питание -->
                    <div class="service-block mb-3 p-3 border rounded bg-light shadow-sm" id="foods-block-{{ $booking->id }}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6>Питание:</h6>
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary add-food-btn"
                                data-booking="{{ $booking->id }}"
                            >+</button>
                        </div>

                        <div class="foods-list"></div>

                        <div
                            class="all-foods-overlay"
                            style="display:none; position:absolute; z-index:1050; background:#fff; border:1px solid #ccc;
        padding:10px; border-radius:6px; box-shadow:0 0 10px rgba(0,0,0,0.2);
        max-height:400px; overflow-y:auto; width:400px;"
                        >
                        </div>
                    </div>



                        <!-- Другое -->
{{--                        <div class="subservice mb-2">--}}
{{--                            <div class="d-flex justify-content-between">--}}
{{--                                <span>Другое</span>--}}
{{--                                <button type="button" class="btn btn-sm btn-outline-primary" @click="addOther">+</button>--}}
{{--                            </div>--}}
{{--                            <table class="table table-sm mt-1">--}}
{{--                                <tbody>--}}
{{--                                <tr v-for="(item, index) in others" :key="index">--}}
{{--                                    <td>--}}
{{--                                        <select v-model="item.value" class="form-select form-select-sm">--}}
{{--                                            <option disabled value="">Выпадающий список</option>--}}
{{--                                            <option>Опция 1</option>--}}
{{--                                            <option>Опция 2</option>--}}
{{--                                        </select>--}}
{{--                                    </td>--}}
{{--                                    <td><button type="button" class="btn btn-sm btn-outline-danger" @click="removeOther(index)">x</button></td>--}}
{{--                                </tr>--}}
{{--                                </tbody>--}}
{{--                            </table>--}}
{{--                        </div>--}}

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
