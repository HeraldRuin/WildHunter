<?php
namespace Modules\Animals\Admin;

use Modules\Animals\Models\Animal;
use Modules\Animals\Models\AnimalDate;
use Modules\Booking\Models\Booking;

class AvailabilityController extends \Modules\Animals\Controllers\AvailabilityController
{
    protected $animalClass;
    protected $animalDateClass;
    protected $bookingClass;
    protected $indexView = 'Animals::admin.availability';

    public function __construct(Animal $animalClass, AnimalDate $animalDateClass, Booking $bookingClass)
    {
        $this->setActiveMenu(route('animal.admin.index'));
        $this->middleware('dashboard');
        $this->animalClass = $animalClass;
        $this->animalDateClass = $animalDateClass;
        $this->bookingClass = $bookingClass;
    }

}
