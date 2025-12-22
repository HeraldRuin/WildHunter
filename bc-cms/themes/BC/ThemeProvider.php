<?php

namespace Themes\BC;

use Illuminate\Contracts\Http\Kernel;

class ThemeProvider extends \Themes\Base\ThemeProvider
{

    public static $version = '4.0.0';
    public static $name = 'Booking Core';
    public static $seeder = \Themes\BC\Database\Seeders\DatabaseSeeder::class;

    public static $modules = [
        'core' => \Modules\Core\ModuleProvider::class,
        'api'       => \Modules\Api\ModuleProvider::class,
        'booking'   => \Modules\Booking\ModuleProvider::class,
        'hotel'     => \Modules\Hotel\ModuleProvider::class,
        'space'     => \Modules\Space\ModuleProvider::class,
        'car'       => \Modules\Car\ModuleProvider::class,
        'event'     => \Modules\Event\ModuleProvider::class,
        'tour'      => \Modules\Tour\ModuleProvider::class,
        'flight'    => \Modules\Flight\ModuleProvider::class,
        'boat'      => \Modules\Boat\ModuleProvider::class,
        'contact'   => \Modules\Contact\ModuleProvider::class,
        'dashboard' => \Modules\Dashboard\ModuleProvider::class,
        'email'     => \Modules\Email\ModuleProvider::class,
        'sms'       => \Modules\Sms\ModuleProvider::class,
        'language'  => \Modules\Language\ModuleProvider::class,
        'media'     => \Modules\Media\ModuleProvider::class,
        'news'      => \Modules\News\ModuleProvider::class,
        'page'      => \Modules\Page\ModuleProvider::class,
        'user'      => \Modules\User\ModuleProvider::class,
        'template'  => \Modules\Template\ModuleProvider::class,
        'report'    => \Modules\Report\ModuleProvider::class,
        'vendor'    => \Modules\Vendor\ModuleProvider::class,
        'coupon'    => \Modules\Coupon\ModuleProvider::class,
        'location'  => \Modules\Location\ModuleProvider::class,
        'review'    => \Modules\Review\ModuleProvider::class,
        'popup'     => \Modules\Popup\ModuleProvider::class,
        'form'      => \Modules\Form\ModuleProvider::class,
        'visa'      => \Modules\Visa\ModuleProvider::class,
        'animal'      => \Modules\Animals\ModuleProvider::class,
        'weapon' => \Modules\Weapon\ModuleProvider::class
    ];

    public function register()
    {
        parent::register();
        $this->app->register(\Themes\BC\Core\ModuleProvider::class);
        $this->app->register(\Themes\BC\Tour\ModuleProvider::class);
        $this->app->register(\Themes\BC\Boat\ModuleProvider::class);
        $this->app->register(\Themes\BC\Car\ModuleProvider::class);
    }
    public function boot(Kernel $kernel)
    {
        parent::boot($kernel);

        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
    }
}
