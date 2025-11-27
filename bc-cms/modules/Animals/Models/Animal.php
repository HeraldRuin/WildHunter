<?php

namespace Modules\Animals\Models;

use Kalnoy\Nestedset\NodeTrait;
use Modules\Booking\Models\Bookable;

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
}

