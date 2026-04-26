<?php
return [
    'errors' => [
        'unknown_type' => 'Неизвестный тип бронирования: :type',
        'cannot_select_more_than_one_place' => 'Нельзя выбрать больше одного места',
        'The selected seat is already taken, try choosing a different one' => 'Выбранное место уже занято, попробуйте выбрать другое',
        'no_free_places_in_room' => 'В этой комнате свободных мест нет',
        'cancel_only_own_place' => 'Вы можете отменить только своё занятое место',
        'hunter_already_in_booking' => 'Такой охотник уже есть в списке этого бронирования',
        'user_not_found' => 'Такой охотник не найден',
        'master_not_found' => 'Мастер охотник не найден',
        'booking_not_confirmable' => 'Эта бронь уже подтверждена или недоступна для подтверждения',
        'booking_cannot_be_cancelled' => 'Эта бронь уже отменена или завершена и не доступна для отмены',
        'booking_access_denied' => 'Доступ запрещён',
        'booking_status_locked' => 'Статус бронирования заблокирован',
        'booking_invitation_not_found' => 'Дя этой брони не найден приглашенный',
    ],
    'successes' => [
        'place_selected' => 'Выбранное место занято за вами',
        'place_cancelled' => 'Выбранное место освобожденно',
        'hunter_removed' => 'Охотник успешно удален с этой охоты',
        'hunter_replace' => 'Охотник успешно заменён',
        'booking_cancelled' => 'Бронь успешно отменена',
        'booking_completed' => 'Бронь успешно завершена',
        'invitation_declined' => 'Приглашение отклонено',
        'invitation_accepted' => 'Приглашение принято',
        'customer_changed' => 'Заказчик изменён',
        'gathering_has_started' => 'Сбор охотников начат',
        'gathering_has_completed' => 'Сбор охотников завершён',
        'booking_confirmed' => 'Бронь успешно подтверждена',
        'hunter_gathering_cancelled' => 'Сбор охотников для этой брони отменён',
        'booking_invitation_sent' => 'Приглашение отправлено',
    ]
];
