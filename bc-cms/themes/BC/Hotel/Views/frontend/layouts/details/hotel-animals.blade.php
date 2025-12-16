<div class="hotel_rooms_form" v-cloak="" v-bind:class="{'d-none':enquiry_type!='book'}">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="heading-section ">{{__('Available Animals')}}</h3>

            @include('Hotel::frontend.layouts.search.fields.booking_animals')

    </div>

    <div class="nav-enquiry" v-if="is_form_enquiry_and_book">
        <div class="enquiry-item active" >
            <span>{{ __("Book") }}</span>
        </div>
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
                                    <span class="one" >@{{adults}}
                                        <span v-if="adults < 2">{{__('Adult')}}</span>
                                        <span v-else>{{__('Adults')}}</span>
                                    </span>
                                </span>
                            </div>
                        </div>
                        <div class="dropdown-menu select-guests-dropdown" >
                            <div class="dropdown-item-row">
                                <div class="label">{{__('Adults')}}</div>
                                <div class="val">
                                    <span class="btn-minus2" data-input="adults" @click="minusPersonType('adults')"><i class="icon ion-md-remove"></i></span>
                                    <span class="count-display"><input type="number" v-model="adults" min="1"/></span>
                                    <span class="btn-add2" data-input="adults" @click="addPersonType('adults')"><i class="icon ion-ios-add"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-btn">
                    <div class="g-button-submit">
                        <button class="btn btn-primary btn-search" @click="checkAvailabilityForAnimal" v-bind:class="{'loading':onLoadAvailability}" type="submit">
                            {{__("Check Presence")}}
                            <i v-show="onLoadAvailability" class="fa fa-spinner fa-spin"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <div class="pt-2">
            <div class="alert alert-success" v-if="animalCheckPassed">
                {{__("On this day there is an animal hunt. You can continue booking")}}
            </div>
        </div>

    </div>
</div>

