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
                data: {

                },
                success: (res) => {
                    if(res.status){
                        bookingCoreApp.showAjaxMessage(res);
                        window.location.reload();
                    }
                },
                error: (e) => {
                    alert('Ошибка подтверждения брони');
                }
            });
        }
    },

    mounted() {
        document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
            new bootstrap.Popover(el);
        });
    }
});
