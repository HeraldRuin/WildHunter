<div class="hotel_rooms_form" v-cloak="" v-bind:class="{'d-none':enquiry_type!='book'}">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="heading-section ">{{__('Available Animals')}}</h3>

            @include('Hotel::frontend.layouts.search.fields.booking_animals')

    </div>
    <div class="form-book">
        <div class="form-search-rooms">
            <div class="d-flex form-search-row">
                <div class="col-md-4">
                    <div class="form-group form-date-field form-date-search" @click="openAnimalStartDate" data-format="{{get_moment_date_format()}}">
                        <i class="fa fa-angle-down arrow"></i>
                        <input type="text" class="start_date" ref="animalStartDate" style="height: 1px; visibility: hidden">
                        <div class="date-wrapper form-content" >
                            <label class="form-label">{{__("Hunting Date")}}</label>
                            <div class="render check-in-render" v-html="start_date_animal_html"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <i class="fa fa-angle-down arrow"></i>
                        <div class="form-content dropdown-toggle" data-toggle="dropdown">
                            <label class="form-label">{{__('Hunters')}}</label>
                            <div class="render">
                                <span class="adults" >
                                    <span class="one" >@{{hunting_adults}}
                                        <span v-if="hunting_adults < 2">{{__('Adult')}}</span>
                                        <span v-else>{{__('Adults')}}</span>
                                    </span>
                                </span>
                            </div>
                        </div>
                        <div class="dropdown-menu select-guests-dropdown" >
                            <div class="dropdown-item-row">
                                <div class="label">{{__('Adults')}}</div>
                                <div class="val">
                                    <span class="btn-minus2" data-input="hunting_adults" @click="minusPersonType('hunting_adults')"><i class="icon ion-md-remove"></i></span>
                                    <span class="count-display"><input type="number" v-model="hunting_adults" min="1"/></span>
                                    <span class="btn-add2" data-input="hunting_adults" @click="addPersonType('hunting_adults')"><i class="icon ion-ios-add"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-btn">
                    <div class="g-button-submit">
                        <button class="btn btn-primary btn-search" @click="checkAvailabilityForAnimal" v-bind:class="{'loading':onLoadAvailability}" type="submit">
                            {{__("Check Presence")}}
                            <i v-show="onLoadAnimalAvailability" class="fa fa-spinner fa-spin"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="hotel_room_book_status" v-if="animalPrice > 0">
            <div class="row row_total_price">
                <div class="col-md-6">
                    <div class="extra-price-wrap d-flex justify-content-between">
                        <div class="flex-grow-1">
                            <label>
                                {{__("Total Hunting")}}:
                            </label>
                        </div>
                        <div class="flex-shrink-0">
                            @{{hunting_adults}}
                        </div>
                    </div>
                    <div class="extra-price-wrap d-flex justify-content-between is_mobile">
                        <div class="flex-grow-1">
                            <label>
                                {{__("Total Hunting")}}:
                            </label>
                        </div>
                        <div class="total-room-price">@{{hunting_adults}}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="control-book">
                        <div class="total-room-price">
                            <span> {{__("Total Price")}}:</span> @{{'₽' + animalPrice * hunting_adults}}
                        </div>
{{--                        <div v-if="is_deposit_ready" class="total-room-price">--}}
{{--                            <span>{{__("Pay now")}}</span>--}}
{{--                            @{{animalPrice}}--}}
{{--                        </div>--}}
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-2">
            <div class="alert alert-success" v-if="animalCheckPassed && animalPrice > 0">
                {{__("On this day there is an animal hunt. You can continue booking")}}
            </div>
        </div>

    </div>
</div>

