/**
 * Логика для работы с приглашениями охотников на сбор
 */

function getInvitationTranslations() {
    const el = document.getElementById('booking-history');
    
    if (!el) {
        return {
            acceptConfirm: 'Вы уверены, что хотите принять это приглашение?',
            declineConfirm: 'Вы уверены, что хотите отказаться от этого приглашения?',
            accepted: 'Приглашение принято',
            declined: 'Приглашение отклонено'
        };
    }
    return {
        acceptConfirm: el.getAttribute('data-accept-confirm') || 'Вы уверены, что хотите принять это приглашение?',
        declineConfirm: el.getAttribute('data-decline-confirm') || 'Вы уверены, что хотите отказаться от этого приглашения?',
        accepted: el.getAttribute('data-invitation-accepted') || 'Приглашение принято',
        declined: el.getAttribute('data-invitation-declined') || 'Приглашение отклонено'
    };
}

function openInvitationModal(bookingId) {
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap не загружен');
        return;
    }
    const modalEl = document.getElementById('invitationModal' + bookingId);
    if (!modalEl) {
        console.error('Модальное окно не найдено: invitationModal' + bookingId);
        return;
    }
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

function acceptInvitation(bookingId) {
    if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
        alert('Ошибка: jQuery не загружен');
        return;
    }
    
    const translations = getInvitationTranslations();
    const confirmMessage = translations.acceptConfirm || 'Вы уверены, что хотите принять это приглашение?';
    
    if (!confirm(confirmMessage)) {
        return;
    }

    const csrfToken = $('meta[name="csrf-token"]').attr('content') || '';
    const url = `/booking/${bookingId}/accept-invitation`;
    
    $.ajax({
        url: url,
        type: 'POST',
        dataType: 'json',
        data: {
            _token: csrfToken
        },
        success: function(res) {
            if (res.status) {
                if (typeof bookingCoreApp !== 'undefined' && bookingCoreApp.showAjaxMessage) {
                    bookingCoreApp.showAjaxMessage(res);
                } else {
                    const translations = getInvitationTranslations();
                    alert(res.message || translations.accepted);
                }
                // Закрываем модальное окно и обновляем страницу
                if (typeof bootstrap !== 'undefined') {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('invitationModal' + bookingId));
                    if (modal) {
                        modal.hide();
                    }
                }
                setTimeout(function() {location.reload()}, 500);
            } else if (res.message) {
                alert(res.message);
            }
        },
        error: function(e) {
            if (e.status === 419) {
                alert('Сессия истекла, обновите страницу');
            } else if (e.responseJSON && e.responseJSON.message) {
                alert('Ошибка: ' + e.responseJSON.message);
            } else {
                alert('Произошла ошибка при принятии приглашения');
            }
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

window.openInvitationModal = openInvitationModal;
window.acceptInvitation = acceptInvitation;
window.declineInvitation = declineInvitation;
