<?php

namespace Themes\BC\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Modules\Visa\Database\Seeders\VisaSeeder;
use Database\Seeders\BookingSeeder;
use Database\Seeders\Tour;
use Database\Seeders\SpaceSeeder;
use Database\Seeders\HotelSeeder;
use Database\Seeders\CarSeeder;
use Database\Seeders\EventSeeder;
use Database\Seeders\DemoSeeder;
use Database\Seeders\FlightSeeder;
use Database\Seeders\BoatSeeder;
use Database\Seeders\ContactSeeder;
use Database\Seeders\CouponSeeder;
use Database\Seeders\LocationSeeder;
use Database\Seeders\News;
use Database\Seeders\PageSeeder;
use Database\Seeders\ReviewSeeder;
use Database\Seeders\TemplateSeeder;
use Database\Seeders\VendorSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\Language;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\MediaFileSeeder;
use Database\Seeders\General;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        Artisan::call('cache:clear');
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(Language::class);
        $this->call(UsersTableSeeder::class);
        $this->call(MediaFileSeeder::class);
        $this->call(General::class);
        $this->call(LocationSeeder::class);
        $this->call(News::class);
        $this->call(Tour::class);
        $this->call(SpaceSeeder::class);
        $this->call(HotelSeeder::class);
        $this->call(CarSeeder::class);
        $this->call(EventSeeder::class);
        $this->call(FlightSeeder::class);
        $this->call(BoatSeeder::class);
        $this->call(VisaSeeder::class);
    }
}
