<?php
namespace Modules\Animals\user;

use Modules\Animals\Models\Animal;
use Modules\Animals\Models\AnimalDate;
use Modules\Booking\Models\Booking;

class OrganisationController extends \Modules\Animals\Controllers\OrganisationController
{
    protected $animalClass;
    protected $animalDateClass;
    protected $bookingClass;
    protected $indexView = 'Animals::admin.organisation';

    public function __construct(Animal $animalClass, AnimalDate $animalDateClass, Booking $bookingClass)
    {
        $this->setActiveMenu(route('animal.admin.index'));
        $this->middleware('dashboard');
        $this->animalClass = $animalClass;
        $this->animalDateClass = $animalDateClass;
        $this->bookingClass = $bookingClass;
    }

}
