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
