<?php

namespace Modules\Animals\Models;

use Kalnoy\Nestedset\NodeTrait;
use Modules\Booking\Models\Bookable;

class Animal extends Bookable
{
    use NodeTrait;
    protected $table = 'bc_animals';

}

