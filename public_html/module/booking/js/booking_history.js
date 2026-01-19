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
            openCollectionModal(bookingId) {
                this.currentCollectionBookingId = bookingId;
                // Инициализируем слоты для охотников при открытии модального окна
                this.initializeHunterSlots(bookingId);
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
                        debounceTimeout: null,
                        showEmailInput: false,
                        emailMessage: '',
                        emailAddress: '' // Для хранения email, если охотник не выбран
                    }));
                } else {
                    this.hunterSlots = [];
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
                
                this.inviteHunter(slot.hunter, bookingId, event);
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
                    }
                } else {
                    // При закрытии очищаем сообщение
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
            const me = this;
            document.querySelectorAll('.modal[id^="collectionModal"]').forEach(function (modalEl) {
                modalEl.addEventListener('hidden.bs.modal', function () {
                    me.hunterSearchQuery = '';
                    me.hunterSearchResults = [];
                    me.hunterNoResults = false;
                });
            });

            const updateCollectionTimers = () => {
                const nodes = document.querySelectorAll('.collection-timer[data-start]');
                const now = Date.now();

                nodes.forEach(el => {
                    const start = parseInt(el.dataset.start, 10);
                    if (!start || start > now) return;

                    let diffMs = now - start;
                    let totalMinutes = Math.floor(diffMs / 60000);

                    const days = Math.floor(totalMinutes / (60 * 24));
                    totalMinutes -= days * 60 * 24;

                    const hours = Math.floor(totalMinutes / 60);
                    const minutes = totalMinutes - hours * 60;

                    const parts = [];
                    if (days > 0) parts.push(days + 'д');
                    if (hours > 0 || days > 0) parts.push(hours + ' ч');
                    parts.push(minutes + ' мин');

                    el.textContent = '[' + parts.join(', ') + ']';
                });
            };

            updateCollectionTimers();
            setInterval(updateCollectionTimers, 60000);
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
