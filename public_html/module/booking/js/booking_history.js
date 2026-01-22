document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('booking-history');
    if (!el) return;

    new Vue({
        el: '#booking-history',
        data: {
            userSearchQuery: '',
            searchResults: [],
            selectedUser: null,
            currentUserId: null,
            currentBookingId: null,
            debounceTimeout: null,
            isSearching: false,
            isResults: false,
            noResults: false,

            hunterSearchQuery: '',
            hunterSearchResults: [],
            hunterIsSearching: false,
            hunterNoResults: false,
            hunterDebounceTimeout: null,
            currentCollectionBookingId: null,
            
            // Слоты для охотников (каждый слот имеет свой поиск)
            hunterSlots: [],
            
            // Переводы для кнопок
            inviteText: el.dataset.inviteText || 'Пригласить',
            invitedText: el.dataset.invitedText || 'Приглашен',
        },
        methods: {
            userPopoverContent(booking) {
                return `<strong>${booking.creator.first_name} ${booking.creator.last_name}</strong><br>
                    Email: ${booking.creator.email}<br>
                    Phone: ${booking.creator.phone}`;
            },
            bookingPopoverContent(booking) {
                return `<strong>Start:</strong> ${booking.start_date}<br>
                    <strong>End:</strong> ${booking.end_date}<br>
                    <strong>Duration:</strong> ${booking.duration_days} days`;
            },
            openUserModal(userId, bookingId) {
                this.currentUserId = userId;
                this.currentBookingId = bookingId;
                const modalEl = document.getElementById('userModal');
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            },
            // Открытие модального окна сбора охотников.
            // При первом открытии запускает сбор (таймер), при повторном - просто открывает модалку.
            openCollectionModal(bookingId, event) {
                const me = this;
                const bookingIdNum = parseInt(bookingId, 10);
                
                // Проверяем, запущен ли уже сбор
                // Ищем таймер в строке таблицы, где находится кнопка
                const bookingRow = event && event.currentTarget ? event.currentTarget.closest('tr') : null;
                const timerInRow = bookingRow ? bookingRow.querySelector('.collection-timer[data-end]') : null;
                
                // Также проверяем статус брони - если статус START_COLLECTION, то сбор уже запущен
                const statusCell = bookingRow ? bookingRow.querySelector('td[class*="START_COLLECTION"]') : null;
                const isCollectionStarted = timerInRow !== null || statusCell !== null;
                
                // Если сбор еще не запущен, запускаем его
                if (!isCollectionStarted) {
                    console.log('Сбор еще не запущен, запускаем таймер для брони:', bookingIdNum);
                    
                    // Вызываем startCollection, но не открываем модалку сразу
                    // После успешного запуска сбора откроем модалку
                    const btn = event && event.currentTarget ? event.currentTarget : null;
                    let originalHtml = null;
                    if (btn) {
                        originalHtml = btn.innerHTML;
                        btn.disabled = true;
                        btn.classList.add('disabled');
                        btn.innerHTML =
                            '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' +
                            '<span> ' + (btn.textContent.trim() || '...') + '</span>';
                    }
                    
                    const restoreButton = function() {
                        if (btn) {
                            btn.disabled = false;
                            btn.classList.remove('disabled');
                            if (originalHtml) {
                                btn.innerHTML = originalHtml;
                            }
                        }
                    };
                    
                    $.ajax({
                        url: `/booking/${bookingIdNum}/start-collection`,
                        type: 'POST',
                        dataType: 'json',
                        data: {},
                        success: function (res) {
                            restoreButton();
                            
                            if (res.status) {
                                // После успешного запуска сбора открываем модалку сразу
                                me.currentCollectionBookingId = bookingIdNum;
                                const modalEl = document.getElementById('collectionModal' + bookingIdNum);
                                if (modalEl) {
                                    const bsModal = new bootstrap.Modal(modalEl);
                                    bsModal.show();
                                    
                                    // Инициализируем слоты после открытия модалки
                                    setTimeout(() => {
                                        console.log('Вызов initializeHunterSlots для брони:', bookingIdNum);
                                        me.initializeHunterSlots(bookingIdNum);
                                    }, 200);
                                }
                                
                                // Обновляем статус в таблице и добавляем таймер, если его еще нет
                                if (bookingRow && res.collection_end_at) {
                                    // Если таймера еще нет, добавляем его
                                    if (!timerInRow) {
                                        const endTimestamp = new Date(res.collection_end_at).getTime();
                                        const timerDiv = document.createElement('div');
                                        timerDiv.className = 'text-muted collection-timer';
                                        timerDiv.setAttribute('data-end', endTimestamp);
                                        timerDiv.textContent = '[0 мин]';
                                        
                                        const statusCell = bookingRow.querySelector('td[class*="status"]');
                                        if (statusCell) {
                                            statusCell.appendChild(timerDiv);
                                        }
                                    }
                                }
                            } else if (res.message) {
                                bookingCoreApp.showAjaxMessage(res);
                            }
                        },
                        error: function (e) {
                            restoreButton();
                            
                            if (e.status === 419) {
                                alert('Сессия истекла, обновите страницу');
                            } else if (e.responseJSON && e.responseJSON.message) {
                                alert('Ошибка: ' + e.responseJSON.message);
                            } else {
                                alert('Произошла ошибка при начале сбора охотников');
                            }
                        }
                    });
                } else {
                    // Сбор уже запущен, просто открываем модалку
                    console.log('Сбор уже запущен, просто открываем модалку для брони:', bookingIdNum);
                    this.currentCollectionBookingId = bookingIdNum;
                    
                    // Инициализируем слоты чуть позже, чтобы модалка успела открыться
                    setTimeout(() => {
                        console.log('Вызов initializeHunterSlots для брони:', bookingIdNum);
                        this.initializeHunterSlots(bookingIdNum);
                    }, 200);
                }
            },
            initializeHunterSlots(bookingId) {
                // Получаем количество охотников из DOM элемента модального окна
                const modal = document.getElementById('collectionModal' + bookingId);
                if (!modal) return;
                
                // Получаем количество из data-атрибута
                const huntersCount = parseInt(modal.dataset.huntersCount || '0', 10);
                
                if (huntersCount > 0) {
                    // Инициализируем массив слотов
                    this.hunterSlots = Array.from({ length: huntersCount }, () => ({
                        query: '',
                        hunter: null,
                        results: [],
                        showResults: false,
                        isSearching: false,
                        noResults: false,
                        debounceTimeout: null,
                        showEmailInput: false,
                        emailMessage: '',
                        emailAddress: '' // Для хранения email, если охотник не выбран
                    }));
                    
                    console.log('Инициализировано слотов:', this.hunterSlots.length);
                    
                    // Загружаем уже приглашенных охотников
                    this.loadInvitedHunters(bookingId);
                    
                    // Проверяем состояние кнопки "Завершить сбор" при инициализации
                    this.$nextTick(() => {
                        this.checkFinishCollectionButton(bookingId);
                    });
                } else {
                    console.log('Количество охотников = 0, слоты не созданы');
                    this.hunterSlots = [];
                }
            },
            loadInvitedHunters(bookingId) {
                console.log('Загрузка приглашенных охотников для брони:', bookingId);
                fetch(`/booking/${bookingId}/invited-hunters`)
                    .then(res => res.json())
                    .then(data => {
                        console.log('Ответ от сервера:', data);
                        // sendSuccess возвращает данные напрямую, не в data.data
                        const hunters = data.hunters || [];
                        console.log('Найдено приглашенных охотников:', hunters.length, hunters);
                        
                        if (data.status && hunters.length > 0) {
                            console.log('Обработка', hunters.length, 'приглашенных охотников');
                            
                            // Создаем новый массив слотов с обновленными данными для лучшей реактивности
                            const updatedSlots = this.hunterSlots.map((slot, index) => {
                                if (index < hunters.length) {
                                    const hunter = hunters[index];
                                    
                                    // Инициализируем флаги для охотника
                                    if (typeof hunter.showEmailInput === 'undefined') {
                                        hunter.showEmailInput = false;
                                    }
                                    if (typeof hunter.emailMessage === 'undefined') {
                                        hunter.emailMessage = '';
                                    }
                                    
                                    // Формируем текст для поля ввода
                                    // Для внешних охотников (без системы) используем email
                                    let queryText = '';
                                    if (hunter.is_external) {
                                        queryText = hunter.email || '';
                                    } else {
                                        queryText = hunter.user_name || (hunter.first_name + ' ' + hunter.last_name).trim() || '';
                                    }
                                    
                                    console.log(`Слот ${index} заполнен охотником:`, {
                                        id: hunter.id,
                                        name: hunter.first_name + ' ' + hunter.last_name,
                                        user_name: hunter.user_name,
                                        email: hunter.email,
                                        is_external: hunter.is_external,
                                        query: queryText,
                                        invited: hunter.invited,
                                        status: hunter.invitation_status
                                    });
                                    
                                    // Возвращаем обновленный слот
                                    return {
                                        ...slot,
                                        hunter: hunter,
                                        query: queryText
                                    };
                                }
                                return slot;
                            });
                            
                            // Заменяем весь массив для реактивности Vue
                            this.$set(this, 'hunterSlots', updatedSlots);
                            
                            console.log('Массив hunterSlots обновлен:', this.hunterSlots.length, 'слотов');
                            console.log('Проверка данных в слотах:', this.hunterSlots.map((s, i) => ({
                                index: i,
                                query: s.query,
                                hasHunter: !!s.hunter,
                                hunterName: s.hunter ? (s.hunter.first_name + ' ' + s.hunter.last_name) : null,
                                hunterId: s.hunter ? s.hunter.id : null
                            })));
                            
                            // Принудительно обновляем Vue для отображения
                            this.$nextTick(() => {
                                this.$forceUpdate();
                                console.log('Vue принудительно обновлен');
                                // Проверяем состояние кнопки "Завершить сбор" после загрузки охотников
                                this.checkFinishCollectionButton(bookingId);
                            });
                        } else {
                            console.log('Нет данных о приглашенных охотниках. Статус:', data.status, 'Hunters count:', hunters.length, 'Data:', data);
                            // Даже если нет охотников, проверяем состояние кнопки
                            this.$nextTick(() => {
                                this.checkFinishCollectionButton(bookingId);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Ошибка при загрузке приглашенных охотников:', error);
                    });
            },
            checkFinishCollectionButton(bookingId) {
                // Проверяем, должен ли быть отключен кнопка "Завершить сбор"
                // Если таймер закончен и не хватает охотников - блокируем кнопку
                const finishBtn = document.querySelector('.btn-finish-collection[data-booking-id="' + bookingId + '"]');
                const modal = document.getElementById('collectionModal' + bookingId);
                if (!finishBtn || !modal) return;
                
                // Проверяем, закончен ли таймер
                const timerEl = document.querySelector('.collection-timer[data-booking-id="' + bookingId + '"]');
                if (!timerEl) {
                    // Если таймера нет, разрешаем кнопку
                    finishBtn.disabled = false;
                    finishBtn.classList.remove('disabled');
                    finishBtn.title = '';
                    return;
                }
                
                const end = parseInt(timerEl.dataset.end, 10);
                if (!end) {
                    finishBtn.disabled = false;
                    finishBtn.classList.remove('disabled');
                    finishBtn.title = '';
                    return;
                }
                
                const now = Date.now();
                const diffMs = end - now;
                const isTimerFinished = diffMs <= 0;
                
                if (isTimerFinished) {
                    // Таймер закончен - проверяем количество охотников
                    const requiredHunters = parseInt(modal.dataset.huntersCount || '0', 10);
                    
                    // Считаем приглашенных охотников (со статусом не declined)
                    // Учитываем как охотников из системы, так и внешних (по email)
                    let invitedCount = 0;
                    if (this.hunterSlots && this.hunterSlots.length > 0) {
                        invitedCount = this.hunterSlots.filter(slot => 
                            slot.hunter && 
                            slot.hunter.invited && 
                            slot.hunter.invitation_status !== 'declined'
                        ).length;
                    }
                    
                    // Если не хватает охотников - блокируем кнопку
                    if (invitedCount < requiredHunters) {
                        finishBtn.disabled = true;
                        finishBtn.classList.add('disabled');
                        finishBtn.title = 'Таймер закончен, но не все охотники приглашены. Необходимо пригласить ' + requiredHunters + ' охотников.';
                    } else {
                        // Если достаточно охотников - разрешаем кнопку
                        finishBtn.disabled = false;
                        finishBtn.classList.remove('disabled');
                        finishBtn.title = '';
                    }
                } else {
                    // Таймер еще идет - разрешаем кнопку
                    finishBtn.disabled = false;
                    finishBtn.classList.remove('disabled');
                    finishBtn.title = '';
                }
            },
            searchHunterDebounced() {
                if (this.hunterSearchQuery.length < 4) {
                    this.hunterSearchResults = [];
                    this.hunterNoResults = false;
                    return;
                }
                clearTimeout(this.hunterDebounceTimeout);

                this.hunterDebounceTimeout = setTimeout(() => {
                    this.searchHunters();
                }, 300);
            },
            searchHunters() {
                if (this.hunterSearchQuery.length < 4) {
                    this.hunterSearchResults = [];
                    this.hunterNoResults = false;
                    return;
                }

                this.hunterIsSearching = true;
                this.hunterNoResults = false;

                const bookingId = this.currentCollectionBookingId || '';

                fetch(`/user/search-hunters?query=${encodeURIComponent(this.hunterSearchQuery)}&booking_id=${bookingId}`)
                    .then(res => res.json())
                    .then(users => {
                        // Инициализируем реактивные флаги для каждой записи
                        users.forEach(u => {
                            if (typeof u.invited === 'undefined') {
                                u.invited = false;
                            }
                            if (typeof u.invitation_status === 'undefined') {
                                u.invitation_status = null;
                            }
                            if (typeof u.showEmailInput === 'undefined') {
                                u.showEmailInput = false;
                            }
                            if (typeof u.emailMessage === 'undefined') {
                                u.emailMessage = '';
                            }
                        });
                        this.hunterSearchResults = users;
                        this.hunterNoResults = users.length === 0;
                    })
                    .finally(() => {
                        this.hunterIsSearching = false;
                    });
            },
            inviteHunter(hunter, bookingId, event) {
                if (!hunter || !hunter.id) return;

                // Останавливаем распространение события
                if (event) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                // КРИТИЧЕСКИ ВАЖНО: Гарантируем, что поле ввода сообщения закрыто ДО выполнения запроса
                // Используем $set для явной реактивности Vue
                this.$set(hunter, 'showEmailInput', false);
                // Дополнительно устанавливаем напрямую для немедленного эффекта
                hunter.showEmailInput = false;

                const bookingIdNum = parseInt(bookingId, 10);
                if (!bookingIdNum) return;

                // Лёгкий спиннер на кнопке
                const btn = event && event.currentTarget ? event.currentTarget : null;
                let originalHtml = null;
                if (btn) {
                    originalHtml = btn.innerHTML;
                    btn.disabled = true;
                    btn.classList.add('disabled');
                    btn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' +
                        '<span> ' + (btn.textContent.trim() || '...') + '</span>';
                }

                const restoreButton = () => {
                    if (btn) {
                        btn.disabled = false;
                        btn.classList.remove('disabled');
                        if (originalHtml) {
                            btn.innerHTML = originalHtml;
                        }
                    }
                };

                $.ajax({
                    url: `/booking/${bookingIdNum}/invite-hunter`,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        hunter_id: hunter.id,
                        _token: $('meta[name="csrf-token"]').attr('content') || ''
                    },
                    success: (res) => {
                        restoreButton();
                        if (res.status) {
                            // Находим объект в массиве и обновляем его напрямую
                            const index = this.hunterSearchResults.findIndex(h => h.id === hunter.id);
                            if (index !== -1) {
                                // Обновляем объект в массиве через $set для реактивности
                                this.$set(this.hunterSearchResults[index], 'invited', true);
                                this.$set(this.hunterSearchResults[index], 'invitation_status', 'pending');
                                this.$set(this.hunterSearchResults[index], 'showEmailInput', false);
                            }
                            
                            // Обновляем также в слотах охотников
                            this.hunterSlots.forEach(slot => {
                                if (slot.hunter && slot.hunter.id === hunter.id) {
                                    this.$set(slot.hunter, 'invited', true);
                                    this.$set(slot.hunter, 'invitation_status', 'pending');
                                }
                            });
                            
                            // Также обновляем переданный объект hunter
                            Object.assign(hunter, {
                                invited: true,
                                invitation_status: 'pending',
                                showEmailInput: false
                            });
                            
                            // Принудительно обновляем Vue для немедленного отображения
                            this.$forceUpdate();
                            
                            // Проверяем состояние кнопки "Завершить сбор" после приглашения охотника
                            this.$nextTick(() => {
                                this.checkFinishCollectionButton(bookingId);
                            });
                            
                            if (typeof bookingCoreApp !== 'undefined' && bookingCoreApp.showAjaxMessage) {
                                bookingCoreApp.showAjaxMessage(res);
                            } else if (res.message) {
                                alert(res.message);
                            }
                        } else if (res.message) {
                            alert(res.message);
                        }
                    },
                    error: function (e) {
                        restoreButton();
                        if (e.status === 419) {
                            alert('Сессия истекла, обновите страницу');
                        } else if (e.responseJSON && e.responseJSON.message) {
                            alert('Ошибка: ' + e.responseJSON.message);
                        } else {
                            alert('Произошла ошибка при сохранении приглашения охотника');
                        }
                    }
                });
            },
            searchHunterForSlot(slotIndex, bookingId) {
                const slot = this.hunterSlots[slotIndex];
                if (!slot) return;
                
                if (slot.query.length < 3) {
                    slot.results = [];
                    slot.showResults = false;
                    slot.noResults = false;
                    // Если поле очищено или запрос слишком короткий, очищаем выбранного охотника
                    if (slot.query.length === 0 || slot.query.trim() === '') {
                        slot.hunter = null;
                        // И если при этом была открыта форма отправки письма — закрываем её
                        if (slot.showEmailInput) {
                            slot.showEmailInput = false;
                            slot.emailAddress = '';
                            slot.emailMessage = '';
                        }
                    }
                    return;
                }
                
                clearTimeout(slot.debounceTimeout);
                slot.debounceTimeout = setTimeout(() => {
                    slot.isSearching = true;
                    slot.showResults = true;
                    slot.noResults = false;
                    
                    fetch(`/user/search-hunters?query=${encodeURIComponent(slot.query)}&booking_id=${bookingId}`)
                        .then(res => res.json())
                        .then(users => {
                            users.forEach(u => {
                                if (typeof u.invited === 'undefined') {
                                    u.invited = false;
                                }
                                if (typeof u.invitation_status === 'undefined') {
                                    u.invitation_status = null;
                                }
                            });
                            slot.results = users;
                            // Если результатов нет и запрос длиной >= 3, показываем сообщение
                            slot.noResults = users.length === 0 && slot.query.length >= 3;
                        })
                        .finally(() => {
                            slot.isSearching = false;
                        });
                }, 300);
            },
            handleHunterInputChange(slotIndex) {
                const slot = this.hunterSlots[slotIndex];
                if (!slot) return;

                // Если поле полностью очищено кнопкой "стереть" или вручную
                if (!slot.query || slot.query.trim() === '') {
                    this.clearHunterSlot(slotIndex);
                    return;
                }

                // Если есть выбранный охотник, но текст в поле уже не совпадает с его именем — сбрасываем выбор
                if (slot.hunter) {
                    const hunterName = slot.hunter.user_name || (slot.hunter.first_name + ' ' + slot.hunter.last_name).trim();
                    if (slot.query.trim() !== hunterName.trim()) {
                        slot.hunter = null;
                    }
                }
            },
            selectHunterForSlot(slotIndex, hunter, bookingId) {
                const slot = this.hunterSlots[slotIndex];
                if (!slot) return;
                
                // Инициализируем флаги для охотника
                if (typeof hunter.showEmailInput === 'undefined') {
                    hunter.showEmailInput = false;
                }
                if (typeof hunter.emailMessage === 'undefined') {
                    hunter.emailMessage = '';
                }
                
                slot.hunter = hunter;
                slot.query = hunter.user_name || hunter.first_name + ' ' + hunter.last_name;
                slot.showResults = false;
                slot.results = [];
                slot.noResults = false;
            },
            inviteHunterForSlot(slotIndex, bookingId, event) {
                const slot = this.hunterSlots[slotIndex];
                if (!slot || !slot.hunter || !slot.hunter.id) return;

                // Мгновенно помечаем охотника как приглашённого в этом слоте,
                // чтобы сразу заблокировать строку поиска, не дожидаясь перезагрузки
                this.$set(slot.hunter, 'invited', true);
                this.$set(slot.hunter, 'invitation_status', 'pending');
                this.$forceUpdate();

                // Для слота не передаём event, чтобы не ломать разметку кнопки через прямую манипуляцию innerHTML
                this.inviteHunter(slot.hunter, bookingId, null);
            },
            toggleEmailInputForSlot(slotIndex) {
                const slot = this.hunterSlots[slotIndex];
                if (!slot) return;
                
                slot.showEmailInput = !slot.showEmailInput;
                if (slot.showEmailInput) {
                    // При открытии поля ввода почты закрываем выпадающее окно результатов поиска
                    slot.showResults = false;
                    slot.results = [];
                    slot.noResults = false;
                    
                    if (slot.hunter && !slot.emailAddress) {
                        // Если охотник выбран, используем его email по умолчанию
                        slot.emailAddress = slot.hunter.email || '';
                    } else if (!slot.hunter && !slot.emailAddress && slot.query) {
                        // Если охотник не выбран, но в поиске введен email — подставляем его в поле,
                        // только если это похоже на корректный email
                        const queryTrim = slot.query.trim();
                        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (emailPattern.test(queryTrim)) {
                            slot.emailAddress = queryTrim;
                        }
                    }
                } else {
                    // При закрытии очищаем сообщение
                    slot.emailMessage = '';
                }
            },
            handleEmailAddressInput(slotIndex) {
                const slot = this.hunterSlots[slotIndex];
                if (!slot) return;

                // Если email стёрт и охотник не выбран — закрываем форму отправки письма
                if (!slot.hunter && (!slot.emailAddress || slot.emailAddress.trim() === '')) {
                    slot.showEmailInput = false;
                    slot.emailMessage = '';
                }
            },
            sendEmailForSlot(slotIndex, bookingId, event) {
                const slot = this.hunterSlots[slotIndex];
                if (!slot) return;
                
                // Определяем кому отправлять
                let hunterId = null;
                let emailAddress = slot.emailAddress || '';
                
                if (slot.hunter && slot.hunter.id) {
                    // Если охотник выбран, используем его ID и email
                    hunterId = slot.hunter.id;
                    emailAddress = slot.hunter.email || emailAddress;
                } else if (!emailAddress) {
                    // Если нет охотника и нет email, показываем ошибку
                    alert('Необходимо выбрать охотника или указать email адрес');
                    return;
                }
                
                const message = slot.emailMessage || '';
                if (!message.trim()) {
                    alert('Введите текст сообщения');
                    return;
                }
                
                if (hunterId) {
                    // Если охотник выбран, используем существующий метод sendHunterEmail
                    const hunter = slot.hunter;
                    hunter.emailMessage = message;
                    this.sendHunterEmail(hunter, bookingId, event);
                } else if (emailAddress) {
                    // Если нет охотника, но есть email, отправляем напрямую
                    // Пока используем существующий метод emailHunter, который требует hunter_id
                    // В будущем можно создать отдельный метод для отправки по email
                    alert('Для отправки email необходимо выбрать охотника из системы. Если охотник не в системе, его нужно сначала добавить.');
                    return;
                }
                
                // Закрываем поле ввода после успешной отправки (для hunterId будет закрыто в sendHunterEmail)
                if (!hunterId) {
                    slot.showEmailInput = false;
                    slot.emailMessage = '';
                    slot.emailAddress = '';
                }
            },
            inviteByEmailForSlot(slotIndex, bookingId, event) {
                const slot = this.hunterSlots[slotIndex];
                if (!slot) return;
                
                const query = slot.query ? slot.query.trim() : '';
                if (!query) {
                    alert('Введите email адрес охотника');
                    return;
                }
                
                // Проверяем, является ли введенный текст email-адресом
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(query)) {
                    alert('Введите корректный email адрес');
                    return;
                }
                
                const bookingIdNum = parseInt(bookingId, 10);
                if (!bookingIdNum) return;
                
                const btn = event && event.currentTarget ? event.currentTarget : null;
                let originalHtml = null;
                if (btn) {
                    originalHtml = btn.innerHTML;
                    btn.disabled = true;
                    btn.classList.add('disabled');
                    btn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' +
                        '<span> ' + (btn.textContent.trim() || '...') + '</span>';
                }
                
                const restoreButton = () => {
                    if (btn) {
                        btn.disabled = false;
                        btn.classList.remove('disabled');
                        if (originalHtml) {
                            btn.innerHTML = originalHtml;
                        }
                    }
                };
                
                // Отправляем приглашение по email (даже если пользователя нет в системе)
                $.ajax({
                    url: `/booking/${bookingIdNum}/invite-hunter-by-email`,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        email: query,
                        _token: $('meta[name="csrf-token"]').attr('content') || ''
                    },
                    success: (res) => {
                        restoreButton();
                        if (res.status) {
                            // Очищаем поле после успешной отправки
                            slot.query = '';
                            slot.noResults = false;
                        } else if (res.message) {
                            if (typeof bookingCoreApp !== 'undefined' && bookingCoreApp.showAjaxMessage) {
                                bookingCoreApp.showAjaxMessage(res);
                            } else {
                                alert(res.message);
                            }
                        }
                    },
                    error: (e) => {
                        restoreButton();
                        console.error('Ошибка при отправке приглашения по email:', e);
                        if (e.status === 419) {
                            alert('Сессия истекла, обновите страницу');
                        } else if (e.responseJSON && e.responseJSON.message) {
                            alert('Ошибка: ' + e.responseJSON.message);
                        } else if (e.responseJSON && e.responseJSON.error) {
                            alert('Ошибка: ' + e.responseJSON.error);
                        } else {
                            alert('Произошла ошибка при отправке приглашения. Проверьте консоль для деталей.');
                        }
                    }
                });
            },
            clearHunterSlot(slotIndex) {
                const slot = this.hunterSlots[slotIndex];
                if (!slot) return;
                
                slot.query = '';
                slot.hunter = null;
                slot.results = [];
                slot.showResults = false;
                slot.noResults = false;
                slot.showEmailInput = false;
                slot.emailMessage = '';
                slot.emailAddress = '';
            },
            sendHunterEmail(hunter, bookingId, event) {
                if (!hunter || !hunter.id) return;

                const bookingIdNum = parseInt(bookingId, 10);
                if (!bookingIdNum) return;

                const btn = event && event.currentTarget ? event.currentTarget : null;
                let originalHtml = null;
                if (btn) {
                    originalHtml = btn.innerHTML;
                    btn.disabled = true;
                    btn.classList.add('disabled');
                    btn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' +
                        '<span> ' + (btn.textContent.trim() || '...') + '</span>';
                }

                const restoreButton = () => {
                    if (btn) {
                        btn.disabled = false;
                        btn.classList.remove('disabled');
                        if (originalHtml) {
                            btn.innerHTML = originalHtml;
                        }
                    }
                };

                $.ajax({
                    url: `/booking/${bookingIdNum}/email-hunter`,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        hunter_id: hunter.id,
                        message: hunter.emailMessage || '',
                        _token: $('meta[name="csrf-token"]').attr('content') || ''
                    },
                    success: function (res) {
                        restoreButton();
                        if (res.status) {
                            if (typeof bookingCoreApp !== 'undefined' && bookingCoreApp.showAjaxMessage) {
                                bookingCoreApp.showAjaxMessage(res);
                            } else if (res.message) {
                                alert(res.message);
                            }
                            // можно скрыть поле ввода после успешной отправки
                            hunter.showEmailInput = false;
                        } else if (res.message) {
                            alert(res.message);
                        }
                    },
                    error: function (e) {
                        restoreButton();
                        if (e.status === 419) {
                            alert('Сессия истекла, обновите страницу');
                        } else if (e.responseJSON && e.responseJSON.message) {
                            alert('Ошибка: ' + e.responseJSON.message);
                        } else {
                            alert('Произошла ошибка при отправке письма охотнику');
                        }
                    }
                });
            },
            searchUserDebounced() {
                if (this.userSearchQuery.length < 2) return;

                clearTimeout(this.debounceTimeout);

                this.debounceTimeout = setTimeout(() => {
                    this.searchUsers();
                }, 300);
            },
            searchUsers() {
                if (this.userSearchQuery.length < 2) {
                    this.searchResults = [];
                    this.noResults = false;
                    return;
                }

                this.isSearching = true;
                this.noResults = false;

                fetch(`/user/search?query=${encodeURIComponent(this.userSearchQuery)}`)
                    .then(res => res.json())
                    .then(users => {
                        this.searchResults = users;
                        this.isResults = users.length > 0;
                        this.noResults = users.length === 0;
                    })
                    .finally(() => {
                        this.isSearching = false;
                    });
            },
            selectUser(user) {
                this.selectedUser = user;
                this.userSearchQuery = user.user_name;
                // this.searchResults = [];
            },
            saveUserChange() {
                var me = this;
                $.ajax({
                    url: `/booking/${this.currentBookingId}/change-user`,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        user_id: this.selectedUser.id,
                    },
                    success: function (res) {
                        if (res.status) {
                            bookingCoreApp.showAjaxMessage(res);
                            window.location.reload();
                        }
                    },
                    error: function (e) {
                        if (e.status === 419) {
                            alert('Сессия истекла, обновите страницу');
                        } else if (e.responseJSON && e.responseJSON.message) {
                            alert('Ошибка: ' + e.responseJSON.message);
                        } else {
                            alert('Произошла ошибка при сохранении пользователя');
                        }
                    }
                });
            },
            confirmBooking($bookingId) {
                $.ajax({
                    url: `/booking/${$bookingId}/confirm`,
                    type: 'POST',
                    dataType: 'json',
                    data: {},
                    success: (res) => {
                        if (res.status) {
                            bookingCoreApp.showAjaxMessage(res);
                            window.location.reload();
                        }
                    },
                    error: (e) => {
                        alert('Ошибка подтверждения брони');
                    }
                });
            },
            completeBooking(event, bookingId) {
                var me = this;
                var bookingIdNum = parseInt(bookingId, 10);

                var btn = event && event.currentTarget ? event.currentTarget : null;
                if (!btn) {
                    return;
                }

                if (!btn.dataset.originalHtml) {
                    btn.dataset.originalHtml = btn.innerHTML;
                }

                btn.disabled = true;
                btn.classList.add('disabled');
                btn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' +
                    '<span> ' + (btn.textContent.trim() || '...') + '</span>';

                var restoreButton = function () {
                    btn.disabled = false;
                    btn.classList.remove('disabled');
                    if (btn.dataset.originalHtml) {
                        btn.innerHTML = btn.dataset.originalHtml;
                    }
                };

                $.ajax({
                    url: `/booking/${bookingIdNum}/complete`,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content') || ''
                    },
                    success: function (res) {
                        restoreButton();

                        if (res.status) {
                            bookingCoreApp.showAjaxMessage(res);
                            setTimeout(function () {
                                window.location.reload();
                            }, 500);
                        } else if (res.message) {
                            bookingCoreApp.showAjaxMessage(res);
                        }
                    },
                    error: function (e) {
                        restoreButton();

                        if (e.status === 419) {
                            alert('Сессия истекла, обновите страницу');
                        } else if (e.responseJSON && e.responseJSON.message) {
                            alert('Ошибка: ' + e.responseJSON.message);
                        } else {
                            alert('Произошла ошибка при завершении бронирования');
                        }
                    }
                });
            },
            startCollection(event, bookingId) {
                var me = this;
                var bookingIdNum = parseInt(bookingId, 10);

                var btn = event && event.currentTarget ? event.currentTarget : null;
                if (!btn) {
                    return;
                }

                if (!btn.dataset.originalHtml) {
                    btn.dataset.originalHtml = btn.innerHTML;
                }

                btn.disabled = true;
                btn.classList.add('disabled');
                btn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' +
                    '<span> ' + (btn.textContent.trim() || '...') + '</span>';

                var restoreButton = function () {
                    btn.disabled = false;
                    btn.classList.remove('disabled');
                    if (btn.dataset.originalHtml) {
                        btn.innerHTML = btn.dataset.originalHtml;
                    }
                };

                $.ajax({
                    url: `/booking/${bookingIdNum}/start-collection`,
                    type: 'POST',
                    dataType: 'json',
                    data: {},
                    success: function (res) {
                        restoreButton();

                        if (res.status) {
                            var modal = bootstrap.Modal.getInstance(document.getElementById('collectionModal' + bookingIdNum));
                            if (modal) {
                                modal.hide();
                            }
                            bookingCoreApp.showAjaxMessage(res);
                            setTimeout(function () {
                                window.location.reload();
                            }, 500);
                        } else if (res.message) {
                            bookingCoreApp.showAjaxMessage(res);
                        }
                    },
                    error: function (e) {
                        restoreButton();

                        if (e.status === 419) {
                            alert('Сессия истекла, обновите страницу');
                        } else if (e.responseJSON && e.responseJSON.message) {
                            alert('Ошибка: ' + e.responseJSON.message);
                        } else {
                            alert('Произошла ошибка при начале сбора охотников');
                        }
                    }
                });
            },
            cancelCollection(event, bookingId) {
                var me = this;
                var bookingIdNum = parseInt(bookingId, 10);

                var btn = event && event.currentTarget ? event.currentTarget : null;
                if (!btn) {
                    return;
                }

                if (!btn.dataset.originalHtml) {
                    btn.dataset.originalHtml = btn.innerHTML;
                }

                btn.disabled = true;
                btn.classList.add('disabled');
                btn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' +
                    '<span> ' + (btn.textContent.trim() || '...') + '</span>';

                var restoreButton = function () {
                    btn.disabled = false;
                    btn.classList.remove('disabled');
                    if (btn.dataset.originalHtml) {
                        btn.innerHTML = btn.dataset.originalHtml;
                    }
                };

                $.ajax({
                    url: `/booking/${bookingIdNum}/cancel-collection`,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content') || ''
                    },
                    success: function (res) {
                        restoreButton();

                        if (res.status) {
                            var modal = bootstrap.Modal.getInstance(document.getElementById('collectionModal' + bookingIdNum));
                            if (modal) {
                                modal.hide();
                            }

                            // Очищаем локальное состояние слотов и поиска
                            me.hunterSlots = [];
                            me.hunterSearchQuery = '';
                            me.hunterSearchResults = [];
                            me.hunterNoResults = false;

                            if (typeof bookingCoreApp !== 'undefined' && bookingCoreApp.showAjaxMessage) {
                                bookingCoreApp.showAjaxMessage(res);
                            } else if (res.message) {
                                alert(res.message);
                            }

                            setTimeout(function () {
                                window.location.reload();
                            }, 500);
                        } else if (res.message) {
                            if (typeof bookingCoreApp !== 'undefined' && bookingCoreApp.showAjaxMessage) {
                                bookingCoreApp.showAjaxMessage(res);
                            } else {
                                alert(res.message);
                            }
                        }
                    },
                    error: function (e) {
                        restoreButton();

                        if (e.status === 419) {
                            alert('Сессия истекла, обновите страницу');
                        } else if (e.responseJSON && e.responseJSON.message) {
                            alert('Ошибка: ' + e.responseJSON.message);
                        } else {
                            alert('Произошла ошибка при отмене сбора охотников');
                        }
                    }
                });
            },
            finishCollection(event, bookingId) {
                var me = this;
                var bookingIdNum = parseInt(bookingId, 10);

                var btn = event && event.currentTarget ? event.currentTarget : null;
                if (!btn) {
                    return;
                }

                if (!btn.dataset.originalHtml) {
                    btn.dataset.originalHtml = btn.innerHTML;
                }

                btn.disabled = true;
                btn.classList.add('disabled');
                btn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' +
                    '<span> ' + (btn.textContent.trim() || '...') + '</span>';

                var restoreButton = function () {
                    btn.disabled = false;
                    btn.classList.remove('disabled');
                    if (btn.dataset.originalHtml) {
                        btn.innerHTML = btn.dataset.originalHtml;
                    }
                };

                $.ajax({
                    url: `/booking/${bookingIdNum}/finish-collection`,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content') || ''
                    },
                    success: function (res) {
                        restoreButton();

                        if (res.status) {
                            var modal = bootstrap.Modal.getInstance(document.getElementById('collectionModal' + bookingIdNum));
                            if (modal) {
                                modal.hide();
                            }
                            bookingCoreApp.showAjaxMessage(res);
                            setTimeout(function () {
                                window.location.reload();
                            }, 500);
                        } else if (res.message) {
                            bookingCoreApp.showAjaxMessage(res);
                        }
                    },
                    error: function (e) {
                        restoreButton();

                        if (e.status === 419) {
                            alert('Сессия истекла, обновите страницу');
                        } else if (e.responseJSON && e.responseJSON.message) {
                            bookingCoreApp.showAjaxMessage(e.responseJSON);
                        } else {
                            alert('Произошла ошибка при завершении сбора охотников');
                        }
                    }
                });
            },
            cancelBooking(event, bookingId) {
                var me = this;
                var bookingIdNum = parseInt(bookingId, 10);

                var btn = event && event.currentTarget ? event.currentTarget : null;
                if (!btn) {
                    return;
                }

                if (!btn.dataset.originalHtml) {
                    btn.dataset.originalHtml = btn.innerHTML;
                }

                btn.disabled = true;
                btn.classList.add('disabled');
                btn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' +
                    '<span> ' + (btn.textContent.trim() || '...') + '</span>';

                var restoreButton = function () {
                    btn.disabled = false;
                    btn.classList.remove('disabled');
                    if (btn.dataset.originalHtml) {
                        btn.innerHTML = btn.dataset.originalHtml;
                    }
                };

                $.ajax({
                    url: `/booking/${bookingIdNum}/cancel`,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content') || ''
                    },
                    success: function (res) {
                        restoreButton();

                        if (res.status) {
                            var modal = bootstrap.Modal.getInstance(document.getElementById('cancelBookingModal' + bookingIdNum));
                            if (modal) {
                                modal.hide();
                            }
                            bookingCoreApp.showAjaxMessage(res);
                            setTimeout(function () {
                                window.location.reload();
                            }, 500);
                        } else if (res.message) {
                            bookingCoreApp.showAjaxMessage(res);
                        }
                    },
                    error: function (e) {
                        restoreButton();

                        if (e.status === 419) {
                            alert('Сессия истекла, обновите страницу');
                        } else if (e.responseJSON && e.responseJSON.message) {
                            alert('Ошибка: ' + e.responseJSON.message);
                        } else {
                            alert('Произошла ошибка при отмене бронирования');
                        }
                    }
                });
            }
        },

        mounted() {
            document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
                new bootstrap.Popover(el);
            });

            // Добавляем обработчики событий для модальных окон сбора охотников
            const me = this;
            // Используем делегирование событий для всех модальных окон
            document.addEventListener('shown.bs.modal', function(event) {
                const modalEl = event.target;
                if (modalEl && modalEl.id && modalEl.id.startsWith('collectionModal')) {
                    const bookingId = modalEl.dataset.bookingId;
                    console.log('Модальное окно collectionModal открыто, bookingId:', bookingId);
                    if (bookingId) {
                        // Загружаем данные после открытия модального окна
                        setTimeout(() => {
                            me.initializeHunterSlots(parseInt(bookingId, 10));
                        }, 50);
                    }
                }
            });

            window.LaravelEcho.channel('booking')
                .listen('.booking.created', (e) => {
                    location.reload();
                });

            // Подписка на событие приглашения охотника
            const el = document.getElementById('booking-history');
            const userId = el ? el.dataset.userId : null;
            
            if (userId && window.LaravelEcho) {
                console.log('[HunterInvitation] Подписка на канал для пользователя:', userId);
                
                try {
                    // Пытаемся подписаться на приватный канал
                    const channel = window.LaravelEcho.private(`user-channel-${userId}`);
                    
                    channel.listen('.hunter.invited', (e) => {
                        console.log('[HunterInvitation] Получено событие приглашения:', e);
                        // Обновляем страницу истории бронирований
                        if (window.location.pathname.includes('booking-history')) {
                            console.log('[HunterInvitation] Обновление страницы...');
                            setTimeout(() => {
                                location.reload();
                            }, 500);
                        }
                    });
                    
                    channel.subscribed(() => {
                        console.log('[HunterInvitation] ✅ Успешно подписан на канал user-channel-' + userId);
                    });
                    
                    channel.error((error) => {
                        console.error('[HunterInvitation] ❌ Ошибка подписки на канал:', error);
                    });
                } catch (e) {
                    console.error('[HunterInvitation] ❌ Исключение при подписке:', e);
                }
            } else {
                console.warn('[HunterInvitation] ⚠️ Не удалось подписаться. userId:', userId, 'LaravelEcho:', !!window.LaravelEcho, 'Element:', !!el);
            }

            // Очистка полей поиска охотников при закрытии модалок сбора
            document.querySelectorAll('.modal[id^="collectionModal"]').forEach((modalEl) => {
                // Проверяем, не добавлен ли уже обработчик
                if (modalEl.dataset.handlerCleared) return;
                modalEl.dataset.handlerCleared = 'true';
                
                modalEl.addEventListener('hidden.bs.modal', () => {
                    me.hunterSearchQuery = '';
                    me.hunterSearchResults = [];
                    me.hunterNoResults = false;
                });
            });

            const updateCollectionTimers = () => {
                // Поддерживаем оба варианта: data-end (новый) и data-start (старый для обратной совместимости)
                const nodesWithEnd = document.querySelectorAll('.collection-timer[data-end]');
                const nodesWithStart = document.querySelectorAll('.collection-timer[data-start]');
                const now = Date.now();

                // Показываем реальный оставшийся таймер в формате "ММ мин SS сек"
                nodesWithEnd.forEach(el => {
                    const end = parseInt(el.dataset.end, 10);
                    if (!end) return;

                    let diffMs = end - now;

                    if (diffMs <= 0) {
                        el.textContent = '[0 мин 00 сек]';
                        // Таймер закончился — разрешаем кнопку продления сбора (если есть)
                        const bookingId = el.dataset.bookingId;
                        if (bookingId) {
                            const extendBtn = document.querySelector('.btn-extend-collection[data-booking-id="' + bookingId + '"]');
                            if (extendBtn) {
                                extendBtn.disabled = false;
                                extendBtn.classList.remove('disabled');
                            }
                            
                            // Проверяем, достаточно ли охотников приглашено
                            // Если таймер закончен, но не все охотники собраны - блокируем кнопку "Завершить сбор"
                            const finishBtn = document.querySelector('.btn-finish-collection[data-booking-id="' + bookingId + '"]');
                            if (finishBtn) {
                                // Получаем Vue компонент для доступа к hunterSlots
                                const vueEl = document.getElementById('booking-history');
                                const modal = document.getElementById('collectionModal' + bookingId);
                                
                                if (vueEl && vueEl.__vue__ && modal) {
                                    const vueComponent = vueEl.__vue__;
                                    const requiredHunters = parseInt(modal.dataset.huntersCount || '0', 10);
                                    
                                    // Считаем приглашенных охотников (со статусом не declined)
                                    let invitedCount = 0;
                                    if (vueComponent.hunterSlots && vueComponent.hunterSlots.length > 0) {
                                        invitedCount = vueComponent.hunterSlots.filter(slot => 
                                            slot.hunter && 
                                            slot.hunter.invited && 
                                            slot.hunter.invitation_status !== 'declined'
                                        ).length;
                                    }
                                    
                                    // Если не хватает охотников - блокируем кнопку
                                    if (invitedCount < requiredHunters) {
                                        finishBtn.disabled = true;
                                        finishBtn.classList.add('disabled');
                                        finishBtn.title = 'Таймер закончен, но не все охотники приглашены. Необходимо пригласить ' + requiredHunters + ' охотников.';
                                    } else {
                                        // Если достаточно охотников - разрешаем кнопку
                                        finishBtn.disabled = false;
                                        finishBtn.classList.remove('disabled');
                                        finishBtn.title = '';
                                    }
                                }
                            }
                        }
                        return;
                    }

                    const totalSeconds = Math.floor(diffMs / 1000);
                    const minutes = Math.floor(totalSeconds / 60);
                    const seconds = totalSeconds % 60;

                    el.textContent = '[' + minutes + ' мин ' + String(seconds).padStart(2, '0') + ' сек]';

                    // Пока таймер тикает — кнопка продления должна быть неактивна
                    const bookingId = el.dataset.bookingId;
                    if (bookingId) {
                        const extendBtn = document.querySelector('.btn-extend-collection[data-booking-id="' + bookingId + '"]');
                        if (extendBtn) {
                            extendBtn.disabled = true;
                            extendBtn.classList.add('disabled');
                        }
                        
                        // Пока таймер идет - кнопка "Завершить сбор" должна быть активна
                        const finishBtn = document.querySelector('.btn-finish-collection[data-booking-id="' + bookingId + '"]');
                        if (finishBtn) {
                            finishBtn.disabled = false;
                            finishBtn.classList.remove('disabled');
                            finishBtn.title = '';
                        }
                    }
                });

                // Старые таймеры с data-start (если ещё используются) просто считаем как прошедшее время
                nodesWithStart.forEach(el => {
                    const start = parseInt(el.dataset.start, 10);
                    if (!start || start > now) return;

                    let diffMs = now - start;
                    if (diffMs <= 0) {
                        el.textContent = '[0 мин 00 сек]';
                        return;
                    }

                    const totalSeconds = Math.floor(diffMs / 1000);
                    const minutes = Math.floor(totalSeconds / 60);
                    const seconds = totalSeconds % 60;

                    el.textContent = '[' + minutes + ' мин ' + String(seconds).padStart(2, '0') + ' сек]';
                });
            };

            updateCollectionTimers();
            // Обновляем таймер каждую секунду, чтобы он работал "в реальном времени"
            setInterval(updateCollectionTimers, 1000);
        }

    });

    $(document).on('click', '.btn-cancel-booking-confirm-vue', function(e) {
        e.preventDefault();
        var btn = $(this);
        var bookingId = btn.data('booking-id');

        if (!bookingId) {
            console.error('Booking ID not found');
            return;
        }

        var bookingIdNum = parseInt(bookingId, 10);

        if (!btn.data('originalHtml')) {
            btn.data('originalHtml', btn.html());
        }

        btn.prop('disabled', true).addClass('disabled').html(
            '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' +
            '<span> ' + (btn.text().trim() || '...') + '</span>'
        );

        var restoreButton = function() {
            btn.prop('disabled', false).removeClass('disabled');
            if (btn.data('originalHtml')) {
                btn.html(btn.data('originalHtml'));
            }
        };

        $.ajax({
            url: `/booking/${bookingIdNum}/cancel`,
            type: 'POST',
            dataType: 'json',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content') || ''
            },
            success: function(res) {
                restoreButton();

                if (res.status) {
                    var modal = bootstrap.Modal.getInstance(document.getElementById('cancelBookingModal' + bookingIdNum));
                    if (modal) {
                        modal.hide();
                    }
                    if (typeof bookingCoreApp !== 'undefined' && bookingCoreApp.showAjaxMessage) {
                        bookingCoreApp.showAjaxMessage(res);
                    } else {
                        alert(res.message || 'Бронь успешно отменена');
                    }
                    setTimeout(function() {
                        window.location.reload();
                    }, 500);
                } else if (res.message) {
                    if (typeof bookingCoreApp !== 'undefined' && bookingCoreApp.showAjaxMessage) {
                        bookingCoreApp.showAjaxMessage(res);
                    } else {
                        alert(res.message);
                    }
                }
            },
            error: function(e) {
                restoreButton();

                if (e.status === 419) {
                    alert('Сессия истекла, обновите страницу');
                } else if (e.responseJSON && e.responseJSON.message) {
                    alert('Ошибка: ' + e.responseJSON.message);
                } else {
                    alert('Произошла ошибка при отмене бронирования');
                }
            }
        });
    });
});
