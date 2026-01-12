@extends('layouts.user')

@section('content')
    <h2 class="title-bar no-border-bottom">
        {{ __('Availability Rooms') }}
        <div class="title-action">
            <a class="btn btn-info" href="{{ route('hotel.vendor.room.index', ['hotel_id' => $hotel->id,'user' => $user->id, 'viewAdminCabinet' => $viewAdminCabinet]) }}">
                <i class="fa fa-hand-o-right"></i> {{ __('Manage Rooms') }}
            </a>
        </div>
    </h2>
    <div class="language-navigation">
        <div class="panel-body">
            <div class="filter-div d-flex justify-content-between">
                <div class="col-left">
                    <form method="get" action="" class="filter-form filter-form-left d-flex flex-column flex-sm-row"
                        role="search">
                        <input type="text" name="s" value="{{ Request()->s }}"
                            placeholder="{{ __('Search by name') }}" class="form-control">&nbsp;&nbsp;
                        <button class="btn-info btn btn-icon btn_search btn-sm" type="submit">{{ __('Search') }}</button>
                    </form>
                </div>
                <div class="col-right number-col">
                    @if ($rows->total() > 0)
                        <span
                            class="count-string">{{ __('Showing :from - :to of :total rooms', ['from' => $rows->firstItem(), 'to' => $rows->lastItem(), 'total' => $rows->total()]) }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @if (count($rows))
        <div class="user-panel">
            <div class="panel-body no-padding" style="background: #f4f6f8;padding: 0px 15px;">
                <div class="row calendar-block">
                    <div class="col-md-3 user-panel-col custom-padding" style="border-right: 1px solid #dee2e6;">
                        <ul class="nav nav-tabs  flex-column vertical-nav" id="items_tab" role="tablist">
                            @foreach ($rows as $k => $item)
                                <li class="nav-item event-name ">
                                    <a class="nav-link" data-id="{{ $item->id }}" data-toggle="tab"
                                        href="#calendar-{{ $item->id }}"
                                        title="{{ $item->title }}">{{ $item->title }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="col-md-9" style="background: white;padding: 15px;">
                        <div id="dates-calendar" class="dates-calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-warning">{{ __('No rooms found') }}</div>
    @endif
    <div class="d-flex justify-content-center">
        {{ $rows->appends($request->query())->links() }}
    </div>
    <div id="bc_modal_calendar" class="modal fade">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Date Information') }}</h5>
                    <button type="button" class="close" @click="hide" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="row form_modal_calendar form-horizontal" novalidate onsubmit="return false">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Date Ranges') }}</label>
                                <input readonly type="text" class="form-control has-daterangepicker">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Status') }}</label>
                                <br>
                                <label><input true-value=1 false-value=0 type="checkbox" v-model="form.active">
                                    {{ __('Available for booking?') }}</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-1">
                                <label>{{ __('Day of week') }}</label>
                            </div>
                            <div class="form-group">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" v-model="form.day_of_week_select"
                                        id="monday" value="1">
                                    <label class="form-check-label" for="monday">{{ __('Monday') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" v-model="form.day_of_week_select"
                                        id="tuesday" value="2">
                                    <label class="form-check-label" for="tuesday">{{ __('Tuesday') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" v-model="form.day_of_week_select"
                                        id="wednesday" value="3">
                                    <label class="form-check-label" for="wednesday">{{ __('Wednesday') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" v-model="form.day_of_week_select"
                                        id="thursday" value="4">
                                    <label class="form-check-label" for="thursday">{{ __('Thursday') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" v-model="form.day_of_week_select"
                                        id="friday" value="5">
                                    <label class="form-check-label" for="friday">{{ __('Friday') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" v-model="form.day_of_week_select"
                                        id="saturday" value="6">
                                    <label class="form-check-label" for="saturday">{{ __('Saturday') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" v-model="form.day_of_week_select"
                                        id="sunday" value="7">
                                    <label class="form-check-label" for="sunday">{{ __('Sunday') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6" v-show="form.active">
                            <div class="form-group">
                                <label>{{ __('Price') }}</label>
                                <input type="number" v-model="form.price" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Number of room') }} 
                                    <span v-if="form.max_number && form.active" class="text-muted">({{ __('Max') }}: @{{ form.max_number }})</span>
                                </label>
                                <input type="number" v-model.number="form.number" :max="form.max_number" :min="form.active ? 1 : 0" class="form-control" 
                                       @input="validateNumber" :disabled="!form.active">
                                <small v-if="form.max_number && form.active && form.number > form.max_number" class="text-danger">
                                    {{ __('Number cannot exceed maximum') }} (@{{ form.max_number }})
                                </small>
                                <small v-if="!form.active" class="text-muted">
                                    {{ __('Set room as available to change number') }}
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6 d-none" v-show="form.active">
                            <div class="form-group">
                                <label>{{ __('Instant Booking?') }}</label>
                                <br>
                                <label><input true-value=1 false-value=0 type="checkbox" v-model="form.is_instant">
                                    {{ __('Enable instant booking') }}</label>
                            </div>
                        </div>
                    </form>
                    <div v-if="lastResponse.message">
                        <br>
                        <div class="alert" v-bind:class="!lastResponse.status ? 'alert-danger' : 'alert-success'">
                            @{{ lastResponse.message }}</div>
                    </div>
                </div>
                <div class="modal-footer">
{{--                    <button type="button" class="btn btn-secondary" @click="hide">{{ __('Close') }}</button>--}}
                    <button type="button" class="btn btn-primary" @click="saveForm">{{ __('Save changes') }}</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="bc_modal_booking" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Бронь <span id="modalBookingId"></span></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="modalBookingBody">
                    Загрузка...
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('libs/fullcalendar-4.2.0/core/main.css') }}">
    <link rel="stylesheet" href="{{ asset('libs/fullcalendar-4.2.0/daygrid/main.css') }}">
    <link rel="stylesheet" href="{{ asset('libs/daterange/daterangepicker.css') }}">

    <style>
        .event-name {
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
        }

        #dates-calendar .loading {}

        .fc-event.available-event {
            /*background-color: rgba(255, 255, 255, 0.5) !important;*/
            /*color: #000 !important;*/
            /*border-bottom: 4px solid #28a745 !important;*/
        }

        .fc-event.full-book-event {
            /* Убрали красный цвет ячейки - окрашиваем только блок с надписью */
        }

        /* Частично забронированные события */
        .fc-event.active-event {
            /*background-color: rgba(40, 167, 69, 0.5) !important;*/
            /*color: #000 !important; */
            /*border: 1px solid #28a745 !important;*/
        }

        .fc table {
            /*width: 120% !important;*/
        }
        .fc-day-custom {
            display: flex;
            flex-direction: column;
        }

        .fc-bookings .booking-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 2px;
        }

        .fc-bookings .booking-id {
            font-weight: 600;
        }

        .fc-bookings .booking-status {
            font-size: 13px;
            color: #555;
        }

        .fc-price-block {
            background-color: #2791fe;
            color: #fff;
            font-weight: 600;
            text-align: center;
            border-radius: 3px;
            padding: 2px 12px;
            margin-top: auto;
        }
    </style>
@endpush

@push('js')
    <script src="{{ asset('libs/daterange/moment.min.js') }}"></script>
    <script src="{{ asset('libs/daterange/daterangepicker.min.js?_ver=' . config('app.asset_version')) }}"></script>
    <script src="{{ asset('libs/fullcalendar-4.2.0/core/main.js') }}"></script>
    <script src="{{ asset('libs/fullcalendar-4.2.0/interaction/main.js') }}"></script>
    <script src="{{ asset('libs/fullcalendar-4.2.0/daygrid/main.js') }}"></script>
    <script src="{{ asset('libs/fullcalendar-4.2.0/core/locales/ru.js') }}"></script>

    <script>
        var calendarEl, calendar, lastId, formModal;
        $('#items_tab').on('show.bs.tab', function(e) {
            calendarEl = document.getElementById('dates-calendar');
            lastId = $(e.target).data('id');
            if (calendar) {
                calendar.destroy();
            }
            calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'ru',
                buttonText: {
                    today: '{{ __('Today') }}',
                },

                plugins: ['dayGrid', 'interaction'],
                header: {},
                selectable: true,
                selectMirror: false,
                allDay: false,
                editable: false,
                eventLimit: true,
                defaultView: 'dayGridMonth',
                firstDay: daterangepickerLocale.first_day_of_week,
                events: {
                    url: "{{ route('hotel.vendor.room.availability.loadDates', ['hotel_id' => $hotel->id]) }}",
                    extraParams: {
                        id: lastId,
                    }
                },
                loading: function(isLoading) {
                    if (!isLoading) {
                        $(calendarEl).removeClass('loading');
                    } else {
                        $(calendarEl).addClass('loading');
                    }
                },
                select: function(arg) {
                    formModal.show({
                        start_date: moment(arg.start).format('YYYY-MM-DD'),
                        end_date: moment(arg.end).format('YYYY-MM-DD'),
                    });
                },
                eventClick: function(info) {
                    // const maxNumber = info.event.extendedProps.max_number;
                    // const events = calendar.getEvents();

                    // const dayEvent = events.find(e =>
                    //     moment(e.start).format('YYYY-MM-DD') === info.dateStr
                    // );
                    //
                    // if (!dayEvent) return;

                    // const maxNumber  = dayEvent.extendedProps.max_number ?? 0;
                    // const freeNumber = dayEvent.extendedProps.number ?? dayEvent.extendedProps.max_number ?? 0;

                    // formModal.show({
                    //     start_date: info.dateStr,
                    //     end_date: info.dateStr,
                    //     max_number: maxNumber,
                    //     free_number: freeNumber,
                    //     number: Math.min(1, freeNumber)
                    // });


                    var form = Object.assign({}, info.event.extendedProps);
                    form.start_date = moment(info.event.start).format('YYYY-MM-DD');
                    form.end_date = moment(info.event.start).format('YYYY-MM-DD');
                    form.max_number = info.event.extendedProps.max_number;
                    // Передаем number и active из события
                    form.number = info.event.extendedProps.number !== undefined ? info.event.extendedProps.number : info.event.number;
                    form.active = info.event.extendedProps.active !== undefined ? info.event.extendedProps.active : (info.event.classNames && !info.event.classNames.includes('blocked-event') ? 1 : 0);
                    form.price = info.event.extendedProps.price !== undefined ? info.event.extendedProps.price : info.event.price;
                    formModal.show(form);
                },
                eventRender: function(info) {
                    const bookingsHtml = info.event.extendedProps.bookings_html || '';
                    const priceHtml = `<div class="fc-price-block">${info.event.title}</div>`;

                    $(info.el).html(`
        <div class="fc-day-custom">
            <div class="fc-bookings">${bookingsHtml}</div>
            ${priceHtml}
        </div>
    `);
                    
                    // Сначала сбрасываем все стили блока
                    $(info.el).find('.fc-price-block').css({
                        backgroundColor: '',
                        color: ''
                    });

                    $(info.el).find('.booking-id').each(function() {
                        const idText = $(this).text().replace('Б', '');
                        const bookingCode = $(this).data('code');
                        $(this).css({
                            'color': '#2791fe',
                            'cursor': 'pointer',
                            'text-decoration': 'underline',
                            'margin-right': '6px'
                        });
                        $(this).off('click').on('click', function(e) {
                            e.stopPropagation();

                            {{--let url = `/user/booking-history/?user={{ $user->id }}&viewAdminCabinet=@json($viewAdminCabinet)`;--}}
                            let url = `/user/booking-history/?user={{ $user->id }}`;
                            //
                            //     url += '?adminBase=true';
                            //
                            window.open(url, '_blank');



                            // Загружаем данные через AJAX и открываем новое модальное окно
                            // $('#modalBookingId').text('Б' + idText);
                            // $('#modalBookingBody').html('Загрузка...');
                            //
                            // $.ajax({
                            //     url: '/booking/' + idText, // или ваш route('booking.show', idText)
                            //     method: 'GET',
                            //     success: function(response) {
                            //         // response может быть HTML для модального окна
                            //         $('#modalBookingBody').html(response);
                            //     },
                            //     error: function() {
                            //         $('#modalBookingBody').html('Ошибка загрузки брони');
                            //     }
                            // });

                            // $('#bc_modal_booking').modal('show');
                        });
                    });

                    // Заблокированные дни - красный цвет блока (приоритет над всеми остальными)
                    if (info.event.classNames.includes('blocked-event')) {
                        info.el.style.pointerEvents = 'none';

                        $(info.el).find('.fc-price-block').css({
                            backgroundColor: '#fe2727',
                            color: '#fff'
                        });
                    }
                    // Полная бронь — красим только блок с надписью в оранжевый (приоритет над изменениями)
                    else if (info.event.classNames.includes('full-book-event') || info.event.extendedProps.classNames?.includes('full-book-event')) {
                        $(info.el).find('.fc-price-block').css({
                            backgroundColor: '#ff9800',
                            color: '#fff'
                        });
                    } else {
                        // Изменение цены - меняем цвет блока с ценой и количеством (желтый)
                        // Только для активных дней (не заблокированных и не полной брони)
                        if (info.event.extendedProps.price_changed) {
                            $(info.el).find('.fc-price-block').css({
                                backgroundColor: '#fff3cd',
                                color: '#856404'
                            });
                        }
                        // Изменение количества номеров - меняем цвет блока с ценой и количеством (синий)
                        // Только если цена не изменена
                        else if (info.event.extendedProps.number_changed) {
                            $(info.el).find('.fc-price-block').css({
                                backgroundColor: '#d1ecf1',
                                color: '#0c5460'
                            });
                        }
                    }
                }
            });
            calendar.render();
        });

        $('.event-name:first-child a').trigger('click');

        formModal = new Vue({
            el: '#bc_modal_calendar',
            data: {
                lastResponse: {
                    status: null,
                    message: ''
                },
                form: {
                    id: '',
                    price: '',
                    start_date: '',
                    end_date: '',
                    is_instant: '',
                    enable_person: 0,
                    min_guests: 0,
                    max_guests: 0,
                    active: 0,
                    number: 1,
                    max_number: null,
                    day_of_week_select: []
                },
                formDefault: {
                    id: '',
                    price: '',
                    start_date: '',
                    end_date: '',
                    is_instant: '',
                    enable_person: 0,
                    min_guests: 0,
                    max_guests: 0,
                    active: 0,
                    number: 1,
                    max_number: null,
                    day_of_week_select: []
                },
                person_types: [

                ],
                person_type_item: {
                    name: '',
                    desc: '',
                    min: '',
                    max: '',
                    price: '',
                },
                onSubmit: false
            },
            methods: {
                show: function(form) {
                    $(this.$el).modal('show');
                    this.lastResponse.message = '';
                    this.onSubmit = false;

                    if (typeof form != 'undefined') {
                        form.day_of_week_select = []
                        this.form = Object.assign({}, form);
                        
                        // Убеждаемся, что number - это число, а не null/undefined
                        if (this.form.number === null || this.form.number === undefined || this.form.number === '') {
                            this.form.number = this.form.active ? 1 : 0;
                        } else {
                            this.form.number = parseInt(this.form.number) || (this.form.active ? 1 : 0);
                        }
                        
                        if (typeof this.form.person_types == 'object') {
                            this.person_types = Object.assign({}, this.form.person_types);
                        }

                        if (form.start_date) {
                            var drp = $('.has-daterangepicker').data('daterangepicker');
                            drp.setStartDate(moment(form.start_date).format(bookingCore.date_format));
                            drp.setEndDate(moment(form.end_date).format(bookingCore.date_format));

                        }
                    }
                },
                hide: function() {
                    $(this.$el).modal('hide');
                    this.form = Object.assign({}, this.formDefault);
                    this.person_types = [];
                },
                saveForm: function() {
                    this.form.target_id = lastId;
                    var me = this;
                    me.lastResponse.message = '';
                    if (this.onSubmit) return;

                    if (!this.validateForm()) return;

                    this.onSubmit = true;
                    this.form.person_types = Object.assign({}, this.person_types);
                    
                    // Убеждаемся, что number всегда передается, даже если 0
                    var formData = Object.assign({}, this.form);
                    if (formData.number === null || formData.number === undefined || formData.number === '') {
                        formData.number = formData.active ? 1 : 0;
                    }
                    formData.number = parseInt(formData.number) || (formData.active ? 1 : 0);
                    
                    $.ajax({
                        url: '{{ route('hotel.vendor.room.availability.store', ['hotel_id' => $hotel->id]) }}',
                        data: formData,
                        dataType: 'json',
                        method: 'post',
                        success: function(json) {
                            if (json.status) {
                                if (calendar)
                                    calendar.refetchEvents();
                                me.hide();
                            }
                            me.lastResponse = json;
                            me.onSubmit = false;
                        },
                        error: function(e) {
                            me.onSubmit = false;
                        }
                    });
                },
                validateForm: function() {
                    if (!this.form.start_date) return false;
                    if (!this.form.end_date) return false;

                    // Валидация количества номеров
                    if (this.form.active && this.form.max_number) {
                        if (this.form.number > this.form.max_number) {
                            this.lastResponse = {
                                status: false,
                                message: '{{ __("Number of rooms cannot exceed maximum") }} (' + this.form.max_number + ')'
                            };
                            return false;
                        }
                        if (this.form.number < 1) {
                            this.lastResponse = {
                                status: false,
                                message: '{{ __("Number of rooms must be at least 1 for available days") }}'
                            };
                            return false;
                        }
                    }
                    // Для неактивных дней разрешаем 0
                    if (!this.form.active && this.form.number < 0) {
                        this.lastResponse = {
                            status: false,
                            message: '{{ __("Number of rooms cannot be negative") }}'
                        };
                        return false;
                    }

                    return true;
                },
                validateNumber: function() {
                    // Автоматически ограничиваем значение, если превышает максимум
                    if (this.form.max_number && this.form.number > this.form.max_number) {
                        this.form.number = this.form.max_number;
                    }
                    // Минимум 1 только для активных дней
                    if (this.form.active && this.form.number < 1) {
                        this.form.number = 1;
                    }
                    // Для неактивных дней разрешаем 0
                    if (!this.form.active && this.form.number < 0) {
                        this.form.number = 0;
                    }
                },
                addItem: function() {
                    console.log(this.person_types);
                    this.person_types.push(Object.assign({}, this.person_type_item));
                },
                deleteItem: function(index) {
                    this.person_types.splice(index, 1);
                }
            },
            created: function() {
                var me = this;
                this.$nextTick(function() {
                    $('.has-daterangepicker').daterangepicker({
                            "locale": {
                                "format": bookingCore.date_format
                            }
                        })
                        .on('apply.daterangepicker', function(e, picker) {
                            me.form.start_date = picker.startDate.format('YYYY-MM-DD');
                            me.form.end_date = picker.endDate.format('YYYY-MM-DD');
                        });

                    $(me.$el).on('hide.bs.modal', function() {

                        this.form = Object.assign({}, this.formDefault);
                        this.person_types = [];

                    });

                })
            },
            mounted: function() {
                // $(this.$el).modal();
            }
        });
    </script>
@endpush
