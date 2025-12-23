<?php
namespace Modules\Weapon\Admin;

use Modules\Weapon\Models\Caliber;
use Modules\Animals\Models\AnimalDate;
use Modules\Booking\Models\Booking;

class CaliberController extends \Modules\Weapon\Controllers\CaliberController
{
    protected $caliber;
    protected $animalDateClass;
    protected $bookingClass;
    protected $indexView = 'Weapon::admin.caliber.caliber';

    public function __construct(Caliber $caliber, AnimalDate $animalDateClass, Booking $bookingClass)
    {
        $this->setActiveMenu(route('animal.admin.index'));
        $this->middleware('dashboard');
        $this->caliber = $caliber;
        $this->animalDateClass = $animalDateClass;
        $this->bookingClass = $bookingClass;
    }

}
