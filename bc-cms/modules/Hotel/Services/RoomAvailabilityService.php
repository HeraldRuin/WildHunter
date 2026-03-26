<?php

namespace Modules\Hotel\Services;

use Carbon\Carbon;
use Modules\Booking\Models\Booking;
use Modules\Hotel\Models\HotelRoomDate;

class RoomAvailabilityService
{
    /**
     * @var string
     */
    protected string $roomDateClass;

    public function __construct()
    {
        $this->roomDateClass = HotelRoomDate::class;
    }

    public function getRoomCalendar($room, $request): array
    {
        $is_single = $request->query('for_single');

        /** ----------------------------------------
         * 1. Загружаем кастомные даты
         * ---------------------------------------- */
        $rows = $this->roomDateClass::query()
            ->where('target_id', $room->id)
            ->whereBetween('start_date', [
                date('Y-m-d 00:00:00', strtotime($request->query('start'))),
                date('Y-m-d 23:59:59', strtotime($request->query('end')))
            ])
            ->get()
            ->keyBy(fn ($row) => date('Y-m-d', strtotime($row->start_date)));

        /** ----------------------------------------
         * 2. Генерируем ВСЕ дни периода
         * ---------------------------------------- */
        $allDates = [];
        $period = periodDate($request->input('start'), $request->input('end'), false);

        foreach ($period as $dt) {
            $dateKey = $dt->format('Y-m-d');

            $allDates[$dateKey] = [
                'id' => uniqid(),
                'start' => $dateKey,
                'allDay' => true,

                'price' => $room->price,
                'number' => $room->number,
                'active' => 1,
                'extendedProps' => [
                    'max_number' => $room->number,
                ],
            ];

            $priceHtml = format_money($room->price);
            if (!$is_single) {
                $priceHtml = format_money_main($room->price);
            }

            $allDates[$dateKey]['title'] = $priceHtml . ' x ' . $room->number;
        }

        /** ----------------------------------------
         * 3. Мержим кастомные даты (НЕ затирая extendedProps)
         * ---------------------------------------- */
        foreach ($rows as $dateKey => $row) {
            $price = $row->price ?: $room->price;
            $number = ($row->number !== null) ? (int)$row->number : $room->number;

            $existing = $allDates[$dateKey];

            $isActive = (int) $row->active;
            $priceChanged = false;
            $numberChanged = false;

            if ($isActive) {
                $priceChanged = $row->price !== null && abs((float)$row->price - (float)$room->price) > 0.01;
                $numberChanged = $row->number !== null && (int)$row->number != (int)$room->number;
            }

            $title = '';
            if (!$isActive) {
                $title = __('Blocked');
            } elseif ($number == 0) {
                $title = __('Full Books');
            } else {
                $title = format_money_main($price) . ' x ' . $number;
            }

            $allDates[$dateKey] = array_merge(
                $existing,
                [
                    'price' => $price,
                    'number' => $number,
                    'active' => $isActive,
                    'classNames' => $isActive ? ['available-event'] : ['blocked-event'],
                    'title' => $title,
                ],
                [
                    'extendedProps' => array_merge(
                        $existing['extendedProps'],
                        [
                            'max_number' => $room->number,
                            'price_changed' => $priceChanged,
                            'number_changed' => $numberChanged,
                        ]
                    ),
                ]
            );
        }

        /** ----------------------------------------
         * 4. Учитываем бронирования с учётом дня выезда
         * ---------------------------------------- */
        $bookings = $room->getBookingsInRange(
            $request->query('start'),
            $request->query('end')
        );

        foreach ($bookings as $roomBooking) {
            $booking = Booking::find($roomBooking->booking_id);
            if (!$booking) continue;

            // -------------------------------
            // ИЗМЕНЕНИЕ: включаем день выезда в период для отображения
            // раньше был ->subDay(), теперь полный диапазон
            $period = periodDate(
                $roomBooking->start_date,
                $roomBooking->end_date,
                false
            );

            $endDate = Carbon::parse($roomBooking->end_date)->format('Y-m-d');

            foreach ($period as $dt) {
                $dateKey = $dt->format('Y-m-d');
                if (!isset($allDates[$dateKey])) continue;

                $day = &$allDates[$dateKey];

                // Добавляем бронь в массив
                $day['bookings'][] = [
                    'id' => $booking->id,
                    'code' => $booking->code,
                    'status' => $booking->status,
                    'statusName' => $booking->statusName,
                ];

                // -------------------------------
                // ИЗМЕНЕНИЕ: расчет свободных комнат только если день не день выезда
                if ($dateKey !== $endDate) {
                    $bookedRooms = (int)($roomBooking->number ?? 0);
                    $day['occupiedRooms'] = ($day['occupiedRooms'] ?? 0) + $bookedRooms;

                    $baseNumber = $day['extendedProps']['max_number'];
                    $freeRooms = max($baseNumber - ($day['occupiedRooms'] ?? 0), 0);

                    if ($freeRooms <= 0) {
                        $day['active'] = 1;
                        $day['number'] = 0;
                        $day['classNames'] = ['full-book-event'];
                        $day['title'] = __('Full Books');
                    } else {
                        $day['active'] = 1;
                        $day['number'] = $freeRooms;
                        $day['classNames'] = ['available-event'];
                        $day['title'] = format_money_main($day['price']) . ' x ' . $day['number'];
                    }
                } else {
                    $day['classNames'] = ['checkout-day-event'];
                    $day['title'] = format_money_main($day['price']) . ' x ' . $day['number'];
                }
            }
        }

        /** ----------------------------------------
         * 5. HTML для броней
         * ---------------------------------------- */
        foreach ($allDates as &$day) {
            if (empty($day['bookings'])) { continue; }
            $bookingHtml = '<div class="calendar-bookings">';
            foreach ($day['bookings'] as $b) {
                $status = htmlspecialchars($b['status'] ?? '');
                $code = htmlspecialchars($b['code'] ?? '');
                $label = htmlspecialchars($b['statusName'] ?? '');

                // Проверяем, день выезда ли это для этой брони
                $isCheckout = false;
                $bookingModel = Booking::find($b['id']);
                if ($bookingModel) {
                    $endDate = Carbon::parse($bookingModel->end_date)->format('Y-m-d');
                    if ($endDate === $day['start']) {
                        $isCheckout = true;
                    }
                }

                $bookingHtml .= '<div class="booking-item booking-status-' . $status . '">'
                    . '<span class="booking-id" data-id="' . (int)$b['id'] . '" data-code="' . e($b['code']) . '">'
                    . 'Б' . (int)$b['id'] .
                    '</span>'
                    . '<span class="booking-status">' . $label . '</span>';

                // Добавляем (Выезд) только рядом с этой бронью
                if ($isCheckout) {
                    $bookingHtml .= ' <span class="checkout-label">(В)</span>';
                }

                $bookingHtml .= '</div>';
            }

            $bookingHtml .= '</div>';
            $day['bookings_html'] = $bookingHtml;
        }
        unset($day);

        return array_values($allDates);
    }

    public function getSummaryCalendar($hotel_id, $request)
    {

    }
}
