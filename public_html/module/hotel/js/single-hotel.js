(function ($) {
    var hotelRoomForm = new Vue({
        el:'#hotel-rooms',
        data:{
            id:'',
            extra_price:[],
            person_types:[
                [

                ]
            ],
            buyer_fees:[],
            message:{
                content:'',
                type:false
            },
            html:'',
            onSubmit:false,
            start_date:'',
            end_date:'',
            start_date_html:'',
            start_date_animal: '',
            end_date_animal: '',
            start_date_animal_html: 'Выберите пожалуйста',
            number_of_guests:0,
            step:1,
            start_date_obj:'',
            adults:1,
            hunting_adults:1,
            children:0,
            allEvents:[],
            rooms:[],
            onLoadAvailability:false,
            onLoadAnimalAvailability:false,
            firstLoad:true,
            i18n:[],
            total_price_before_fee:0,
            total_price_fee:0,

            is_form_enquiry_and_book:false,
            enquiry_type:'book',
            enquiry_is_submit:false,
            enquiry_name:"",
            enquiry_email:"",
            enquiry_phone:"",
            enquiry_note:"",
            confirm_message: "",
            show_confirm_only_hotel: false,
            animal_id: '',
            animalCheckPassed: false,
            animalPrice: 0,
            hunterCount: 0,
            urlParams: {}
        },
        watch:{
            extra_price:{
                handler:function f() {
                    this.step = 1;
                    // this.handleTotalPrice();
                },
                deep:true
            },
            start_date(){
                this.step = 1;
            },
            guests(){
                this.step = 1;
            },
            person_types:{
                handler:function f() {
                    this.step = 1;
                },
                deep:true
            },
        },
        computed:{
            total_price:function(){
                var me = this;
                if (me.start_date !== "" && me.total_rooms > 0) {
                    var guests = me.children + me.adults;
                    var total_price = 0;
                    var startDate = new Date(me.start_date).getTime();
                    var endDate = new Date(me.end_date).getTime();
                    this.rooms.forEach(function (item) {
                        if(item.number_selected){
                            total_price += item.price* parseInt(item.number_selected);
                        }
                    });

                    var duration_in_day = moment(endDate).diff(moment(startDate), 'days');
                    for (var ix in me.extra_price) {
                        var item = me.extra_price[ix];
                        if(!item.price) continue;
                        var type_total = 0;
                        if (item.enable == 1) {
                            switch (item.type) {
                                case "one_time":
                                    type_total += parseFloat(item.price);
                                    break;
                                case "per_day":
                                    type_total += parseFloat(item.price) * Math.max(1,duration_in_day);
                                    break;
                            }
                            if (typeof item.per_person !== "undefined") {
                                type_total = type_total * guests;
                            }
                            total_price += type_total;
                        }
                    }
                    this.total_price_before_fee = total_price;

                    var total_fee = 0;
                    // for (var ix in me.buyer_fees) {
                    //     var item = me.buyer_fees[ix];
                    //
                    //     if(!item.price) continue;
                    //
                    //     //for Fixed
                    //     var fee_price = parseFloat(item.price);
                    //
                    //     //for Percent
                    //     if (typeof item.unit !== "undefined" && item.unit === "percent" ) {
                    //         fee_price = ( total_price / 100 ) * fee_price;
                    //     }
                    //
                    //     if (typeof item.per_person !== "undefined") {
                    //         fee_price = fee_price * guests;
                    //     }
                    //     total_fee += fee_price;
                    // }
                    total_price += total_fee;
                    this.total_price_fee = total_fee;

                    return total_price;
                }
                return 0;
            },
            total_rooms:function(){
                var me = this;
                if (me.start_date !== "") {
                    var t = 0;
                    this.rooms.forEach(function (item) {
                        if(item.number_selected){
                            t += parseInt(item.number_selected);
                        }
                    })
                    return t;
                }
                return 0;
            },
            total_price_html:function(){
                if(!this.total_price) return '';
                setTimeout(function () {
                    $('[data-toggle="tooltip"]').tooltip();
                    $(document).trigger("scroll");
                },200);
                return window.bc_format_money(this.total_price);
            },
            pay_now_price:function(){
                if(this.is_deposit_ready){
                    var total_price_depossit = 0;

                    var tmp_total_price = this.total_price;
                    var deposit_fomular = this.deposit_fomular;
                    if(deposit_fomular === "deposit_and_fee"){
                        tmp_total_price = this.total_price_before_fee;
                    }

                    switch (this.deposit_type) {
                        case "percent":
                            total_price_depossit =  tmp_total_price * this.deposit_amount / 100;
                            break;
                        default:
                            total_price_depossit =  this.deposit_amount;
                    }
                    if(deposit_fomular === "deposit_and_fee"){
                        total_price_depossit = total_price_depossit + this.total_price_fee;
                    }

                    return  total_price_depossit
                }
                return this.total_price;
            },
            pay_now_price_html:function(){
                return window.bc_format_money(this.pay_now_price);
            },
            is_deposit_ready:function () {
                if(this.deposit && this.deposit_amount) return true;
                return false;
            },
            daysOfWeekDisabled(){
                var res = [];

                for(var k in this.open_hours)
                {
                    if(typeof this.open_hours[k].enable == 'undefined' || this.open_hours[k].enable !=1 ){

                        if(k == 7){
                            res.push(0);
                        }else{
                            res.push(k);
                        }
                    }
                }

                return res;
            },
            guests(){
                return this.children + this.adults;
            }
        },
        created:function(){
            for(var k in bc_booking_data){
                this[k] = bc_booking_data[k];
            }
            // Инициализируем параметры из URL перед проверкой доступности
            this.initFromUrlParams();
            // Проверяем, есть ли параметры из URL - если есть, проверку доступности сделаем после их применения
            var hasUrlParams = this.urlParams && (this.urlParams.start || this.urlParams.adults !== null || this.urlParams.children !== null || this.urlParams.animal_id);
            if (!hasUrlParams) {
                this.checkAvailability();
            }
        },
        mounted(){
            var me = this;
            /*$(".hotel_room_book_status").sticky({
                topSpacing:30,
                bottomSpacing:$(document).height() - $('.end_tour_sticky').offset().top + 40
            });*/


            var options = {
				maxSpan: {
					"days": 30
				},
                showCalendar: false,
                sameDate: true,
                autoApply           : true,
                disabledPast        : true,
                dateFormat          : bookingCore.date_format,
                enableLoading       : true,
                showEventTooltip    : true,
                classNotAvailable   : ['disabled', 'off'],
                disableHightLight: true,
                minDate:this.minDate,
                opens: bookingCore.rtl ? 'left':'right',
                locale:{
                    direction: bookingCore.rtl ? 'rtl':'ltr',
                    firstDay:daterangepickerLocale.first_day_of_week
                },
                isInvalidDate:function (date) {
                    for(var k = 0 ; k < me.allEvents.length ; k++){
                        var item = me.allEvents[k];
                        if(item.start == date.format('YYYY-MM-DD')){
                            return item.active ? false : true;
                        }
                    }
                    return false;
                },
                addClassCustom:function (date) {
                    for(var k = 0 ; k < me.allEvents.length ; k++){
                        var item = me.allEvents[k];
                        if(item.start == date.format('YYYY-MM-DD') && item.classNames !== undefined){
                            var class_names = "";
                            for(var i = 0 ; i < item.classNames.length ; i++){
                                var classItem = item.classNames[i];
                                class_names += " "+classItem;
                            }
                            return class_names;
                        }
                    }
                    return "";
                }
            };
            var animalOptions = {
                singleDatePicker: true,
                maxSpan: {
                    "days": 30
                },
                showCalendar: false,
                sameDate: true,
                autoApply           : true,
                disabledPast        : true,
                dateFormat          : bookingCore.date_format,
                enableLoading       : true,
                showEventTooltip    : true,
                classNotAvailable   : ['disabled', 'off'],
                disableHightLight: true,
                minDate:this.minDate,
                opens: bookingCore.rtl ? 'left':'right',
                locale:{
                    direction: bookingCore.rtl ? 'rtl':'ltr',
                    firstDay:daterangepickerLocale.first_day_of_week
                },
                isInvalidDate:function (date) {
                    for(var k = 0 ; k < me.allEvents.length ; k++){
                        var item = me.allEvents[k];
                        if(item.start == date.format('YYYY-MM-DD')){
                            return item.active ? false : true;
                        }
                    }
                    return false;
                },
                addClassCustom:function (date) {
                    for(var k = 0 ; k < me.allEvents.length ; k++){
                        var item = me.allEvents[k];
                        if(item.start == date.format('YYYY-MM-DD') && item.classNames !== undefined){
                            var class_names = "";
                            for(var i = 0 ; i < item.classNames.length ; i++){
                                var classItem = item.classNames[i];
                                class_names += " "+classItem;
                            }
                            return class_names;
                        }
                    }
                    return "";
                }
            };

            if (typeof  daterangepickerLocale == 'object') {
                options.locale = _merge(daterangepickerLocale, options.locale);
                animalOptions.locale = _merge(daterangepickerLocale, animalOptions.locale);
            }


            this.$nextTick(() => {
                $(this.$refs.hotelStartDate).daterangepicker(options).on('apply.daterangepicker', (ev, picker) => {
                    if(picker.endDate.diff(picker.startDate,'day') <= 0){
                        picker.endDate.add(1,'day');
                    }
                    this.start_date = picker.startDate.format('YYYY-MM-DD');
                    this.end_date = picker.endDate.format('YYYY-MM-DD');
                    this.start_date_html = picker.startDate.format(bookingCore.date_format) +
                        ' <i class="fa fa-long-arrow-right" style="font-size: inherit"></i> ' +
                        picker.endDate.format(bookingCore.date_format);

                    // Ограничиваем календарь охоты датами проживания
                    var animalPicker = $(this.$refs.animalStartDate).data('daterangepicker');
                    if (animalPicker) {
                        animalPicker.minDate = picker.startDate.clone();
                        animalPicker.maxDate = picker.endDate.clone().subtract(1, 'day');
                        // Если текущая выбранная дата охоты выходит за диапазон — сбрасываем
                        if (this.start_date_animal) {
                            var hunt = moment(this.start_date_animal, 'YYYY-MM-DD');
                            if (hunt.isBefore(animalPicker.minDate) || hunt.isAfter(animalPicker.maxDate)) {
                                this.start_date_animal = '';
                                this.start_date_animal_html = bookingCoreApp && bookingCoreApp.i18n && bookingCoreApp.i18n.select_date
                                    ? bookingCoreApp.i18n.select_date
                                    : 'Выберите пожалуйста';
                            }
                        }
                    }
                });
            });

            this.$nextTick(() => {
                $(this.$refs.animalStartDate).daterangepicker(animalOptions).on('apply.daterangepicker',(ev, picker) => {
                        this.start_date_animal = picker.startDate.format('YYYY-MM-DD');
                        this.end_date_animal = this.start_date_animal;
                        this.start_date_animal_html = picker.startDate.format(bookingCore.date_format);
                    })
            })

            // Применяем параметры из URL после инициализации всех компонентов
            // Используем setTimeout для гарантии, что все компоненты (включая smart-search) загружены
            // Увеличиваем задержку для smart-search, так как он инициализируется в home.js при document.ready
            var me = this;
            setTimeout(() => {
                me.applyUrlParams();
            }, 800);

        },
        methods:{
            // Читает параметры из URL и сохраняет их в переменные компонента
            initFromUrlParams: function() {
                var urlParams = new URLSearchParams(window.location.search);
                var startParam = urlParams.get('start');
                var endParam = urlParams.get('end');
                var adultsParam = urlParams.get('adults');
                var childrenParam = urlParams.get('children');
                var animalIdParam = urlParams.get('animal_id');

                // Сохраняем параметры для последующего применения
                this.urlParams = {
                    start: startParam,
                    end: endParam,
                    adults: adultsParam ? parseInt(adultsParam) : null,
                    children: childrenParam ? parseInt(childrenParam) : null,
                    animal_id: animalIdParam
                };
            },
            // Применяет параметры из URL к форме
            applyUrlParams: function() {
                var me = this;
                var params = this.urlParams || {};

                // Применяем даты заезда-выезда
                if (params.start && params.end) {
                    try {
                        // Преобразуем даты в нужный формат (DD.MM.YYYY -> YYYY-MM-DD)
                        var startMoment = moment(params.start, ['DD.MM.YYYY', 'YYYY-MM-DD', 'MM/DD/YYYY'], true);
                        var endMoment = moment(params.end, ['DD.MM.YYYY', 'YYYY-MM-DD', 'MM/DD/YYYY'], true);

                        if (startMoment.isValid() && endMoment.isValid()) {
                            // Устанавливаем даты в Vue модель
                            this.start_date = startMoment.format('YYYY-MM-DD');
                            this.end_date = endMoment.format('YYYY-MM-DD');
                            this.start_date_html = startMoment.format(bookingCore.date_format) +
                                ' <i class="fa fa-long-arrow-right" style="font-size: inherit"></i> ' +
                                endMoment.format(bookingCore.date_format);

                            // Устанавливаем даты в datepicker
                            var datePicker = $(this.$refs.hotelStartDate).data('daterangepicker');
                            if (datePicker) {
                                datePicker.setStartDate(startMoment);
                                datePicker.setEndDate(endMoment);
                            }
                        }
                    } catch (e) {
                        console.error('Error parsing dates from URL:', e);
                    }
                }

                // Применяем количество гостей
                if (params.adults !== null && params.adults > 0) {
                    this.adults = params.adults;
                }
                if (params.children !== null && params.children >= 0) {
                    this.children = params.children;
                }

                // Применяем выбор животного
                if (params.animal_id) {
                    var me = this;
                    this.selectAnimalFromUrl(params.animal_id);
                }
                
                // Если были установлены даты заезда-выезда, вызываем проверку доступности
                if (params.start && params.end && this.start_date && this.end_date) {
                    // Используем setTimeout для гарантии, что все изменения применены
                    setTimeout(() => {
                        this.checkAvailability();
                    }, 100);
                }
            },
            handleTotalPrice:function() {
            },
            formatMoney: function (m) {
                return window.bc_format_money(m);
            },
            clearAnimal() {
                this.animal_id = '';
                this.animalCheckPassed = false;
            },
            // Выбирает животное по ID из URL
            selectAnimalFromUrl: function(animalId) {
                var me = this;
                var attempts = 0;
                var maxAttempts = 20;
                
                // Функция для попытки выбора животного
                var trySelectAnimal = function() {
                    attempts++;
                    // Ищем элементы внутри секции с животными (более точный селектор)
                    var animalSection = document.querySelector('#hotel-rooms') || document.querySelector('.hotel_rooms_form');
                    var animalInput = animalSection ? animalSection.querySelector('.smart-search .child_id') : document.querySelector('.smart-search .child_id');
                    var animalTextInput = animalSection ? animalSection.querySelector('.smart-search-booking-animal') : document.querySelector('.smart-search-booking-animal');
                    
                    // Если элементы не найдены и не достигнут лимит попыток - пробуем еще раз
                    if ((!animalInput || !animalTextInput) && attempts < maxAttempts) {
                        setTimeout(trySelectAnimal, 100);
                        return;
                    }
                    
                    if (!animalInput || !animalTextInput) {
                        console.warn('Animal input elements not found after', attempts, 'attempts');
                        return;
                    }
                    
                    // Находим название животного из списка по умолчанию
                    var defaultListStr = animalTextInput.getAttribute('data-default');
                    if (defaultListStr && defaultListStr.length > 0) {
                        try {
                            var defaultList = JSON.parse(defaultListStr);
                            var selectedAnimal = defaultList.find(function(item) {
                                return String(item.id) === String(animalId);
                            });
                            
                            if (selectedAnimal) {
                                // Находим родительский элемент smart-search
                                var smartSearch = $(animalTextInput).closest('.smart-search');
                                
                                // Пытаемся найти элемент в списке autocomplete и программно кликнуть на него
                                // Это гарантирует правильную инициализацию smart-search
                                var itemElement = smartSearch.find('.bc-autocomplete .item[data-id="' + animalId + '"]');
                                if (itemElement.length > 0) {
                                    // Если элемент найден в списке - просто кликаем на него
                                    // bcAutocomplete сам установит все нужные значения
                                    itemElement[0].click();
                                    
                                    // Обновляем Vue модель
                                    me.animal_id = animalId;
                                } else {
                                    // Если элемент еще не найден в списке, пробуем еще раз через небольшую задержку
                                    if (attempts < maxAttempts) {
                                        setTimeout(trySelectAnimal, 150);
                                        return;
                                    } else {
                                        // Если после всех попыток элемент не найден, устанавливаем значения вручную
                                        var animalText = selectedAnimal.title || selectedAnimal.name || '';
                                        
                                        // Очищаем текст от дефисов как делает bcAutocomplete
                                        var cleanText = animalText.replace(/-/g, "").trim();
                                        cleanText = cleanText.replace(/^-+|-+$/g, '');
                                        
                                        // Устанавливаем текст в видимом поле
                                        $(animalTextInput).val(cleanText).trigger("change");
                                        
                                        // Устанавливаем значение в hidden input и триггерим событие
                                        $(animalInput).val(animalId).trigger("change");
                                        
                                        // Обновляем Vue модель
                                        me.animal_id = animalId;
                                    }
                                }
                            } else {
                                console.warn('Animal with id', animalId, 'not found in list');
                            }
                        } catch (e) {
                            console.error('Error parsing animal list:', e);
                        }
                    } else {
                        // Если список еще не загружен, пробуем еще раз
                        if (attempts < maxAttempts) {
                            setTimeout(trySelectAnimal, 100);
                            return;
                        }
                        // В крайнем случае просто устанавливаем ID
                        $(animalInput).val(animalId);
                        me.animal_id = animalId;
                    }
                };
                
                // Начинаем попытки выбора животного
                trySelectAnimal();
            },
            getUserRoleFromUrl: function() {
                var urlParams = new URLSearchParams(window.location.search);
                return urlParams.get('userRole') || null;
            },

            validate(){
                var me = this;
                // if(!this.start_date || !this.end_date)
                // {
				// 	this.message.status = false;
                //     this.message.content = bc_booking_i18n.no_date_select;
                //     return false;
                // }

                var animalInput = document.querySelector('.child_id');
                var animalId = animalInput ? Number(animalInput.value) : 0;

                let selects = document.querySelectorAll('#hotel-rooms select.custom-select');
                let hasSelectedRoom = false;

                selects.forEach(function(sel){
                    if(parseInt(sel.value) > 0){
                        hasSelectedRoom = true;
                    }
                });

                if (!hasSelectedRoom && animalId > 0) {
                    $('#confirmBookingAnimalText').text("Вы бронируете только охоту, без жилья. Продолжить?");
                    $('#confirmAnimalBooking').modal('show');

                    $('#confirmBookingAnimalYes').off('click').on('click', function () {
                        $('#confirmAnimalBooking').modal('hide');
                        me.doSubmit(null, {skipValidate: true, type: 'animal'});
                    });

                    $('#confirmBookingAnimalNo').off('click').on('click', function() {
                        $('#confirmAnimalBooking').modal('hide');
                    });

                    return false;
                }

                if (animalId === 0 && hasSelectedRoom) {
                    $('#confirmSingleBookingText').text("Вы бронируете только жильё, без охоты. Продолжить?");
                    $('#confirmSingleHotelBooking').modal('show');


                    $('#confirmSingleBookingYes').off('click').on('click', function () {
                        $('#confirmSingleHotelBooking').modal('hide');
                        me.doSubmit(null, {skipValidate: true, type: 'hotel'});
                    });

                    $('#confirmSingleBookingNo').off('click').on('click', function() {
                        $('#confirmSingleHotelBooking').modal('hide');
                    });

                    return false;
                }

                if(!this.guests )
                {
					this.message.status = false;
                    this.message.content = bc_booking_i18n.no_guest_select;
                    return false;
                }

                return true;
            },
            addPersonType(type){
                switch (type){
                    case "adults":
                        this.adults ++ ;
                    break;
                    case "hunting_adults":
                        this.hunting_adults++;
                        break;
                    case "children":
                        this.children ++;
                    break;
                }
                // this.handleTotalPrice();
            },
            minusPersonType(type){
				switch (type){
					case "adults":
						if(this.adults  >=2){
						    this.adults --;
                        }
						break;
                    case "hunting_adults":
                        if(this.hunting_adults >= 1){
                            this.hunting_adults--;
                        }
                        break;
					case "children":
						if(this.children  >=1){
							this.children --;
						}
						break;
				}
                // this.handleTotalPrice();
            },
			checkAvailability:function () {
                var me  = this;
                if(!this.firstLoad){
                    if(!this.start_date || !this.start_date){
                        bookingCoreApp.showError(this.i18n.date_required);
                        return;
                    }
                }
                this.onLoadAvailability = true;

                $.ajax({
                    url:bookingCore.module.hotel+'/checkAvailability',
                    data:{
                        hotel_id:this.id,
                        start_date:this.start_date,
                        end_date:this.end_date,
						firstLoad:me.firstLoad,
                        adults:this.adults,
                        children:this.children,
                    },
                    method:'post',
                    success:function (json) {
                        me.onLoadAvailability = false;
                        me.firstLoad = false;
                        if(json.rooms){
                            me.rooms = json.rooms;
                            me.$nextTick(function () {
                                me.initJs();
                            })
                        }
                        if(json.message){
                            bookingCoreApp.showAjaxMessage(json);
                        }
                    },
                    error:function (e) {
                        me.firstLoad = false;
                        bookingCoreApp.showAjaxError(e);
                    }
                })
			},
            checkAvailabilityForAnimal:function () {
                var me  = this;
                me.animalPrice = 0;

                if(!this.start_date_animal){
                    bookingCoreApp.showError('Пожалуйста, выберите дату охоты');
                    return;
                }

                if(!this.getSelectAnimalId()){
                    bookingCoreApp.showError('Пожалуйста, выберите животное');
                    return;
                }

                // Проверяем диапазон дат проживания ТОЛЬКО если выбраны даты проживания
                // Если выбрано только животное (без номера) - проверку не делаем
                if (this.start_date && this.end_date) {
                    var stayStart = moment(this.start_date, 'YYYY-MM-DD');
                    var stayEnd = moment(this.end_date, 'YYYY-MM-DD'); // дата выезда
                    var huntDate = moment(this.start_date_animal, 'YYYY-MM-DD');

                    // Дата охоты должна быть в интервале [заезд; выезд)
                    if (huntDate.isBefore(stayStart) || !huntDate.isBefore(stayEnd)) {
                        bookingCoreApp.showError('Дата охоты должна быть в пределах дат проживания');
                        return;
                    }
                }

                var me = this;
                
                // Используем $nextTick чтобы Vue точно обновил модель перед чтением
                this.$nextTick(function() {
                    // Читаем значение из Vue модели hunting_adults
                    var huntingAdultsValue = parseInt(me.hunting_adults) || 1;
                    
                    me.onLoadAnimalAvailability = true;
                    
                    // Формируем данные для запроса
                    var requestData = {
                        hotel_id: me.id,
                        animal_id: me.getSelectAnimalId(),
                        start_date: me.start_date_animal,
                        firstLoad: me.firstLoad,
                        adults: huntingAdultsValue,
                    };

                    // Передаем даты проживания только если они выбраны
                    if (me.start_date && me.end_date) {
                        requestData.start_date_hotel = me.start_date;
                        requestData.end_date_hotel = me.end_date;
                    }

                    $.ajax({
                        url:bookingCore.module.animal+'/checkAvailability',
                        data: requestData,
                        method:'post',
                        success:function (json) {
                            me.onLoadAnimalAvailability = false;
                            me.firstLoad = false;

                            if (json.available === true) {
                                me.animalCheckPassed = true;
                                me.animalPrice = json.price;
                                me.hunterCount = json.adults;
                            }

                            if(json.message){
                                bookingCoreApp.showAjaxMessage(json);
                            }
                        },
                        error:function (e) {
                            me.onLoadAnimalAvailability = false;
                            me.firstLoad = false;
                            bookingCoreApp.showAjaxError(e);
                        }
                    });
                });
            },
            getSelectAnimalId(){
                var animalInput = document.querySelector('.child_id');
                return animalInput ? animalInput.value : ''
            },
            doSubmit:function (e, options = {}) {
                const type = options.type || null;

                if(e && e.preventDefault) e.preventDefault();
                if(this.onSubmit) return false;

                // --- ДОПОЛНИТЕЛЬНАЯ ПРОВЕРКА ДЛЯ ЖИВОТНОГО ---
                // Если выбрано животное, запрещаем продолжать,
                // пока не была выполнена проверка доступности (кнопкой «Проверить наличие»)
                var animalId = Number(this.getSelectAnimalId() || 0);
                if (animalId > 0 && !options.skipValidate) {
                    if (!this.start_date_animal) {
                        bookingCoreApp.showError('Пожалуйста, выберите дату охоты');
                        return false;
                    }
                    if (!this.animalCheckPassed) {
                        bookingCoreApp.showError('Пожалуйста, сначала проверьте доступность выбранного животного');
                        return false;
                    }
                    // Если выбраны и номера, и животное - проверяем диапазон дат проживания
                    if (this.start_date && this.end_date) {
                        var stayStart = moment(this.start_date, 'YYYY-MM-DD');
                        var stayEnd = moment(this.end_date, 'YYYY-MM-DD');
                        var huntDate = moment(this.start_date_animal, 'YYYY-MM-DD');
                        if (huntDate.isBefore(stayStart) || !huntDate.isBefore(stayEnd)) {
                            bookingCoreApp.showError('Дата охоты должна быть в пределах дат проживания');
                            return false;
                        }
                    }
                }

                if (!options.skipValidate && !this.validate()) return false;

                this.onSubmit = true;
                var me = this;

                this.message.content = '';

                if(this.step == 1){
                    this.html = '';
                }

                let request = null;

                if (type === 'animal') {
                    var userRole = this.getUserRoleFromUrl();
                    request = {
                        url: bookingCore.url + '/booking/addToCartAnimal',
                        data: {
                            service_id: this.getSelectAnimalId(),
                            service_type: 'animal',
                            type: 'animal',
                            start_date_animal: this.start_date_animal,
                            hunting_adults: this.hunting_adults,
                            animal_id: this.getSelectAnimalId(),
                            hotel_id: this.id,
                            animal_price: this.animalPrice
                        }
                    };
                    if (userRole) {
                        request.data.userRole = userRole;
                    }
                }
                else if (type === 'hotel') {
                    var userRole = this.getUserRoleFromUrl();
                    request = {
                        url: bookingCore.url + '/booking/addToCart',
                        data: {
                            service_id: this.id,
                            hotel_id: this.id,
                            service_type: 'hotel',
                            type: 'hotel',
                            start_date: this.start_date,
                            end_date: this.end_date,
                            extra_price: this.extra_price,
                            adults: this.adults,
                            children: this.children,
                            rooms: this.rooms.map(item =>
                                objectPick(item, ['id', 'number_selected'])
                            )
                        }
                    };
                    if (userRole) {
                        request.data.userRole = userRole;
                    }
                }
                else {
                    var userRole = this.getUserRoleFromUrl();
                    request = {
                        url: bookingCore.url + '/booking/addToCart',
                        data: {
                            service_id: this.id,
                            service_type: 'hotel',
                            type: 'hotel_animal',
                            start_date_animal: this.start_date_animal,
                            start_date: this.start_date,
                            end_date: this.end_date,
                            extra_price: this.extra_price,
                            adults: this.adults,
                            hunting_adults: this.hunting_adults,
                            children: this.children,
                            animal_id: this.getSelectAnimalId(),
                            hotel_id: this.id,
                            animal_price: this.animalPrice,
                            rooms: this.rooms.map(item =>
                                objectPick(item, ['id', 'number_selected'])
                            )
                        }
                    };
                    if (userRole) {
                        request.data.userRole = userRole;
                    }
                }


                $.ajax({
                    url: request.url,
                    data: request.data,
                    dataType:'json',
                    type:'post',
                    success:function(res){

                        if(!res.status){
                            me.onSubmit = false;
                        }
                        if(res.message){
                            bookingCoreApp.showAjaxMessage(res);
                        }

                        if(res.step){
                            me.step = res.step;
                        }
                        if(res.html){
                            me.html = res.html
                        }

                        if(res.url){
                            me.onSubmit = false;
                            window.location.href = res.url
                        }

                        if(res.errors && typeof res.errors == 'object')
                        {
                            var html = '';
                            for(var i in res.errors){
                                html += res.errors[i]+'<br>';
                            }
                            me.message.content = html;

                            bookingCoreApp.showError(html);
                        }
                    },
                    error:function (e) {
                        me.onSubmit = false;

                        bc_handle_error_response(e);

                        if(e.status == 401){
                            //$('.bc_single_book_wrap').modal('hide');
                        }

                        if(e.status != 401 && e.responseJSON){
                            me.message.content = e.responseJSON.message ? e.responseJSON.message : 'Can not booking';
                            me.message.type = false;

                        }
                    }
                })
            },
            doEnquirySubmit:function(e){
                e.preventDefault();
                if(this.onSubmit) return false;
                if(!this.validateenquiry()) return false;
                this.onSubmit = true;
                var me = this;
                this.message.content = '';

                $.ajax({
                    url:bookingCore.url+'/booking/addEnquiry',
                    data:{
                        service_id:this.id,
                        service_type:'hotel',
                        name:this.enquiry_name,
                        email:this.enquiry_email,
                        phone:this.enquiry_phone,
                        note:this.enquiry_note,
                    },
                    dataType:'json',
                    type:'post',
                    success:function(res){
                        if(res.message)
                        {
                            me.message.content = res.message;
                            me.message.type = res.status;
                        }
                        if(res.errors && typeof res.errors == 'object')
                        {
                            var html = '';
                            for(var i in res.errors){
                                html += res.errors[i]+'<br>';
                            }
                            me.message.content = html;
                        }
                        if(res.status){
                            me.enquiry_is_submit = true;
                            me.enquiry_name = "";
                            me.enquiry_email = "";
                            me.enquiry_phone = "";
                            me.enquiry_note = "";
                        }
                        me.onSubmit = false;
                    },
                    error:function (e) {
                        me.onSubmit = false;
                        bc_handle_error_response(e);
                        if(e.status == 401){
                            $('.bc_single_book_wrap').modal('hide');
                        }
                        if(e.status != 401 && e.responseJSON){
                            me.message.content = e.responseJSON.message ? e.responseJSON.message : 'Can not booking';
                            me.message.type = false;
                        }
                    }
                })
            },
            validateenquiry(){
                if(!this.enquiry_name)
                {
                    this.message.status = false;
                    this.message.content = bc_booking_i18n.name_required;
                    return false;
                }
                if(!this.enquiry_email)
                {
                    this.message.status = false;
                    this.message.content = bc_booking_i18n.email_required;
                    return false;
                }
                return true;
            },
            openStartDate:function(){
                $(this.$refs.hotelStartDate).trigger('click');
            },
            openAnimalStartDate:function(){
                $(this.$refs.animalStartDate).trigger('click');
            },
            initJs:function () {
                //$('.fotorama').fotorama();
            },
            showGallery:function(e,id,gallery)
            {
                if(gallery !== null){
                    var p  = $(e.target).closest('.row');
                    $('#modal_room_'+id).modal().modal('show');
                    p.find('.fotorama').each(function () {
                        $(this).fotorama();
                    });
                }
            }
        }

    });



    $(window).on("load", function () {
        var urlHash = window.location.href.split("#")[1];
        if (urlHash &&  $('.' + urlHash).length ){
            var offset_other = 70
            if(urlHash === "review-list"){
                offset_other = 330;
            }
            $('html,body').animate({
                scrollTop: $('.' + urlHash).offset().top - offset_other
            }, 1000);
        }
        $(document).find('[data-toggle=tooltip]').tooltip();
    });

    $(".bc-button-book-mobile").click(function () {
        //$('.bc_single_book_wrap').modal('show');

    });

    $(".bc_detail_space .g-faq .item .header").click(function () {
        $(this).parent().toggleClass("active");
    });

    $(".btn-show-all").click(function () {
        $(this).parent().find(".d-none").removeClass("d-none");
        $(this).addClass("d-none");
    });

    $(".start_room_sticky").each(function () {
        var $this_list_room = $(this).closest(".hotel_rooms_form");
        $(window).scroll(function() {
            var window_height = $(window).height();
            var windowTop = $(window).scrollTop();
            var stickyTop = $('.start_room_sticky').offset().top + 100 - window_height;
            var stickyBottom =  stickyTop + $this_list_room.height() - 300;
            if (stickyTop < windowTop && windowTop < stickyBottom) {
                $(document).find(".hotel_room_book_status").addClass("sticky").css("width",$this_list_room.width());
                $(document).find(".end_room_sticky").css("min-height",$(document).find(".hotel_room_book_status").height() + 32 + 20);

                setTimeout(function () {
                    $(document).find(".hotel_room_book_status").addClass("active");
                },100);
            } else {
                $(document).find(".hotel_room_book_status").removeClass("sticky").css("width","auto");
                $(document).find(".end_room_sticky").css("min-height","auto");
                $(document).find(".hotel_room_book_status").removeClass("active");
            }
        });
    });

})(jQuery);
