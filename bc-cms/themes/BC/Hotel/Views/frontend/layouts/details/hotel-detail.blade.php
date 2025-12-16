<div class="g-header">
    <div class="left">
        @if($row->star_rate)
            <div class="star-rate">
                @for ($star = 1 ;$star <= $row->star_rate ; $star++)
                    <i class="fa fa-star"></i>
                @endfor
            </div>
        @endif
        <h1>{{$translation->title}}</h1>
        @if($translation->address)
            <h2 class="address"><i class="fa fa-map-marker"></i>
                {{$translation->address}}
            </h2>
        @endif
    </div>
    <div class="right">
        @if($row->getReviewEnable())
            @if($review_score)
                <div class="review-score">
                    <div class="head">
                        <div class="left">
                            <span class="head-rating">{{$review_score['score_text']}}</span>
                            <span class="text-rating">{{__("from :number reviews",['number'=>$review_score['total_review']])}}</span>
                        </div>
                        <div class="score">
                            {{$review_score['score_total']}}<span>/5</span>
                        </div>
                    </div>
                    <div class="foot">
                        {{__(":number% of guests recommend",['number'=>$row->recommend_percent])}}
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
@include('Layout::global.details.gallery')
@if($translation->content)
    <div class="g-overview">
        <h3>{{__("Description")}}</h3>
        <div class="description">
            <?php echo $translation->content ?>
        </div>
    </div>
