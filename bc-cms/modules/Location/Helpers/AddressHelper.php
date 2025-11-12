<?php

namespace Modules\Location\Helpers;

use Illuminate\Support\Facades\Cache;

class AddressHelper
{
    public static function getStates($country)
    {
        return Cache::rememberForever("states_" . $country, function () use ($country) {
            return json_decode(file_get_contents(storage_path('app/data/states/' . $country . '.json')), true) ?? [];
        });
    }


    public static function getStateName($country, $stateCode)
    {
        $states = collect(self::getStates($country));

        return $states->where('state_code', $stateCode)->first()['name'] ?? $stateCode;
    }
}