<?php
namespace Modules\Tour;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Helpers\SitemapHelper;
use Modules\ModuleServiceProvider;
use Modules\Tour\Models\Tour;
use Modules\User\Helpers\PermissionHelper;

class ModuleProvider extends ModuleServiceProvider
{
    public function boot(SitemapHelper $sitemapHelper)
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

        if(is_installed() and Tour::isEnable()){
            $sitemapHelper->add("tour",[app()->make(Tour::class),'getForSitemap']);
        }

        PermissionHelper::add([
            // Tour
            'tour_view',
            'tour_create',
            'tour_update',
            'tour_delete',
            'tour_manage_others',
            'tour_manage_attributes',
        ]);
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouterServiceProvider::class);
    }

    public static function getBookableServices()
    {
        if(!Tour::isEnable()) return [];
        return [
            'tour' => Tour::class,
        ];
    }

    public static function getAdminMenu()
    {
        return [];
    }


    public static function getUserMenu()
    {
        return [];
    }

    public static function getMenuBuilderTypes()
    {
        if(!Tour::isEnable()) return [];

        return [
            [
                'class' => \Modules\Tour\Models\Tour::class,
                'name'  => __("Tour"),
                'items' => \Modules\Tour\Models\Tour::searchForMenu(),
                'position'=>20
            ],
            [
                'class' => \Modules\Tour\Models\TourCategory::class,
                'name'  => __("Tour Category"),
                'items' => \Modules\Tour\Models\TourCategory::searchForMenu(),
                'position'=>30
            ],
        ];
    }

    public static function getTemplateBlocks(){
        if(!Tour::isEnable()) return [];

        return [
            'list_tours'=>"\\Modules\\Tour\\Blocks\\ListTours",
            'form_search_tour'=>"\\Modules\\Tour\\Blocks\\FormSearchTour",
            'box_category_tour'=>"\\Modules\\Tour\\Blocks\\BoxCategoryTour",
        ];
    }
}
