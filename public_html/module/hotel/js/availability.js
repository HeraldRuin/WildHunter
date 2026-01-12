(function() {
    'use strict';
    function initAvailabilityAutoRefresh() {
        if (typeof window.LaravelEcho === 'undefined') {
            console.warn('LaravelEcho не найден. Автообновление календаря недоступно.');
            return;
        }

        function refreshCalendar() {
            if (typeof window.calendar !== 'undefined' && window.calendar) {
                try {
                    window.calendar.refetchEvents();
                    // console.log('📅 Календарь доступности обновлен');
                } catch (error) {
                    // console.error('Ошибка при обновлении календаря:', error);
                    location.reload();
                }
            } else {
                // console.log('📅 Календарь не найден, перезагружаем страницу');
                location.reload();
            }
        }
        window.LaravelEcho.channel('booking')
            .listen('.booking.created', (e) => {
                refreshCalendar();
            })
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAvailabilityAutoRefresh);
    } else {
        initAvailabilityAutoRefresh();
    }
})();