@endif
<div id="hotel-rooms" class="hotel_rooms_form" v-cloak="" v-bind:class="{'d-none':enquiry_type!='book'}">
    <h3 class="heading-section">{{__('Available Rooms')}}</h3>
    <div class="nav-enquiry" v-if="is_form_enquiry_and_book">
        <div class="enquiry-item active" >
            <span>{{ __("Book") }}</span>
        </div>
        <div class="enquiry-item" data-toggle="modal" data-target="#enquiry_form_modal">
            <span>{{ __("Enquiry") }}</span>
        </div>
    </div>
    <div class="form-book">
        <div class="form-search-rooms">
            <div class="d-flex form-search-row">
                <div class="col-md-4">
                    <div class="form-group form-date-field form-date-search " @click="openStartDate" data-format="{{get_moment_date_format()}}">
                        <i class="fa fa-angle-down arrow"></i>
                        <input type="text" class="start_date" ref="hotelStartDate" style="height: 1px; visibility: hidden">
                        <div class="date-wrapper form-content" >
                            <label class="form-label">{{__("Check In - Out")}}</label>
                            <div class="render check-in-render" v-html="start_date_html"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <i class="fa fa-angle-down arrow"></i>
                        <div class="form-content dropdown-toggle" data-toggle="dropdown">
                            <label class="form-label">{{__('Guests')}}</label>
                            <div class="render">
                                <span class="adults" >
                                    <span class="one" >@{{adults}}
                                        <span v-if="adults < 2">{{__('Adult')}}</span>
                                        <span v-else>{{__('Adults')}}</span>
                                    </span>
                                </span>
                                -
                                <span class="children" >
                                    <span class="one" >@{{children}}
                                        <span v-if="children < 2">{{__('Child')}}</span>
                                        <span v-else>{{__('Children')}}</span>
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
                            <div class="dropdown-item-row">
                                <div class="label">{{__('Children')}}</div>
                                <div class="val">
                                    <span class="btn-minus2" data-input="children" @click="minusPersonType('children')"><i class="icon ion-md-remove"></i></span>
                                    <span class="count-display"><input type="number" v-model="children" min="0"/></span>
                                    <span class="btn-add2" data-input="children" @click="addPersonType('children')"><i class="icon ion-ios-add"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-btn">
                    <div class="g-button-submit">
                        <button class="btn btn-primary btn-search" @click="checkAvailability" v-bind:class="{'loading':onLoadAvailability}" type="submit">
                            {{__("Check Availability")}}
                            <i v-show="onLoadAvailability" class="fa fa-spinner fa-spin"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="start_room_sticky"></div>
        <div class="hotel_list_rooms" v-bind:class="{'loading':onLoadAvailability}">
            <div class="row">
                <div class="col-md-12">
                    <div class="room-item" v-for="room in rooms">
                        <div class="row">
                            <div class="col-xs-12 col-md-3">
                                <div class="image" @click="showGallery($event,room.id,room.gallery)">
                                    <img v-bind:src="room.image" alt="">
                                    <div class="count-gallery" v-if="typeof room.gallery !='undefined' && room.gallery && room.gallery.length > 1">
                                        <i class="fa fa-picture-o"></i>
                                        @{{room.gallery.length}}
                                    </div>
                                </div>
                                <div class="modal" :id="'modal_room_'+room.id" tabindex="-1" role="dialog">
                                    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">@{{ room.title }}</h5>
                                                <span class="c-pointer" data-dismiss="modal" aria-label="Close">
                                                    <i class="input-icon field-icon fa">
                                                        <img src="{{asset('images/ico_close.svg')}}" alt="close">
                                                    </i>
                                                </span>
                                            </div>
                                            <div class="modal-body">
                                                <div class="fotorama" data-nav="thumbs" data-width="100%" data-auto="false" data-allowfullscreen="true">
                                                    <a v-for="g in room.gallery" :href="g.large"></a>
                                                </div>
                                                <div class="list-attributes">
                                                    <div class="attribute-item" v-for="term in room.terms">
                                                        <h4 class="title">@{{ term.parent.title }}</h4>
                                                        <ul v-if="term.child">
                                                            <li v-for="term_child in term.child">
                                                                <i class="input-icon field-icon" v-bind:class="term_child.icon" data-toggle="tooltip" data-placement="top" v-bind:title="term_child.title"></i>
                                                                @{{ term_child.title }}
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-6">
                                <div class="hotel-info">
                                    <h3 class="room-name" @click="showGallery($event,room.id,room.gallery)">@{{room.title}}</h3>
                                    <ul class="room-meta">
                                        <li v-if="room.size_html">
                                            <div class="item" data-toggle="tooltip" data-placement="top" title="" data-original-title="{{__('Room Footage')}}">
                                                <i class="input-icon field-icon icofont-ruler-compass-alt"></i>
                                                <span v-html="room.size_html"></span>
                                            </div>
                                        </li>
                                        <li v-if="room.beds_html">
                                            <div class="item" data-toggle="tooltip" data-placement="top" title="" data-original-title="{{__('No. Beds')}}">
                                                <i class="input-icon field-icon icofont-hotel"></i>
                                                <span v-html="room.beds_html"></span>
                                            </div>
                                        </li>
                                        <li v-if="room.adults_html">
                                            <div class="item" data-toggle="tooltip" data-placement="top" title="" data-original-title="{{__('No. Adults')}}">
                                                <i class="input-icon field-icon icofont-users-alt-4"></i>
                                                <span v-html="room.adults_html"></span>
                                            </div>
                                        </li>
                                        <li v-if="room.children_html">
                                            <div class="item" data-toggle="tooltip" data-placement="top" title="" data-original-title="{{__('No. Children')}}">
                                                <i class="input-icon field-icon fa-child fa"></i>
                                                <span v-html="room.children_html"></span>
                                            </div>
                                        </li>
                                    </ul>
                                    <div class="room-attribute-item" v-if="room.term_features">
                                        <ul>
                                            <li v-for="term_child in room.term_features">
                                                <i class="input-icon field-icon" v-bind:class="term_child.icon" data-toggle="tooltip" data-placement="top" v-bind:title="term_child.title"></i>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3" v-if="room.number">
                                <div class="col-price clear">
                                    <div class="text-center">
                                        <span class="price" v-html="room.price_html"></span>
                                    </div>
                                    <select v-if="room.number" v-model="room.number_selected" class="custom-select">
                                        <option value="0">0</option>
                                        <option v-for="i in (1,room.number)" v-bind:value="i">@{{i+' '+ (i > 1 ? i18n.rooms  : i18n.room)}} &nbsp;&nbsp; (@{{formatMoney(i*room.price)}})</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="hotel_room_book_status" v-if="total_price">
            <div class="row row_extra_service" v-if="extra_price.length">
                <div class="col-md-12">
                    <div class="form-section-group">
                        <label>{{__('Extra prices:')}}</label>
                        <div class="row">
                            <div class="col-md-6 extra-item" v-for="(type,index) in extra_price">
                                <div class="extra-price-wrap d-flex justify-content-between">
                                    <div class="flex-grow-1">
                                        <label>
                                            <input type="checkbox" true-value="1" false-value="0" v-model="type.enable"> @{{type.name}}
                                            <div class="render" v-if="type.price_type">(@{{type.price_type}})</div>
                                        </label>
                                    </div>
                                    <div class="flex-shrink-0">@{{type.price_html}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row row_total_price">
                <div class="col-md-6">
                    <div class="extra-price-wrap d-flex justify-content-between">
                        <div class="flex-grow-1">
                            <label>
                                {{__("Total Room")}}:
                            </label>
                        </div>
                        <div class="flex-shrink-0">
                            @{{total_rooms}}
                        </div>
                    </div>
                    <div class="extra-price-wrap d-flex justify-content-between is_mobile">
                        <div class="flex-grow-1">
                            <label>
                                {{__("Total Price")}}:
                            </label>
                        </div>
                        <div class="total-room-price">@{{total_price_html}}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="control-book">
                        <div class="total-room-price">
                            <span> {{__("Total Price")}}:</span> @{{total_price_html}}
                        </div>
                        <div v-if="is_deposit_ready" class="total-room-price">
                            <span>{{__("Pay now")}}</span>
                            @{{pay_now_price_html}}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="g-rules">
            <h3>{{__("Rules")}}</h3>
            <div class="description">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="key">{{__("Check In")}}</div>
                    </div>
                    <div class="col-lg-8">
                        <div class="value">	{{$row->check_in_time}} </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="key">{{__("Check Out")}}</div>
                    </div>
                    <div class="col-lg-8">
                        <div class="value">	{{$row->check_out_time}} </div>
                    </div>
                </div>
                @if($translation->policy)
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="key">{{__("Hotel Policies")}}</div>
                        </div>
                        <div class="col-lg-8">
                            @foreach($translation->policy as $key => $item)
                                <div class="item @if($key > 1) d-none @endif">
                                    <div class="strong">{{$item['title'] ?? ''}}</div>
                                    <div class="context">{!! $item['content'] !!}</div>
                                </div>
                            @endforeach
                            @if( count($translation->policy) > 2)
                                <div class="btn-show-all">
                                    <span class="text">{{__("Show All")}}</span>
                                    <i class="fa fa-caret-down"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="bc-hr"></div>
        @includeIf("Hotel::frontend.layouts.details.hotel-surrounding")

        @if($row->map_lat && $row->map_lng)
            <div class="g-location">
                <div class="location-title">
                    <h3>{{__("Location")}}</h3>
                    @if($translation->address)
                        <div class="address">
                            <i class="icofont-location-arrow"></i>
                            {{$translation->address}}
                        </div>
                    @endif
                </div>

                <div class="location-map">
                    <div id="map_content"></div>
                </div>
            </div>
        @endif

        <div class="end_room_sticky"></div>
        <div class="alert alert-warning" v-if="!firstLoad && !rooms.length">
            {{__("No room available with your selected date. Please change your search critical")}}
        </div>
    </div>
    @include('Hotel::frontend.layouts.details.hotel-animals')
            <div class="pt-2">
                <div v-if="total_price > 0 || animalCheckPassed">
                    <button type="button" class="btn btn-primary btn-lg w-100"
                            @click="doSubmit($event)"
                            :class="{'disabled':onSubmit}">
                        <span>{{__("Book Now")}}</span>
                        <i v-show="onSubmit" class="fa fa-spinner fa-spin"></i>
                    </button>
                </div>
            </div>
</div>

@include("Booking::frontend.global.enquiry-form",['service_type'=>'hotel'])

<div class="g-all-attribute is_mobile">
    @include('Hotel::frontend.layouts.details.hotel-attributes')
</div>

<div class="modal fade" id="confirmAnimalBooking" tabindex="-1" role="dialog" aria-labelledby="confirmBookingAnimalOnlyLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <p id="confirmBookingAnimalText">Бронируете только охоту, без жилья?</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary" id="confirmBookingAnimalYes">Да</button>
                <button type="button" class="btn btn-secondary" id="confirmBookingAnimalNo" data-bs-dismiss="modal">Нет</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmSingleHotelBooking" tabindex="-1" role="dialog" aria-labelledby="confirmSingleBookingLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <p id="confirmSingleBookingText"></p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary" id="confirmSingleBookingYes">Да</button>
                <button type="button" class="btn btn-secondary" id="confirmSingleBookingNo">Нет</button>
            </div>
        </div>
    </div>
</div>



