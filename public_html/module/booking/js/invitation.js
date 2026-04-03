/**
 * Логика для работы с приглашениями охотников на сбор
 */

function acceptInvitation(bookingId) {
    bookingCoreApp.showConfirm({
        message: 'Вы уверены, что хотите принять это приглашение?',
        callback: (result) => {
            if (!result) return;

            $.ajax({
                url: `/booking/${bookingId}/accept-invitation`,
                type: 'POST',
                dataType: 'json',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content') || ''
                },
                success: function(res) {
                    if (res.status) {
                        window.closeModal('invitationModal', bookingId)
                        bookingCoreApp.showAjaxMessage(res);
                        setTimeout(function() {location.reload()}, 1000);
                    }
                },
                error: function (e) {
                    if (e.status === 419) {
                        alert('Сессия истекла, обновите страницу');
                    } else if (e.responseJSON && e.responseJSON.message) {
                        bookingCoreApp.showAjaxMessage(e.responseJSON);
                    }
                }
            });
        }
    });
}

function declineInvitation(bookingId) {
    bookingCoreApp.showConfirm({
        message: 'Вы уверены, что хотите отказаться от этого приглашения?',
        callback: (result) => {
            if (!result) return;

            $.ajax({
                url: `/booking/${bookingId}/decline-invitation`,
                type: 'POST',
                dataType: 'json',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content') || ''
                },
                success: function(res) {
                    if (res.status) {
                        window.closeModal('invitationModal', bookingId)
                        bookingCoreApp.showAjaxMessage(res);
                        setTimeout(function() {location.reload()}, 1000);
                    }
                },
                error: function (e) {
                    if (e.status === 419) {
                        alert('Сессия истекла, обновите страницу');
                    } else if (e.responseJSON && e.responseJSON.message) {
                        bookingCoreApp.showAjaxMessage(e.responseJSON);
                    }
                }
            });
        }
    });
}