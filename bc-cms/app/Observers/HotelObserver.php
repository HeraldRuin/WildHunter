<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use Modules\Attendance\Models\AddetionalPrice;

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
                    'price'    => 0,
                    'user_id'  => $authUser->id,
                    'hotel_id' => $hotel->id ?? null,
                ]);
            }
        }
    }
}
