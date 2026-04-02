document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('booking-history');
    if (!el) return;

    new Vue({
        el: '#booking-history',
        data: {
            booking: [],
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
            masterHunterId: null,

            hunterToReplace: null,
            replaceQuery: '',
            replaceResults: [],
            isSearchingReplace: false,
            showReplaceResults: false,
            selectedReplaceHunter: null,

            // Слоты для охотников (каждый слот имеет свой поиск)
            hunterSlots: [],
            declinedHunters: [],
            invitedHunters:[],

            inviteText: el.dataset.inviteText || 'Пригласить',
            invitedText: el.dataset.invitedText || 'Приглашен',
            acceptedText: el.dataset.acceptedText || 'Подтвержден',
            declinedText: el.dataset.declinedText || 'Отказался',
            prepaymentPaidMap: {},

            placesMap: {},
        },
        computed: {
            allowedBookingStatuses() {
                return [
                    '{{ \Modules\Booking\Models\Booking::FINISHED_COLLECTION }}',
                    '{{ \Modules\Booking\Models\Booking::PREPAYMENT_COLLECTION }}',
                    '{{ \Modules\Booking\Models\Booking::FINISHED_PREPAYMENT }}',
                    '{{ \Modules\Booking\Models\Booking::BED_COLLECTION }}',
                    '{{ \Modules\Booking\Models\Booking::FINISHED_BED }}'
                ];
            }
        },
        methods: {
            formatTimer(diffMs) {
                const totalSeconds = Math.floor(diffMs / 1000);
                const minutes = Math.floor(totalSeconds / 60);
                const seconds = totalSeconds % 60;

                return `[${minutes} мин ${String(seconds).padStart(2, '0')} сек]`;
            },
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

            isCollectionTimerExpired(bookingId) {
                window.collectionState = window.collectionState || {};
                return !!window.collectionState[String(bookingId)];
            },

            // ТОЛЬКО ДЛЯ МАСТЕРА
            openCollectionAsMaster(bookingId, event) {
                const bookingIdNum = parseInt(bookingId, 10);
                const me = this;
                const btn = event?.currentTarget || null;
                if (btn) bc_button_loading(btn, true);

                $.post(`/booking/${bookingIdNum}/start-collection`)
                    .done(res => {
                        bc_button_loading(btn, false);

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
                        bc_button_loading(btn, false);
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
                const modal = document.getElementById('collectionModal' + bookingId);
                if (!modal) return;

                this.declinedHunters = [];
                const huntersCount = parseInt(modal.dataset.huntersCount || '0', 10);

                if (huntersCount > 0) {
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
                        emailAddress: ''
                    }));

                    this.loadInvitedHunters(bookingId);

                    this.$nextTick(() => {
                        this.checkFinishCollectionButton(bookingId);
                    });
                } else {
                    this.hunterSlots = [];
                }
            },
            getStatusBadge(hunter) {
                switch (hunter.invitation_status) {
                    case 'accepted': return { text: 'Приглашение принято', class: 'bg-secondary' }
                    case 'pending':  return { text: 'Приглашен', class: 'bg-secondary' }
                    case 'declined': return { text: 'Приглашение отклонено', class: 'bg-secondary' }
                    default: return { text: '', class: '' }
                }
            },
            getPayBadge(hunter) {
                if (hunter.prepayment_paid === true || hunter.prepayment_paid === 1) {
                    return {
                        text: 'Оплачено',
                        class: 'bg-success'
                    }
                }

                if (hunter.prepayment_paid === false || hunter.prepayment_paid === 0) {
                    return {
                        text: 'Ожидается оплата',
                        class: 'bg-warning'
                    }
                }
                return {
                    text: 'Не оплачено',
                    class: 'bg-danger'
                }
            },
            loadInvitedHunters(bookingId) {
                fetch(`/booking/${bookingId}/invited-hunters`)
                    .then(res => res.json())
                    .then(data => {
                        const allHunters = data.hunters || [];
                        const activeHunters = allHunters.filter(h => h.invitation_status !== 'declined');
                        const declinedHunters = allHunters.filter(h => h.invitation_status === 'declined');
                        this.$set(this, 'declinedHunters', declinedHunters);
                        this.invitedHunters = activeHunters
                        this.booking = data.booking
                        if (data.status && activeHunters.length > 0) {
                            const updatedSlots = this.hunterSlots.map((slot, index) => {
                                if (index < activeHunters.length) {
                                    const hunter = activeHunters[index];

                                    if (typeof hunter.showEmailInput === 'undefined') {
                                        hunter.showEmailInput = false;
                                    }
                                    if (typeof hunter.emailMessage === 'undefined') {
                                        hunter.emailMessage = '';
                                    }
                                    let queryText = '';
                                    if (hunter.is_external) {
                                        queryText = hunter.email || '';
                                    } else {
                                        queryText = hunter.user_name || (hunter.first_name + ' ' + hunter.last_name).trim() || '';
                                    }

                                    return {
                                        ...slot,
                                        hunter: hunter,
                                        query: queryText
                                    };
                                }
                                return {
                                    ...slot,
                                    hunter: null,
                                    query: ''
                                };
                            });

                            this.$set(this, 'hunterSlots', updatedSlots);
                            this.$nextTick(() => {
                                this.$forceUpdate();
                                this.checkFinishCollectionButton(bookingId);
                            });
                        } else {
                            const clearedSlots = this.hunterSlots.map(slot => ({
                                ...slot,
                                hunter: null,
                                query: ''
                            }));
                            this.$set(this, 'hunterSlots', clearedSlots);
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
                const finishBtn = document.querySelector('.btn-finish-collection[data-booking-id="' + bookingId + '"]');
                const modal = document.getElementById('collectionModal' + bookingId);
                if (!finishBtn || !modal) return;

                const animalMinHunters = parseInt(modal.dataset.animalMinHunters || '0', 10);

                // Считаем приглашенных охотников (со статусом не accepted)
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
                if (this.hunterSearchQuery.length < 2) {
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

                if (event) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                this.$set(hunter, 'showEmailInput', false);
                hunter.showEmailInput = false;

                const bookingIdNum = parseInt(bookingId, 10);
                if (!bookingIdNum) return;

                const btn = event?.currentTarget || null;
                if (btn) bc_button_loading(btn, true);

                $.ajax({
                    url: `/booking/${bookingIdNum}/invite-hunter`,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        hunter_id: hunter.id,
                        _token: $('meta[name="csrf-token"]').attr('content') || ''
                    },
                    success: (res) => {
                        bc_button_loading(btn, false);

                        if (res.status) {
                            // Находим объект в массиве и обновляем его напрямую
                            const index = this.hunterSearchResults.findIndex(h => h.id === hunter.id);
                            if (index !== -1) {
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
                        bc_button_loading(btn, false);
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

                if (slot.query.length === 0 || slot.query.trim() === '') {
                    slot.hunter = null;
                    if (slot.showEmailInput) {
                        slot.showEmailInput = false;
                        slot.emailAddress = '';
                        slot.emailMessage = '';
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

                if (!slot.query || slot.query.trim() === '') {
                    this.clearHunterSlot(slotIndex);
                    return;
                }

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

                this.$set(slot.hunter, 'invited', true);
                this.$set(slot.hunter, 'invitation_status', 'pending');
                this.$forceUpdate();
                this.inviteHunter(slot.hunter, bookingId, null);
            },
            toggleEmailInputForSlot(slotIndex) {
                const slot = this.hunterSlots[slotIndex];
                if (!slot) return;

                slot.showEmailInput = !slot.showEmailInput;
                if (slot.showEmailInput) {
                    slot.showResults = false;
                    slot.results = [];
                    slot.noResults = false;

                    if (slot.hunter && !slot.emailAddress) {
                        slot.emailAddress = slot.hunter.email || '';
                    } else if (!slot.hunter && !slot.emailAddress && slot.query) {
                        const queryTrim = slot.query.trim();
                        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (emailPattern.test(queryTrim)) {
                            slot.emailAddress = queryTrim;
                        }
                    }
                } else {
                    slot.emailMessage = '';
                }
            },
            handleEmailAddressInput(slotIndex) {
                const slot = this.hunterSlots[slotIndex];
                if (!slot) return;

                if (!slot.hunter && (!slot.emailAddress || slot.emailAddress.trim() === '')) {
                    slot.showEmailInput = false;
                    slot.emailMessage = '';
                }
            },
            sendEmailForSlot(slotIndex, bookingId, event) {
                const slot = this.hunterSlots[slotIndex];
                if (!slot) return;

                let hunterId = null;
                let emailAddress = slot.emailAddress || '';

                if (slot.hunter && slot.hunter.id) {
                    hunterId = slot.hunter.id;
                    emailAddress = slot.hunter.email || emailAddress;
                } else if (!emailAddress) {
                    alert('Необходимо выбрать охотника или указать email адрес');
                    return;
                }

                const message = slot.emailMessage || '';
                if (!message.trim()) {
                    alert('Введите текст сообщения');
                    return;
                }

                if (hunterId) {

                    const hunter = slot.hunter;
                    hunter.emailMessage = message;
                    this.sendHunterEmail(hunter, bookingId, event);
                } else if (emailAddress) {
                    alert('Для отправки email необходимо выбрать охотника из системы. Если охотник не в системе, его нужно сначала добавить.');
                    return;
                }
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
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(query)) {
                    alert('Введите корректный email адрес');
                    return;
                }

                const bookingIdNum = parseInt(bookingId, 10);
                if (!bookingIdNum) return;

                const btn = event?.currentTarget || null;
                if (btn) bc_button_loading(btn, true);

                $.ajax({
                    url: `/booking/${bookingIdNum}/invite-hunter-by-email`,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        email: query,
                        _token: $('meta[name="csrf-token"]').attr('content') || ''
                    },
                    success: (res) => {
                        bc_button_loading(btn, false);

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
                        bc_button_loading(btn, false);
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

                const btn = event?.currentTarget || null;
                if (btn) bc_button_loading(btn, true);

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
                        bc_button_loading(btn, false);

                        if (res.status) {
                            bookingCoreApp.showAjaxMessage(res);
                            hunter.showEmailInput = false;
                        } else if (res.message) {
                            alert(res.message);
                        }
                    },
                    error: function (e) {
                        bc_button_loading(btn, false);
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
                if (this.userSearchQuery.length < 1) return;

                clearTimeout(this.debounceTimeout);

                this.debounceTimeout = setTimeout(() => {
                    this.searchUsers();
                }, 300);
            },
            searchUsers() {
                if (this.userSearchQuery.length < 1) {
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
                const me = this;
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
            openConfirmBookingModal($bookingId, event) {
                const btn = event?.currentTarget || null;

                bookingCoreApp.showConfirm({
                    message: 'Вы уверены, что хотите подтвердить бронь?',
                    callback: (result) => {
                        if (!result) return;
                        if (btn) bc_button_loading(btn, true);

                        $.ajax({
                            url: `/booking/${$bookingId}/confirm`,
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content') || ''
                            },
                            success: (res) => {
                                if (res.status) {
                                    if (btn) bc_button_loading(btn, false);
                                    bookingCoreApp.showAjaxMessage(res);
                                    setTimeout(function () {window.location.reload()}, 1000);
                                }
                            },
                            error: function (e) {
                                if (btn) bc_button_loading(btn, false);
                                bookingCoreApp.showError({ message: 'Ошибка подтверждения брони' });
                            }
                        });
                    }
                });
            },
            openCancelBookingModal($bookingId, event) {
                const btn = event?.currentTarget || null;

                bookingCoreApp.showConfirm({
                    message: 'Вы уверены, что хотите отменить бронь?',
                    callback: (result) => {
                        if (!result) return;
                        if (btn) bc_button_loading(btn, true);

                        $.ajax({
                            url: `/booking/${$bookingId}/cancel`,
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content') || ''
                            },
                            success: (res) => {
                                if (res.status) {
                                    if (btn) bc_button_loading(btn, false);
                                    bookingCoreApp.showAjaxMessage(res);
                                    setTimeout(function () {window.location.reload()}, 1000);
                                }
                            },
                            error: function (e) {
                                if (btn) bc_button_loading(btn, false);
                                bookingCoreApp.showError({ message: 'Ошибка отмены брони' });
                            }
                        });
                    }
                });
            },
            completeBooking(event, bookingId) {
                const me = this;
                const bookingIdNum = parseInt(bookingId, 10);

                const btn = event?.currentTarget || null;
                if (btn) bc_button_loading(btn, true);

                $.ajax({
                    url: `/booking/${bookingIdNum}/complete`,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content') || ''
                    },
                    success: function (res) {
                        bc_button_loading(btn, false);

                        if (res.status) {
                            bookingCoreApp.showAjaxMessage(res);
                            setTimeout(function () {window.location.reload()}, 1000);
                        } else if (res.message) {
                            bookingCoreApp.showAjaxMessage(res);
                        }
                    },
                    error: function (e) {
                        bc_button_loading(btn, false);

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
            copyBookingLink(link) {
                navigator.clipboard.writeText(link)
                    .then(() => {
                        bookingCoreApp.showAjaxMessage({
                            message: 'Ссылка скопирована',
                            status: true
                        });
                    })
                    .catch(err => {
                        console.error('Ошибка копирования:', err);
                    });
            },
            startCollection(event, bookingId) {
                const bookingIdNum = parseInt(bookingId, 10);
                const btn = event?.currentTarget || null;
                if (btn) bc_button_loading(btn, true);

                $.ajax({
                    url: `/booking/${bookingIdNum}/start-collection`,
                    type: 'POST',
                    dataType: 'json',
                    data: {},
                    success: function (res) {
                        bc_button_loading(btn, false);

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
                        bc_button_loading(btn, false);

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
                const me = this;
                const bookingIdNum = parseInt(bookingId, 10);
                const btn = event?.currentTarget || null;

                bookingCoreApp.showConfirm({
                    message: 'Вы уверены, что хотите отменить сбор?',
                    callback: (result) => {
                        if (!result) return;
                        if (btn) bc_button_loading(btn, true);

                        $.ajax({
                            url: `/booking/${bookingIdNum}/cancel-collection`,
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content') || ''
                            },
                            success: function (res) {
                                if (res.status) {
                                    const modal = bootstrap.Modal.getInstance(document.getElementById('collectionModal' + bookingIdNum));
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

                                    setTimeout(function () {window.location.reload()}, 500);
                                } else if (res.message) {
                                    bookingCoreApp.showAjaxMessage(res);
                                }
                            },
                            error: function (e) {
                                bc_button_loading(btn, false);
                                bookingCoreApp.showError({ message: 'Произошла ошибка при отмене сбора охотников' });
                            }
                        });
                    }
                });
            },
            finishCollection(event, bookingId) {
                const bookingIdNum = parseInt(bookingId, 10);
                const btn = event?.currentTarget || null;

                bookingCoreApp.showConfirm({
                    message: 'Вы уверены, что хотите завершить сбор?',
                    callback: (result) => {
                        if (!result) return;
                        if (btn) bc_button_loading(btn, true);

                        $.ajax({
                            url: `/booking/${bookingIdNum}/finish-collection`,
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content') || ''
                            },
                            success: function (res) {
                                if (res.status) {
                                    var modal = bootstrap.Modal.getInstance(document.getElementById('collectionModal' + bookingIdNum));
                                    if (modal) {
                                        modal.hide();
                                    }
                                    bookingCoreApp.showAjaxMessage(res);
                                    setTimeout(function () {window.location.reload()}, 500);
                                } else if (res.message) {
                                    bookingCoreApp.showAjaxMessage(res);
                                }
                            },
                            error: function (e) {
                                bc_button_loading(btn, false);

                                if (e.status === 419) {
                                    alert('Сессия истекла, обновите страницу');
                                } else if (e.responseJSON && e.responseJSON.message) {
                                    bookingCoreApp.showAjaxMessage(e.responseJSON);
                                } else {
                                    alert('Произошла ошибка при завершении сбора охотников');
                                }
                            }
                        });
                    }
                });
            },
            cancelBooking(event, bookingId) {
                const bookingIdNum = parseInt(bookingId, 10);
                const btn = event?.currentTarget || null;
                if (btn) bc_button_loading(btn, true);

                $.ajax({
                    url: `/booking/${bookingIdNum}/cancel`,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content') || ''
                    },
                    success: function (res) {
                        if (res.status) {
                            var modal = bootstrap.Modal.getInstance(document.getElementById('cancelBookingModal' + bookingIdNum));
                            if (modal) {
                                modal.hide();
                            }
                            bookingCoreApp.showAjaxMessage(res);
                            setTimeout(function () {window.location.reload()}, 500);
                        } else if (res.message) {
                            bookingCoreApp.showAjaxMessage(res);
                        }
                    },
                    error: function (e) {
                        bc_button_loading(btn, false);

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
            bookingPrepaymentPaid(bookingId, event) {
                if (!bookingId) return;
                if (this.prepaymentPaidMap[bookingId]) return;

                const btn = event.currentTarget;
                if (btn) bc_button_loading(btn, true);

                $.ajax({
                    url: `/booking/${bookingId}/prepayment-paid`,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content') || ''
                    },
                    success: function (res) {
                        if (res.status) {
                                if (res?.payment_url) {
                                    window.open(res.payment_url, '_blank');
                                }
                                // this.$set(this.prepaymentPaidMap, bookingId, true);
                        }
                    },
                    error: function (e) {
                        bc_button_loading(btn, false);
                        bookingCoreApp.showError({ message: 'Произошла ошибка при отмене сбора охотников' });
                    }
                });
            },
            searchReplaceHunter(bookingId) {
                if (!this.replaceQuery || this.replaceQuery.trim() === '') {
                    this.replaceResults = [];
                    return;
                }

                this.isSearchingReplace = true;
                this.showReplaceResults = true;

                clearTimeout(this.replaceDebounce);

                this.replaceDebounce = setTimeout(() => {
                    fetch(`/user/search-hunters?query=${encodeURIComponent(this.replaceQuery)}&booking_id=${bookingId}`)
                        .then(res => res.json())
                        .then(users => {
                            users.forEach(u => {
                                if (typeof u.invited === 'undefined') u.invited = false;
                                if (typeof u.invitation_status === 'undefined') u.invitation_status = null;
                            });

                            this.replaceResults = users;
                        })
                        .finally(() => {
                            this.isSearchingReplace = false;
                        });
                }, 300);
            },
            selectReplaceHunter(user) {
                this.replaceQuery = user.user_name
                    ? user.user_name
                    : (user.first_name || user.last_name)
                        ? `${user.first_name || ''} ${user.last_name || ''}`.trim()
                        : user.email;

                this.selectedReplaceHunter = user;
                this.showReplaceResults = false;
            },
            async confirmReplace(oldHunterId, bookingId) {
                if (!this.selectedReplaceHunter) {
                    bookingCoreApp.showAjaxMessage({
                        status: false,
                        message: 'Выберите охотника из списка'
                    });
                    return;
                }

                $.ajax({
                    url: `/booking/${bookingId}/replace-hunter`,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        old_hunter_id: oldHunterId,
                        hunter: this.selectedReplaceHunter,
                        _token: $('meta[name="csrf-token"]').attr('content') || ''
                    },
                    success: (res) => {
                        if (res.status) {
                            const newHunter = res.hunter;
                            const index = this.invitedHunters.findIndex(h => h.id === oldHunterId);
                            if (index !== -1) {
                                this.$set(this.invitedHunters, index, newHunter);
                            }
                            const slotIndex = this.hunterSlots.findIndex(s => s.hunter && s.hunter.id === oldHunterId);
                            if (slotIndex !== -1) {
                                this.$set(this.hunterSlots, slotIndex, {
                                    ...this.hunterSlots[slotIndex],
                                    hunter: newHunter,
                                    query: newHunter.user_name || (newHunter.first_name + ' ' + newHunter.last_name).trim() || newHunter.email
                                });
                            }
                            this.clearReplace();
                        } else {
                            bookingCoreApp.showError(res.message);
                        }
                    },
                    error: function(e) {
                        console.error(e);
                    }
                });
            },
            cancelReplace() {
                this.clearReplace()
            },
            clearReplace() {
                this.hunterToReplace = false
                this.showReplaceResults = false
                this.replaceQuery = ''
                this.replaceResults = []
            },
            removeHunter(hunterId, bookingId) {
                bookingCoreApp.showConfirm({
                    message: 'Удалить охотника?',
                    callback: (result) => {
                        if (!result) return;

                        const self = this;

                        $.ajax({
                            url: `/booking/${bookingId}/remove/not_paid/hunter`,
                            type: 'DELETE',
                            dataType: 'json',
                            data: {
                                hunter_id: hunterId,
                                _token: $('meta[name="csrf-token"]').attr('content') || ''
                            },
                            success: (res) => {
                                if (res.status) {
                                    bookingCoreApp.showAjaxMessage(res);
                                    this.invitedHunters = this.invitedHunters.filter(h => h.id !== hunterId);
                                } else {
                                    bookingCoreApp.showError({ message: 'Ошибка удаления охотника' });
                                }
                            },
                            error: function() {
                                bookingCoreApp.showError({ message: 'Ошибка удаления охотника' });
                            }
                        });
                    }
                });
            },
            openBookingPlacesModal(booking, event) {
                window.openModal('placeBookingModal', booking.id);
                this.loadBookingPlaces(booking, event);
            },

            // Метод загрузки данных занятых мест
            loadBookingPlaces(booking, event = null) {
                const btn = event?.currentTarget || null;
                if (btn) bc_button_loading(btn, true);

                const self = this;

                $.post(`/booking/${booking.id}/places`)
                    .done(res => {
                        bc_button_loading(btn, false);

                        if (!res.rooms || !res.places) {
                            alert('Ошибка получения данных');
                            return;
                        }

                        self.placesMap = res.places || {};
                        self.currentUserId = res.current_user_id || null;

                        const modalEl = document.getElementById('placeBookingModal' + booking.id);
                        if (!modalEl) return;

                        const contentEl = modalEl.querySelector('#booking-places-content-' + booking.id);
                        if (!contentEl) return;

                        contentEl.innerHTML = '';
                        self.renderBookingPlaces(booking, res.rooms, self.placesMap, contentEl);

                    })
                    .fail(() => {
                        bc_button_loading(btn, false);
                        alert('Ошибка при запросе к серверу');
                    });
            },

            // Метод рендера комнат и мест
            renderBookingPlaces(booking, rooms, placesMap, contentEl) {
                const self = this;

                rooms.forEach(room => {
                    const block = document.createElement('div');
                    block.className = 'mb-2 p-3 border border-2 rounded shadow-sm';

                    const header = document.createElement('h5');
                    header.textContent = room.title;
                    header.style.textAlign = 'center';
                    header.style.marginBottom = '15px';
                    block.appendChild(header);

                    const roomId = room.room_id;
                    const roomsCount = room.booking_number;
                    const placesPerRoom = room.number;

                    for (let roomIndex = 1; roomIndex <= roomsCount; roomIndex++) {
                        const roomHeader = document.createElement('h6');
                        roomHeader.textContent = `${room.title} №${roomIndex}`;
                        roomHeader.style.marginTop = '10px';
                        roomHeader.style.marginBottom = '8px';
                        block.appendChild(roomHeader);

                        const list = document.createElement('ul');
                        list.style.listStyle = 'none';
                        list.style.padding = '0';
                        list.style.marginBottom = '15px';

                        for (let placeNumber = 1; placeNumber <= placesPerRoom; placeNumber++) {
                            const placeData = placesMap[roomIndex]?.[roomId]?.[placeNumber]?.[0] ?? null;

                            const li = document.createElement('li');
                            li.className = 'guest-slot d-flex align-items-center justify-content-between px-2 py-1 border rounded mb-2';
                            li.style.minHeight = '35px';
                            li.dataset.roomIndex = roomIndex;

                            // Левый текст: номер места
                            const textDiv = document.createElement('div');
                            textDiv.textContent = `место ${placeNumber}`;
                            textDiv.className = 'text-muted fw-bold';
                            li.appendChild(textDiv);

                            // Центр: имя пользователя или свободно
                            const inputDiv = document.createElement('div');
                            inputDiv.style.flex = '1';
                            inputDiv.style.textAlign = 'center';
                            if (placeData) {
                                const firstName = placeData.user.first_name ?? '';
                                const lastName = placeData.user.last_name ?? '';
                                const username = placeData.user.user_name ?? '';
                                inputDiv.textContent = (firstName || lastName)
                                    ? `${firstName} ${lastName}`.trim()
                                    : username;
                                inputDiv.className = 'fw-semibold text-success';
                            } else {
                                inputDiv.textContent = 'свободно';
                                inputDiv.className = 'text-muted';
                            }
                            li.appendChild(inputDiv);

                            // Кнопка справа
                            if (!booking.is_all_places_assigned) {
                                const button = document.createElement('button');
                                button.type = 'button';
                                button.className = 'btn btn-sm';

                                if (placeData) {
                                    if (placeData.user_id === self.currentUserId) {
                                        button.textContent = 'Вы выбрали';
                                        button.classList.add('btn-success');
                                        button.addEventListener('click', () => {
                                            self.cancelSelectPlace(booking.id, placeData.id);
                                        });
                                    } else {
                                        button.textContent = 'Отменить';
                                        button.classList.add('btn-danger');
                                        button.addEventListener('click', () => {
                                            self.cancelSelectPlace(booking.id, placeData.id);
                                        });
                                    }
                                } else {
                                    button.textContent = 'Выбрать';
                                    button.classList.add('btn-primary');
                                    button.addEventListener('click', () => {
                                        self.selectPlace(booking.id, roomId, placeNumber, roomIndex)
                                            .then(() => {
                                                self.loadBookingPlaces(booking);
                                            });
                                    });
                                }

                                li.appendChild(button);
                            }

                            list.appendChild(li);
                        }

                        block.appendChild(list);
                    }

                    contentEl.appendChild(block);
                });
            },

            selectPlace(bookingId, roomId, placeNumber, roomIndex) {
                const self = this;

                 return $.post(`/booking/${bookingId}/select-place`, {
                    room_id: roomId,
                    place_number: placeNumber,
                    room_index: roomIndex
                })
                     .done(function(res) {
                         if (res.success) {
                             const booking = { id: bookingId };
                             self.loadBookingPlaces(booking);
                         } else {
                             bookingCoreApp.showAjaxMessage(res);
                         }
                     })
                     .fail(function(xhr) {
                         bookingCoreApp.showAjaxMessage({
                             status: false,
                             message: 'Ошибка выбора места'
                         });
                     });
            },

            cancelSelectPlace(bookingId, placeId) {
                const self = this;

                $.post(`/booking/${bookingId}/cancel-select-place`, {
                    place_id: placeId
                })
                    .done(function() {
                        const booking = { id: bookingId };
                        self.loadBookingPlaces(booking);
                    })
                    .fail(function() {
                        alert('Ошибка отмены места');
                    });
            },

            openCalculatingModal(booking, event) {
                window.openModal('calculatingBookingModal', booking.id);
                this.loadCalculatingData(booking);
            },

            loadCalculatingData(booking) {
                const bookingIdNum = parseInt(booking.id, 10);
                const self = this;

                return $.get(`/booking/${booking.id}/calculating`)
                    .done(res => {
                        if (!res.status) {
                            alert('Ошибка получения данных');
                            return;
                        }

                        self.calculatingData = res;

                        const modalEl = document.getElementById('calculatingBookingModal' + bookingIdNum);
                        if (!modalEl) return;

                        const contentEl = modalEl.querySelector('#calculating-content-' + bookingIdNum);
                        if (!contentEl) return;

                        self.renderCalculatingData(booking, contentEl, res);
                    })
                    .fail(() => {
                        alert('Ошибка при запросе к серверу');
                    });
            },
            renderCalculatingData(booking, contentEl, res) {
                const places = res.places ?? {};
                const is_baseAdmin = res.is_baseAdmin ?? false;

                let html = `<table class="table table-bordered">
<thead>
    <tr class="table-secondary">
        <th>Услуги</th>
        <th>Всего расходы</th>
        <th>Мои расходы</th>
    </tr>
</thead>
<tbody>`;

                html += `<tr><td colspan="3"></td></tr>`;

                // === Основные услуги ===
                (res.items || []).forEach(item => {
                    html += `
<tr>
    <td>${item.name}</td>
    <td>${item.total_cost ?? 0}</td>
<td>
    ${item.my_cost ?? 0}
    ${item.has_tooltip
                        ? `<span 
                class="info-icon"
                data-bs-toggle="popover"
                data-bs-trigger="focus"
                data-bs-placement="top"
                data-bs-content="За человека в сутки"
                tabindex="0"
                style="color:red; cursor:pointer;"
           >!</span>`
                        : ''}
</td>
</tr>`;
                });

                // === Трофеи ===
                html += `<tr class="table-secondary"><td colspan="3"><strong>Трофеи</strong></td></tr>`;
                (res.trophies || []).forEach(item => {
                    html += `
<tr>
    <td>${item.name}</td>
    <td>${item.total_cost ?? 0}</td>
    <td>${item.my_cost ?? 0}</td>
</tr>`;
                });

                // === Штрафы ===
                html += `<tr class="table-secondary"><td colspan="3"><strong>Штрафы</strong></td></tr>`;
                (res.penalties || []).forEach(item => {
                    html += `
<tr>
    <td>${item.name}</td>
    <td>${item.total_cost ?? 0}</td>
    <td>${item.my_cost ?? 0}</td>
</tr>`;
                });

                // === Дополнительные услуги ===
                html += `<tr class="table-secondary"><td colspan="3"><strong>Доп. услуги</strong></td></tr>`;
                (res.meals || []).concat(res.preparation || []).concat(res.addetionals || []).forEach(item => {
                    html += `
<tr>
    <td>${item.name}</td>
    <td>${item.total_cost ?? 0}</td>
    <td>${item.my_cost ?? 0}</td>
</tr>`;
                });

                // === Расходы охотников ===
                if (!is_baseAdmin) {
                    html += `
<tr class="table-secondary">
    <td><strong>Расходы охотников</strong></td>
    <td></td>
    <td><strong style="color:red;">Я должен</strong></td>
</tr>`;
                    (res.spendings || []).forEach(item => {
                        html += `
<tr>
    <td>${item.name}</td>
    <td>${item.total_cost ?? 0}</td>
    <td>${item.my_cost ?? 0}</td>
</tr>`;
                    });
                }

                // === Подытог ===
                html += `<tr><td colspan="3"></td></tr>`;
                (res.all_items || []).forEach(item => {
                    html += `
<tr>
    <td>${item.name}</td>
    <td>${item.total_cost ?? 0}</td>
    <td>${item.my_cost ?? 0}</td>
</tr>`;
                });

                html += `</tbody></table>`;
                contentEl.innerHTML = html;
                contentEl.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
                    new bootstrap.Popover(el);
                });
            }
        },

        mounted() {
            const me = this;

            document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
                new bootstrap.Popover(el);
            });

            document.addEventListener('shown.bs.modal', function (event) {
                const modalEl = event.target;
                if (modalEl && modalEl.id && modalEl.id.startsWith('collectionModal')) {
                    const bookingId = modalEl.dataset.bookingId;
                    if (bookingId) {
                        setTimeout(() => {
                            me.initializeHunterSlots(parseInt(bookingId, 10));
                        }, 50);
                    }
                }
            });
            document.addEventListener('hide.bs.modal', function (event) {
                const modalEl = event.target;

                if (modalEl && modalEl.id && modalEl.id.startsWith('collectionModal')) {
                    const bookingId = modalEl.dataset.bookingId;

                    if (bookingId) {
                        window.location.reload();
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
                //console.log('[HunterInvitation] Подписка на канал для пользователя:', userId);

                try {
                    const channel = window.LaravelEcho.private(`user-channel-${userId}`);

                    channel.listen('.hunter.invited', (e) => {
                        console.log('[HunterInvitation] Получено событие приглашения:', e);
                        // Обновляем страницу истории бронирований
                        if (window.location.pathname.includes('booking-history')) {
                            setTimeout(() => {
                                location.reload();
                            }, 500);
                        }
                    });

                    channel.subscribed(() => {
                        //console.log('[HunterInvitation] ✅ Успешно подписан на канал user-channel-' + userId);
                    });

                    channel.error((error) => {
                        //console.error('[HunterInvitation] ❌ Ошибка подписки на канал:', error);
                    });
                } catch (e) {
                    //console.error('[HunterInvitation] ❌ Исключение при подписке:', e);
                }
            } else {
                //console.warn('[HunterInvitation] ⚠️ Не удалось подписаться. userId:', userId, 'LaravelEcho:', !!window.LaravelEcho, 'Element:', !!el);
            }

            // Подписка на каналы бронирований для обновления счетчика в реальном времени
            if (window.LaravelEcho) {
                const subscribedBookings = new Set();
                const subscribeToBooking = (bookingId) => {
                    if (!bookingId || subscribedBookings.has(bookingId)) {
                        return;
                    }

                    subscribedBookings.add(bookingId);

                    try {
                        const channel = window.LaravelEcho.private(`booking-${bookingId}`);

                        channel.listen('.hunter.invitation.accepted', (e) => {
                            // console.log('[HunterInvitationAccepted] Получено событие принятия приглашения:', e);

                            const targetRow = document.querySelector(`tr[data-booking-id="${bookingId}"]`);
                            if (targetRow) {
                                const statusCell = Array.from(targetRow.querySelectorAll('td')).find(td => {
                                    return td.querySelector('.collection-timer') ||
                                        td.classList.toString().includes('START_COLLECTION');
                                });

                                if (statusCell) {
                                    const counterElement = statusCell.querySelector('.text-muted.mt-1');
                                    if (counterElement && e.accepted_count !== undefined && e.total_hunters_needed !== undefined) {
                                        counterElement.textContent = `Собранно ${e.accepted_count}/${e.total_hunters_needed}`;
                                        //console.log('[HunterInvitationAccepted] Счетчик обновлен:', counterElement.textContent);
                                    }
                                }
                            }
                        });

                        channel.subscribed(() => {
                            //console.log(`[HunterInvitationAccepted] ✅ Успешно подписан на канал booking-${bookingId}`);
                        });

                        channel.error((error) => {
                            //console.error(`[HunterInvitationAccepted] ❌ Ошибка подписки на канал booking-${bookingId}:`, error);
                        });
                    } catch (e) {
                        //console.error(`[HunterInvitationAccepted] ❌ Исключение при подписке на booking-${bookingId}:`, e);
                    }
                };

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

            document.querySelectorAll('.modal[id^="collectionModal"]').forEach((modalEl) => {
                if (modalEl.dataset.handlerCleared) return;
                modalEl.dataset.handlerCleared = 'true';

                modalEl.addEventListener('hidden.bs.modal', () => {
                    me.hunterSearchQuery = '';
                    me.hunterSearchResults = [];
                    me.hunterNoResults = false;
                });
            });

            const updateCollectionTimers = () => {
                const nodesWithEnd = document.querySelectorAll('.collection-timer[data-end]');
                const now = Date.now();
                window.collectionState = window.collectionState || {};

                nodesWithEnd.forEach(el => {
                    const end = parseInt(el.dataset.end, 10);
                    if (!end) return;

                    const bookingId = el.dataset.bookingId;
                    if (!bookingId) return;

                    let diffMs = end - now;

                    const extendBtn = document.querySelector('.btn-extend-collection[data-booking-id="' + bookingId + '"]');
                    const btn = document.getElementById(`accept-btn-${bookingId}`);


                    // Таймер закончился
                    if (diffMs <= 0) {

                            if (extendBtn) {
                                extendBtn.disabled = false;
                                extendBtn.classList.remove('disabled');
                            }

                            if (el.dataset.collectionExpired === '1') return;
                            el.dataset.collectionExpired = '1';

                            // Запрещаем приглашать охотников - нужно продлить сбор
                            const bookingKey = String(bookingId);
                            window.collectionState[bookingKey] = true;

                        return;
                    }

                    if (el.dataset.collectionExpired) {
                        delete el.dataset.collectionExpired;
                        delete window.collectionState[String(bookingId)];
                    }

                    el.textContent = this.formatTimer(diffMs);

                        // Пока таймер идет — кнопка продления должна быть неактивна
                        if (extendBtn) {
                            extendBtn.disabled = true;
                            extendBtn.classList.add('disabled');
                        }
                });
            };

            const updatePaidTimers = () => {
                const nodes = document.querySelectorAll('.paid-timer[data-end]');
                const now = Date.now();

                nodes.forEach(el => {
                    const end = parseInt(el.dataset.end, 10);
                    if (!end) return;

                    const bookingId = el.dataset.bookingId;
                    if (!bookingId) return;

                    let diffMs = end - now;

                    if (diffMs <= 0) {
                        if (el.dataset.paidExpired === '1') return;
                        el.dataset.paidExpired = '1';

                        handleBookingPaidTimerExpired(bookingId);
                        return;
                    }
                    if (el.dataset.paidExpired) {
                        delete el.dataset.paidExpired;
                    }

                    el.textContent = this.formatTimer(diffMs);
                });
            };
            const handleBookingPaidTimerExpired = (bookingId) => {
                $.ajax({
                    url: `/booking/${bookingId}/check/prepayment-paid`,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content') || '',
                        booking_id: bookingId
                    },
                    success: function (res) {

                        if (res.status) {


                            // if (typeof bookingCoreApp !== 'undefined' && bookingCoreApp.showAjaxMessage) {
                            //     bookingCoreApp.showAjaxMessage(res);
                            // } else if (res.message) {
                            //     alert(res.message);
                            // }

                            // setTimeout(function () {
                            //     window.location.reload();
                            // }, 500);
                        } else if (res.message) {
                            // if (typeof bookingCoreApp !== 'undefined' && bookingCoreApp.showAjaxMessage) {
                            //     bookingCoreApp.showAjaxMessage(res);
                            // } else {
                            //     alert(res.message);
                            // }
                        }
                    },
                    // error: function (e) {
                    //
                    //     if (e.status === 419) {
                    //         alert('Сессия истекла, обновите страницу');
                    //     } else if (e.responseJSON && e.responseJSON.message) {
                    //         alert('Ошибка: ' + e.responseJSON.message);
                    //     } else {
                    //         alert('Произошла ошибка при отмене сбора охотников');
                    //     }
                    // }
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
                    el.textContent = this.formatTimer(diffMs);
                });
            };

            // Обновляем таймер каждую секунду, чтобы он работал "в реальном времени"
            updateCollectionTimers(); updatePaidTimers(); updateBedsTimers();
            setInterval(() => {updateCollectionTimers(); updatePaidTimers(); updateBedsTimers()}, 1000);

            //Кнопка оплата или оплачено в модальном окне предоплата
            document.querySelectorAll('.btn-prepayment').forEach(btn => {
                const bookingId = btn.dataset.bookingId;
                const isPaid = btn.dataset.prepaymentPaid === '1' || btn.dataset.prepaymentPaid === 'true';
                this.$set(this.prepaymentPaidMap, bookingId, isPaid);
            });
        },
    });
});
