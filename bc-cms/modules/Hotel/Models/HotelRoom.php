<?php

namespace Modules\Hotel\Models;

use Carbon\Carbon;
use ICal\ICal;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Modules\Booking\Models\Bookable;
use Modules\Booking\Models\BookedDay;
use Modules\Booking\Models\Booking;
use Modules\Core\Models\SEO;
use Modules\Media\Helpers\FileHelper;
use Modules\Review\Models\Review;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hotel\Models\HotelTranslation;
use Modules\User\Models\UserWishList;

class HotelRoom extends Bookable
{
    use SoftDeletes;
    protected $table = 'bc_hotel_rooms';
    public $type = 'hotel_room';
    public $availabilityClass = HotelRoomDate::class;
    protected $translation_class = HotelRoomTranslation::class;

    protected $fillable = [
        'title',
        'content',
        'status',
    ];

    protected $seo_type = 'hotel_room';


    protected $bookingClass;
    protected $roomDateClass;
    protected $hotelRoomTermClass;
    protected $roomBookingClass;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->bookingClass = Booking::class;
        $this->roomDateClass = HotelRoomDate::class;
        $this->hotelRoomTermClass = HotelRoomTerm::class;
        $this->roomBookingClass = HotelRoomBooking::class;
    }

    public static function getModelName()
    {
        return __("Hotel Room");
    }

    public static function getTableName()
    {
        return with(new static)->table;
    }


    public function terms(){
        return $this->hasMany($this->hotelRoomTermClass, "target_id");
    }

    public function isAvailableAt($filters = []): bool
    {
        if (empty($filters['start_date']) || empty($filters['end_date'])) {
            return true;
        }

        $filters['end_date'] = date("Y-m-d", strtotime($filters['end_date'] . " -1 day"));

        $allDates  = [];
        $tmp_night = 0;

        $period = periodDate($filters['start_date'], $filters['end_date'], true);

        foreach ($period as $dt) {
            $allDates[$dt->format('Y-m-d')] = [
                'number' => $this->number,
                'price'  => $this->price,
            ];
            $tmp_night++;
        }

        $roomDates = $this->getDatesInRange($filters['start_date'], $filters['end_date']);

        if (!empty($roomDates)) {
            foreach ($roomDates as $row) {
                if (!$row->active || !$row->number || !$row->price) {
                    return false;
                }

                $rowPeriod = periodDate(
                    date('Y-m-d', strtotime($row->start_date)),
                    date('Y-m-d', strtotime($row->end_date)),
                    false
                );

                foreach ($rowPeriod as $dt) {
                    $date = $dt->format('Y-m-d');
                    if (isset($allDates[$date])) {
                        $allDates[$date] = [
                            'number' => $row->number,
                            'price'  => $row->price,
                        ];
                    }
                }
            }
        }

        $roomBookings = $this->getBookingsInRange($filters['start_date'], $filters['end_date']);

        if (!empty($roomBookings)) {
            foreach ($roomBookings as $booking) {

                $bookingStart = date('Y-m-d', strtotime($booking->start_date));
                $bookingEnd   = date('Y-m-d', strtotime($booking->end_date));

                $bookingPeriod = periodDate($bookingStart, $bookingEnd, false);

                foreach ($bookingPeriod as $dt) {
                    $date = $dt->format('Y-m-d');

                    if (!isset($allDates[$date])) {
                        continue;
                    }

                    $allDates[$date]['number'] -= $booking->number;

                    if ($allDates[$date]['number'] <= 0) {
                        return false;
                    }
                }
            }
        }

        $this->tmp_number = min(array_column($allDates, 'number'));

        if ($this->tmp_number <= 0) {
            return false;
        }

//        if (!empty($filters['adults'])) {
//            $requested_adults = (int)$filters['adults'];
//            $max_adults_possible = $this->tmp_number * (int)$this->adults;
//
//            if ($requested_adults > $max_adults_possible) {
//                return false;
//            }
//        }

        $this->tmp_price  = array_sum(array_column($allDates, 'price'));
        $this->tmp_dates  = $allDates;
        $this->tmp_nights = $tmp_night;

        return true;
    }

    public function getDatesInRange($start_date,$end_date)
    {
        $query = $this->roomDateClass::query();
        $query->where('target_id',$this->id);
        $startTimestamp = date('Y-m-d 00:00:00', strtotime($start_date));
        $endTimestamp = date('Y-m-d 23:59:59', strtotime($end_date));

        $query->where('start_date', '<=', $endTimestamp)
              ->where('end_date', '>=', $startTimestamp);

        $results = $query->take(100)->get();

        return $results;
    }
    public function getBookingsInRange($from, $to)
    {
       return $this->roomBookingClass::query()
           ->where('bc_hotel_room_bookings.room_id',$this->id)
           ->active()
           ->inRange($from,$to)
           ->get(['bc_hotel_room_bookings.*']);
    }


    public function saveClone($newHotelId)
    {
        $old = $this;
        $selected_terms = $old->terms->pluck('term_id');
        $new = $old->replicate();
        $new->parent_id = $newHotelId;
        $new->save();
        foreach ($selected_terms as $term_id) {
            $this->hotelRoomTermClass::firstOrCreate([
                'term_id'   => $term_id,
                'target_id' => $new->id
            ]);
        }
        $langs = $this->translation_class::where("origin_id", $old->id)->get();
        if (!empty($langs)) {
            foreach ($langs as $lang) {
                $langNew = $lang->replicate();
                $langNew->origin_id = $new->id;
                $langNew->save();
                $langSeo = SEO::where('object_id', $lang->id)->where('object_model', $lang->getSeoType() . "_" . $lang->locale)->first();
                if (!empty($langSeo)) {
                    $langSeoNew = $langSeo->replicate();
                    $langSeoNew->object_id = $langNew->id;
                    $langSeoNew->save();
                }
            }
        }
        $metaSeo = SEO::where('object_id', $old->id)->where('object_model', $this->seo_type)->first();
        if (!empty($metaSeo)) {
            $metaSeoNew = $metaSeo->replicate();
            $metaSeoNew->object_id = $new->id;
            $metaSeoNew->save();
        }
    }

    public function bookings()
    {
        return $this->hasMany(HotelRoomBooking::class, 'room_id', 'id');
    }

    public function bookedDays(): HasMany
    {
        return $this->hasMany(BookedDay::class, 'room_id', 'id');
    }



}
