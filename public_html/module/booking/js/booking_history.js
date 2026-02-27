document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('booking-history');
    if (!el) return;

    new Vue({
        el: '#booking-history',
        data: {
            searchResults: [],
            selectedUser: null,
            currentUserId: null,
            currentBookingId: null,
            debounceTimeout: null,
            isSearching: false,
            isResults: false,
            noResults: false,
            userSearchQuery: '',

            hunterSearchQuery: '',
            hunterSearchResults: [],
            hunterIsSearching: false,
            hunterNoResults: false,
            hunterDebounceTimeout: null,
            currentCollectionBookingId: null,

            // Слоты для охотников (каждый слот имеет свой поиск)
            hunterSlots: [],

            // История отказавшихся охотников
            declinedHunters: [],

            // Переводы для кнопок
            inviteText: el.dataset.inviteText || 'Пригласить',
            invitedText: el.dataset.invitedText || 'Приглашен',
            acceptedText: el.dataset.acceptedText || 'Подтвержден',
            declinedText: el.dataset.declinedText || 'Отказался',
            prepaymentPaidMap: {}
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


            // ТОЛЬКО ДЛЯ МАСТЕРА
            openCollectionAsMaster(bookingId, event) {
                const bookingIdNum = parseInt(bookingId, 10);
                const me = this;

                const btn = event?.currentTarget ?? null;
                let originalHtml = null;

                if (btn) {
                    originalHtml = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = `
                <span class="spinner-border spinner-border-sm me-1"></span>
                <span>${btn.textContent.trim()}</span>
            `;
                }

                const restoreButton = () => {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                };

                $.post(`/booking/${bookingIdNum}/start-collection`)
                    .done(res => {
                        restoreButton();

                        if (!res.status) {
                            bookingCoreApp.showAjaxMessage(res);
                            return;
                        }

                        me.currentCollectionBookingId = bookingIdNum;

                        const modalEl = document.getElementById('collectionModal' + bookingIdNum);
                        if (modalEl) {
                            new bootstrap.Modal(modalEl).show();
                        }

                        setTimeout(() => {
                            me.initializeHunterSlots(bookingIdNum);
                        }, 200);
                    })
                    .fail(() => {
                        restoreButton();
                        alert('Ошибка при запуске сбора');
                    });
            },
            // ТОЛЬКО ДЛЯ ПРИГЛАШЕННОГО
            openCollectionAsHunter(bookingId) {
                const bookingIdNum = parseInt(bookingId, 10);

                this.currentCollectionBookingId = bookingIdNum;

                const modalEl = document.getElementById('collectionModal' + bookingIdNum);
                if (modalEl) {
                    new bootstrap.Modal(modalEl).show();
                }

                setTimeout(() => {
                    this.initializeHunterSlots(bookingIdNum);
                }, 200);
            },
            initializeHunterSlots(bookingId) {
                // Получаем количество охотников из DOM элемента модального окна
                const modal = document.getElementById('collectionModal' + bookingId);
                if (!modal) return;

                // Очищаем историю отказавшихся при инициализации
                this.declinedHunters = [];

                // Получаем количество из data-атрибута
                const huntersCount = parseInt(modal.dataset.huntersCount || '0', 10);

                if (huntersCount > 0) {
                    // Инициализируем массив слотов
                    this.hunterSlots = Array.from({length: huntersCount}, () => ({
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
                        const allHunters = data.hunters || [];
                        console.log('Найдено приглашенных охотников:', allHunters.length, allHunters);

                        // Разделяем охотников на активных (не declined) и declined
                        const activeHunters = allHunters.filter(h => h.invitation_status !== 'declined');
                        const declinedHunters = allHunters.filter(h => h.invitation_status === 'declined');

                        // Сохраняем declined охотников в отдельный массив для истории
                        this.$set(this, 'declinedHunters', declinedHunters);

                        if (data.status && activeHunters.length > 0) {
                            console.log('Обработка', activeHunters.length, 'активных охотников');

                            // Создаем новый массив слотов только для активных охотников (не declined)
                            const updatedSlots = this.hunterSlots.map((slot, index) => {
                                if (index < activeHunters.length) {
                                    const hunter = activeHunters[index];

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
                                // Если слот не заполнен активным охотником, очищаем его
                                return {
                                    ...slot,
                                    hunter: null,
                                    query: ''
                                };
                            });

                            // Заменяем весь массив для реактивности Vue
                            this.$set(this, 'hunterSlots', updatedSlots);

                            console.log('Массив hunterSlots обновлен:', this.hunterSlots.length, 'слотов');
                            console.log('Отказавшиеся охотники:', declinedHunters.length);

                            // Принудительно обновляем Vue для отображения
                            this.$nextTick(() => {
                                this.$forceUpdate();
                                console.log('Vue принудительно обновлен');
                                // Проверяем состояние кнопки "Завершить сбор" после загрузки охотников
                                this.checkFinishCollectionButton(bookingId);
                            });
                        } else {
                            console.log('Нет активных охотников. Статус:', data.status, 'Active hunters:', activeHunters.length, 'Declined:', declinedHunters.length);
                            // Очищаем все слоты, если нет активных охотников
                            const clearedSlots = this.hunterSlots.map(slot => ({
                                ...slot,
                                hunter: null,
                                query: ''
                            }));
                            this.$set(this, 'hunterSlots', clearedSlots);

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
                // const timerEl = document.querySelector('.collection-timer[data-booking-id="' + bookingId + '"]');
                // if (!timerEl) {
                //     // Если таймера нет, разрешаем кнопку
                //     finishBtn.disabled = false;
                //     finishBtn.classList.remove('disabled');
                //     finishBtn.title = '';
                //     return;
                // }
                //
                // const end = parseInt(timerEl.dataset.end, 10);
                // if (!end) {
                //     finishBtn.disabled = false;
                //     finishBtn.classList.remove('disabled');
                //     finishBtn.title = '';
                //     return;
                // }

                // const now = Date.now();
                // const diffMs = end - now;
                // const isTimerFinished = diffMs <= 0;

                // if (isTimerFinished) {
                // Таймер закончен - проверяем количество охотников
                const animalMinHunters = parseInt(modal.dataset.animalMinHunters || '0', 10);

                // Считаем приглашенных охотников (со статусом не declined)
                // Учитываем как охотников из системы, так и внешних (по email)
                let invitedCount = 0;
                if (this.hunterSlots && this.hunterSlots.length > 0) {
                    invitedCount = this.hunterSlots.filter(slot =>
                        slot.hunter &&
                        slot.hunter.invited &&
                        slot.hunter.invitation_status === 'accepted'
                    ).length;
                }

                // Если не хватает охотников - блокируем кнопку
                if (invitedCount < animalMinHunters) {
                    finishBtn.disabled = true;
                    finishBtn.classList.add('disabled');
                    finishBtn.title = 'Таймер закончен, но не все охотники собраны. Необходимо собрать ' + animalMinHunters + ' охотников.';
                } else {
                    finishBtn.disabled = false;
                    finishBtn.classList.remove('disabled');
                    finishBtn.title = '';
                }
                // } else {
                //     // Таймер еще идет - разрешаем кнопку
                //     finishBtn.disabled = false;
                //     finishBtn.classList.remove('disabled');
                //     finishBtn.title = '';
                // }
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
                            const email = query.trim();
                            const hunterData = {
                                id: null,
                                email: email,
                                first_name: '',
                                last_name: '',
                                user_name: null,
                                phone: null,
                                invited: true,
                                invitation_status: 'pending',
                                is_external: true
                            };
                            const updatedSlot = {
                                ...slot,
                                hunter: hunterData,
                                query: email,
                                results: [],
                                showResults: false,
                                noResults: false
                            };

                            const updatedSlots = [...this.hunterSlots];
                            updatedSlots[slotIndex] = updatedSlot;
                            this.$set(this, 'hunterSlots', updatedSlots);
                            this.$nextTick(() => {
                                this.$forceUpdate();
                                this.checkFinishCollectionButton(bookingIdNum);
                                setTimeout(() => {
                                    this.loadInvitedHunters(bookingIdNum);
                                }, 500);
                            });
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
                // const modal = document.getElementById('collectionModal' + bookingId);
                // const animalMinHunters = parseInt(modal.dataset.animalMinHunters || '0', 10);

                // Перед отправкой запроса дополнительно проверяем, что все участники подтвердили приглашение
                // На клиенте считаем неподтверждёнными всех с invitation_status, отличным от 'accepted'
                // (сервер всё равно повторно проверит это условие для надёжности)
                try {
                    if (me.hunterSlots && me.hunterSlots.length > 0) {
                        var hasNotAccepted = me.hunterSlots.some(function (slot) {
                            return slot.hunter &&
                                slot.hunter.invited &&
                                slot.hunter.invitation_status &&
                                slot.hunter.invitation_status !== 'accepted';
                        });
                        // let invitedCount = 0;
                        // invitedCount = this.hunterSlots.filter(slot =>
                        //     slot.hunter &&
                        //     slot.hunter.invited &&
                        //     slot.hunter.invitation_status === 'accepted'
                        // ).length;
                        //
                        // if (invitedCount < animalMinHunters) {
                        //     alert('Нельзя завершить сбор: не все участники подтвердили приглашение.');
                        //     return;
                        // }
                    }
                } catch (e) {
                    console.warn('finishCollection: не удалось выполнить предварительную проверку статусов приглашений', e);
                }

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
            },
            bookingPrepaymentPaid(event) {

                const btn = event.currentTarget;
                if (!btn) return;

                const bookingId = btn.dataset.bookingId;
                if (!bookingId) return;

                // Если уже нажато — ничего не делаем
                if (this.prepaymentPaidMap[bookingId]) return;

                fetch(`/booking/${bookingId}/prepayment-paid`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                }).then(res => {
                    if (!res.ok) {
                        throw new Error();
                    }

                    this.$set(this.prepaymentPaidMap, bookingId, true);
                });
            },

            loadBookingPlaces(booking, event) {
                const bookingIdNum = parseInt(booking.id, 10);
                const btn = event?.currentTarget ?? null;
                let originalHtml = null;

                if (btn) {
                    originalHtml = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = ` 
            <span>${btn.textContent.trim()}</span>`;
                }

                const restoreButton = () => {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                };

                $.post(`/booking/${booking.id}/places`)
                    .done(res => {
                        restoreButton();

                        if (!res.status) {
                            alert('Ошибка получения данных');
                            return;
                        }

                        const places = res.places ?? {};

                        const modalEl = document.getElementById('placeBookingModal' + bookingIdNum);
                        if (!modalEl) return;

                        const contentEl = modalEl.querySelector('#booking-places-content-' + bookingIdNum);
                        if (!contentEl) return;

                        contentEl.innerHTML = '';
                        const self = this;

                        // группируем комнаты по типу
                        const roomsByType = {};
                        res.rooms.forEach(room => {
                            if (!roomsByType[room.title]) {
                                roomsByType[room.title] = [];
                            }
                            roomsByType[room.title].push(room);
                        });

                        Object.keys(roomsByType).forEach(type => {
                            const block = document.createElement('div');
                            block.className = 'mb-3 p-2';

                            const header = document.createElement('h4');
                            header.textContent = type;
                            header.style.textAlign = 'center';
                            block.appendChild(header);

                            const list = document.createElement('ul');
                            list.style.listStyle = 'none';
                            list.style.padding = '0';

                            // 🔴 ВАЖНО: идём по каждой комнате отдельно
                            roomsByType[type].forEach(room => {
                                const roomId = room.room_id;

                                for (let i = 0; i < room.total_guests_in_type; i++) {
                                    const placeNumber = i + 1;
                                    const placeData = places[roomId]?.[placeNumber]?.[0] ?? null;

                                    const li = document.createElement('li');
                                    li.className = 'guest-slot mb-2';
                                    li.style.display = 'flex';
                                    li.style.alignItems = 'center';
                                    li.style.gap = '10px';
                                    li.style.border = '1px solid #ccc';
                                    li.style.minHeight = '30px';

                                    // 1 место
                                    const textDiv = document.createElement('div');
                                    textDiv.textContent = `место ${placeNumber}`;
                                    textDiv.className = 'text-muted';
                                    textDiv.style.width = '70px';
                                    textDiv.style.marginLeft = '10px';
                                    li.appendChild(textDiv);

                                    //  имя / свободно
                                    const inputDiv = document.createElement('div');
                                    inputDiv.style.flex = '1';

                                    if (placeData) {
                                        const firstName = placeData.user.first_name ?? '';
                                        const lastName = placeData.user.last_name ?? '';
                                        inputDiv.textContent = firstName + ' ' + lastName;
                                        inputDiv.className = 'fw-semibold text-success';
                                    } else {
                                        inputDiv.textContent = 'свободно';
                                        inputDiv.className = 'text-muted';
                                    }

                                    li.appendChild(inputDiv);

                                    // 3 кнопка
                                    if (!booking.is_all_places_assigned) {
                                        const button = document.createElement('button');
                                        button.type = 'button';
                                        button.className = 'btn btn-sm';

                                        if (placeData) {
                                            button.textContent = 'Отменить';
                                            button.classList.add('btn-danger');
                                            button.addEventListener('click', () => {
                                                self.cancelSelectPlace(booking.id, placeData.id);
                                            });
                                        } else {
                                            button.textContent = 'Выбрать';
                                            button.classList.add('btn-primary');
                                            button.addEventListener('click', () => {
                                                self.selectPlace(booking.id, roomId, placeNumber);
                                            });
                                        }
                                        li.appendChild(button);
                                    }
                                    list.appendChild(li);
                                }
                            });

                            block.appendChild(list);
                            contentEl.appendChild(block);
                        });

                        new bootstrap.Modal(modalEl).show();
                    })
                    .fail(() => {
                        restoreButton();
                        alert('Ошибка при запросе к серверу');
                    });
            },
            selectPlace(bookingId, roomId, placeNumber) {
                const self = this;

                $.ajax({
                    url: `/booking/${bookingId}/select-place`,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content') || '',
                        room_id: roomId,
                        place_number: placeNumber
                    },
                    success: function (res) {

                        if (!res.success) {
                            bookingCoreApp.showAjaxMessage(res);
                            return;
                        }

                        const modalEl = document.getElementById('placeBookingModal' + bookingId);
                        if (!modalEl) return;

                        const contentEl = modalEl.querySelector('#booking-places-content-' + bookingId);
                        if (!contentEl) return;

                        const liList = contentEl.querySelectorAll('.guest-slot');

                        liList.forEach(li => {

                            const placeLabel = li.querySelector('div.text-muted');
                            const nameDiv = li.children[1];
                            const btn = li.querySelector('button');

                            if (!placeLabel) return;

                            if (placeLabel.textContent.includes(`место ${placeNumber}`)) {

                                nameDiv.textContent = `${res.place.user.first_name} ${res.place.user.name ?? ''}`;
                                nameDiv.className = 'fw-semibold text-success';

                                if (res.place.user_id === res.currentUserId) {
                                    btn.textContent = 'Отменить';
                                    btn.classList.remove('btn-primary');
                                    btn.classList.add('btn-danger');

                                    btn.disabled = false;
                                    btn.onclick = function () {
                                        self.cancelSelectPlace(bookingId, res.place.id);
                                    };
                                } else {
                                    btn.textContent = 'Отм';
                                    btn.classList.remove('btn-primary');
                                    btn.disabled = true;
                                }
                            }

                        });

                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        let res = {
                            status: false,
                            message: 'Ошибка при запросе к серверу'
                        };

                        if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                            res.message = jqXHR.responseJSON.message;
                        }
                        bookingCoreApp.showAjaxMessage(res);
                    }
                });
            },

            cancelSelectPlace(bookingId, placeId) {
                fetch(`/booking/${bookingId}/cancel-select-place`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        place_id: placeId,
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Место успешно выбрано!');
                        } else {
                            alert('Ошибка при выборе места: ' + data.message);
                        }
                    })
            },

            calculatingBookingModal(booking, event) {
                const bookingIdNum = parseInt(booking.id, 10);
                const btn = event?.currentTarget ?? null;
                let originalHtml = null;

                if (btn) {
                    originalHtml = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = ` 
            <span>${btn.textContent.trim()}</span>`;
                }

                const restoreButton = () => {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                };

                $.get(`/booking/${booking.id}/calculating`)
                    .done(res => {
                        restoreButton();

                        if (!res.status) {
                            alert('Ошибка получения данных');
                            return;
                        }

                        const places = res.places ?? {};

                        const modalEl = document.getElementById('calculatingBookingModal' + bookingIdNum);
                        if (!modalEl) return;

                        const contentEl = modalEl.querySelector('#calculating-content-' + bookingIdNum);
                        if (!contentEl) return;

                        // Формируем HTML таблицы
                        let html = `<table class="table table-bordered">
    <thead>
        <tr>
            <th>Услуги</th>
            <th>Всего расходы</th>
            <th>Мои расходы</th>
        </tr>
    </thead>
    <tbody>`;

                        html += `<tr"><td colspan="3"></td></tr>`;
                        (res.items || []).forEach(item => {
                            html += `
    <tr>
        <td>${item.name}</td>
        <td>${item.total_cost ?? 0}</td>
        <td>${item.my_cost ?? 0}</td>
    </tr>`;
                        });

// === Блок "Трофеи" ===
                        html += `<tr class="table-secondary"><td colspan="3"><strong>Трофеи</strong></td></tr>`;
// допустим пока пусто, можно добавить пример
                        (res.trophies || []).forEach(item => {
                            html += `
    <tr>
        <td>${item.name}</td>
        <td>${item.total_cost ?? 0}</td>
        <td>${item.my_cost ?? 0}</td>
    </tr>`;
                        });

// === Блок "Штрафы" ===
                        html += `<tr class="table-secondary"><td colspan="3"><strong>Штрафы</strong></td></tr>`;
                        (res.penalties || []).forEach(item => {
                            html += `
    <tr>
        <td>${item.name}</td>
        <td>${item.total_cost ?? 0}</td>
        <td>${item.my_cost ?? 0}</td>
    </tr>`;
                        });

// === Блок "Доп. услуги" ===
                        html += `<tr class="table-secondary"><td colspan="3"><strong>Доп. услуги</strong></td></tr>`;
                        (res.meals || []).forEach(item => {
                            html += `
    <tr>
        <td>${item.name}</td>
        <td>${item.total_cost ?? 0}</td>
        <td>${item.my_cost ?? 0}</td>
    </tr>`;
                        });
                        (res.preparation || []).forEach(item => {
                            html += `
    <tr>
        <td>${item.name}</td>
        <td>${item.total_cost ?? 0}</td>
        <td>${item.my_cost ?? 0}</td>
    </tr>`;
                        });
                        (res.addetionals || []).forEach(item => {
                            html += `
    <tr>
        <td>${item.name}</td>
        <td>${item.total_cost ?? 0}</td>
        <td>${item.my_cost ?? 0}</td>
    </tr>`;
                        });

// === Блок "Расходы охотников" ===

                        html += `<tr class="table-secondary"><td colspan="3"><strong>Расходы охотников</strong></td></tr>`;
                        (res.spendings || []).forEach(item => {
                            html += `
    <tr>
        <td>${item.name}</td>
        <td>${item.total_cost ?? 0}</td>
        <td>${item.my_cost ?? 0}</td>
    </tr>`;
                        });

                        html += `<tr"><td colspan="3"></td></tr>`;
                        (res.all_items || []).forEach(item => {
                            html += `
    <tr>
        <td>${item.name}</td>
        <td>${item.total_paid ?? 0}</td>
        <td>${item.my_cost ?? 0}</td>
    </tr>`;
                        });

                        html += `</tbody></table>`;

                        contentEl.innerHTML = html;


                        new bootstrap.Modal(modalEl).show();
                    })
                    .fail(() => {
                        restoreButton();
                        alert('Ошибка при запросе к серверу');
                    });
            },
        },

        mounted() {
            document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
                new bootstrap.Popover(el);
            });

            // Добавляем обработчики событий для модальных окон сбора охотников
            const me = this;
            // Используем делегирование событий для всех модальных окон
            document.addEventListener('shown.bs.modal', function (event) {
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

            // Подписка на каналы бронирований для обновления счетчика в реальном времени
            if (window.LaravelEcho) {
                const subscribedBookings = new Set();

                // Функция для подписки на канал бронирования
                const subscribeToBooking = (bookingId) => {
                    if (!bookingId || subscribedBookings.has(bookingId)) {
                        return;
                    }

                    subscribedBookings.add(bookingId);

                    try {
                        const channel = window.LaravelEcho.private(`booking-${bookingId}`);

                        channel.listen('.hunter.invitation.accepted', (e) => {
                            console.log('[HunterInvitationAccepted] Получено событие принятия приглашения:', e);

                            // Находим строку таблицы с этим booking_id
                            const targetRow = document.querySelector(`tr[data-booking-id="${bookingId}"]`);
                            if (targetRow) {
                                const statusCell = Array.from(targetRow.querySelectorAll('td')).find(td => {
                                    return td.querySelector('.collection-timer') ||
                                        td.classList.toString().includes('START_COLLECTION');
                                });

                                if (statusCell) {
                                    // Находим элемент со счетчиком
                                    const counterElement = statusCell.querySelector('.text-muted.mt-1');
                                    if (counterElement && e.accepted_count !== undefined && e.total_hunters_needed !== undefined) {
                                        counterElement.textContent = `Собранно ${e.accepted_count}/${e.total_hunters_needed}`;
                                        console.log('[HunterInvitationAccepted] Счетчик обновлен:', counterElement.textContent);
                                    }
                                }
                            }
                        });

                        channel.subscribed(() => {
                            console.log(`[HunterInvitationAccepted] ✅ Успешно подписан на канал booking-${bookingId}`);
                        });

                        channel.error((error) => {
                            console.error(`[HunterInvitationAccepted] ❌ Ошибка подписки на канал booking-${bookingId}:`, error);
                        });
                    } catch (e) {
                        console.error(`[HunterInvitationAccepted] ❌ Исключение при подписке на booking-${bookingId}:`, e);
                    }
                };

                // Находим все строки таблицы с data-booking-id
                const bookingRows = document.querySelectorAll('tr[data-booking-id]');
                bookingRows.forEach((row) => {
                    const bookingId = row.dataset.bookingId;
                    if (bookingId) {
                        subscribeToBooking(bookingId);
                    }
                });

                const timers = document.querySelectorAll('.collection-timer[data-booking-id]');
                timers.forEach((timer) => {
                    const bookingId = timer.dataset.bookingId;
                    if (bookingId) {
                        subscribeToBooking(bookingId);
                    }
                });
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
                                    // if (invitedCount < requiredHunters) {
                                    //     finishBtn.disabled = true;
                                    //     finishBtn.classList.add('disabled');
                                    //     finishBtn.title = 'Таймер закончен, но не все охотники собранны. Необходимо собрать ' + requiredHunters + ' охотников.';
                                    // } else {
                                    //     // Если достаточно охотников - разрешаем кнопку
                                    //     finishBtn.disabled = false;
                                    //     finishBtn.classList.remove('disabled');
                                    //     finishBtn.title = '';
                                    // }
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

                            // finishBtn.disabled = false;
                            // finishBtn.classList.remove('disabled');
                            // finishBtn.title = '';
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

            const updateBedsTimers = () => {
                const nodes = document.querySelectorAll('.beds-timer[data-end]');
                const now = Date.now();

                nodes.forEach(el => {
                    const end = parseInt(el.dataset.end, 10);
                    if (!end) return;

                    let diffMs = end - now;

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

            // Обновляем таймер каждую секунду, чтобы он работал "в реальном времени"
            updateCollectionTimers();
            updateBedsTimers();
            setInterval(() => {
                updateCollectionTimers();
                updateBedsTimers();
            }, 1000);

            //Кнопка оплата или оплачено в модальном окне предоплата
            document.querySelectorAll('.btn-prepayment').forEach(btn => {
                const bookingId = btn.dataset.bookingId;
                const isPaid = btn.dataset.prepaymentPaid === '1' || btn.dataset.prepaymentPaid === 'true';
                this.$set(this.prepaymentPaidMap, bookingId, isPaid);
            });
        }

    });

    $(document).on('click', '.btn-cancel-booking-confirm-vue', function (e) {
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

        var restoreButton = function () {
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
            success: function (res) {
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
                    alert('Произошла ошибка при отмене бронирования');
                }
            }
        });
    });
});
