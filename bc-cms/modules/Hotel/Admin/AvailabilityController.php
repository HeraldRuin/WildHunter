<?php

namespace Modules\Hotel\Admin;

use Illuminate\Support\Facades\Auth;
use Modules\Hotel\Services\AddDataInView;
use Modules\Hotel\Services\RoomAvailabilityService;

class AvailabilityController extends \Modules\Hotel\Controllers\AvailabilityController
{
    protected string $indexView = 'Hotel::admin.room.availability';

    public function __construct(AddDataInView $cabinetService, RoomAvailabilityService $roomAvailabilityService)
    {
        parent::__construct($cabinetService, $roomAvailabilityService);
        $this->setActiveMenu(route('hotel.admin.index'));
        $this->middleware('dashboard');
    }

    protected function hasHotelPermission($hotel_id = false): bool
    {
        if(empty($hotel_id)) return false;

        $hotel = $this->hotelClass::find($hotel_id);
        if(empty($hotel)) return false;

        if(!$this->hasPermission('hotel_manage_others') and $hotel->author_id != Auth::id()){
            return false;
        }

        $this->currentHotel = $hotel;
        return true;
    }
}
