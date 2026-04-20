<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use Modules\Attendance\Models\AddetionalPrice;
use Modules\Booking\Models\BookingCounter;

class HotelObserver
{
    public function created($hotel): void
    {
        $authUser = Auth::user();

        if ($authUser && $authUser->hasRole('baseadmin')) {
            $exists = AddetionalPrice::where('user_id', $authUser->id)
                ->where('name', 'Питание')
                ->exists();

            if (!$exists) {
                AddetionalPrice::create([
                    'name'     => 'Питание',
                    'type'    => 'food',
                    'price'    => 0,
                    'user_id'  => $authUser->id,
                    'hotel_id' => $hotel->id ?? null,
                ]);
            }
        }

        BookingCounter::firstOrCreate(
            ['hotel_id' => $hotel->id],
            ['last_number' => 0]
        );
    }
}
