<?php

namespace Modules\Animals\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Kalnoy\Nestedset\NodeTrait;
use Modules\Booking\Models\Bookable;
use Modules\Hotel\Models\Hotel;

class Animal extends Bookable
{


    protected $table = 'bc_animals';

    protected $fillable = [
        'title',
        'content',
        'status',
        'faqs',

    ];

    public static function isEnable(): bool
    {
        return setting_item('animal_disable') == false;
    }

    public function getNumberReviewsInService($status = false)
    {
        return $this->reviewClass::countReviewByServiceID($this->id, false, $status, $this->type) ?? 0;
    }
    public function hotels(): BelongsToMany
    {
        return $this->belongsToMany(Hotel::class, 'bc_hotel_animals','animal_id','hotel_id')->withPivot('status');
    }
}

