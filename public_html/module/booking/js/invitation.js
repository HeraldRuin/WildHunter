/**
 * Логика для работы с приглашениями охотников на сбор
 */

// Получение переводов из data-атрибутов
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
    
    // Используем getAttribute для надежности (data-атрибуты с дефисами)
    return {
        acceptConfirm: el.getAttribute('data-accept-confirm') || 'Вы уверены, что хотите принять это приглашение?',
        declineConfirm: el.getAttribute('data-decline-confirm') || 'Вы уверены, что хотите отказаться от этого приглашения?',
        accepted: el.getAttribute('data-invitation-accepted') || 'Приглашение принято',
        declined: el.getAttribute('data-invitation-declined') || 'Приглашение отклонено'
    };
}

// Функция для открытия модального окна приглашения
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

// Функция для принятия приглашения
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
                setTimeout(function() {
                    location.reload();
                }, 500);
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

// Функция для отклонения приглашения
function declineInvitation(bookingId) {
    if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
        alert('Ошибка: jQuery не загружен');
        return;
    }
    
    const translations = getInvitationTranslations();
    const confirmMessage = translations.declineConfirm || 'Вы уверены, что хотите отказаться от этого приглашения?';
    
    if (!confirm(confirmMessage)) {
        return;
    }

    const csrfToken = $('meta[name="csrf-token"]').attr('content') || '';
    const url = `/booking/${bookingId}/decline-invitation`;
    
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
                    alert(res.message || translations.declined);
                }
                // Закрываем модальное окно и обновляем страницу
                if (typeof bootstrap !== 'undefined') {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('invitationModal' + bookingId));
                    if (modal) {
                        modal.hide();
                    }
                }
                setTimeout(function() {
                    location.reload();
                }, 500);
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
                alert('Произошла ошибка при отклонении приглашения');
            }
        }
    });
}

// Явно добавляем функции в глобальную область видимости (window)
// Это необходимо для доступа из onclick атрибутов в HTML
window.openInvitationModal = openInvitationModal;
window.acceptInvitation = acceptInvitation;
window.declineInvitation = declineInvitation;
