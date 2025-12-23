<?php
namespace Modules\Animals\User;

use Modules\Animals\Models\Animal;
use Modules\Animals\Models\AnimalDate;
use Modules\Booking\Models\Booking;

class OrganisationController extends \Modules\Animals\Controllers\OrganisationController
{
    protected $animalClass;
    protected $animalDateClass;
    protected $bookingClass;
    protected $indexView = 'Animals::user.organisation';

    public function __construct(Animal $animalClass, AnimalDate $animalDateClass, Booking $bookingClass)
    {
        $this->setActiveMenu(route('animal.vendor.organisation'));
//        $this->middleware('dashboard');
        $this->animalClass = $animalClass;
        $this->animalDateClass = $animalDateClass;
        $this->bookingClass = $bookingClass;
    }

}
