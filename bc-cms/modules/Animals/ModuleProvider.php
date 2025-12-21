<?php
namespace Modules\Animals;
use Modules\Animals\Models\Animal;
use Modules\Animals\RouterServiceProvider;
use Modules\Core\Helpers\SitemapHelper;
use Modules\ModuleServiceProvider;
use Modules\User\Helpers\PermissionHelper;

class ModuleProvider extends ModuleServiceProvider
{

    public function boot(SitemapHelper $sitemapHelper){

        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

        if(is_installed() and Animal::isEnable()){

            $sitemapHelper->add("animal",[app()->make(Animal::class),'getForSitemap']);
        }

        PermissionHelper::add([
            // animal
            'animal_view',
            'animal_create',
            'animal_update',
            'animal_delete',
            'animal_manage_others',
            'animal_manage_attributes',
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

    public static function getAdminMenu()
    {
//        if(!Animal::isEnableForAdmin()) return [];
        return [
            'animal'=>[
                "position"=>45,
                'url'        => route('animal.admin.index'),
                'title'      => __('Animal'),
                'icon'       => 'ion-ios-play-circle',
                'permission' => 'animal_view',
                'group'      => 'catalog',
                'children'   => [
                    'add'=>[
                        'url'        => route('animal.admin.index'),
                        'title'      => __('All Animals'),
                        'permission' => 'animal_view',
                    ],
                    'create'=>[
                        'url'        => route('animal.admin.create'),
                        'title'      => __('Add new animal'),
                        'permission' => 'animal_create',
                    ],
                    'dates'=>[
                        'url'        => route('animal.admin.availability'),
                        'title'      => __("Availability Dates"),
                        'permission' => 'animal_create',
                    ],
                    'recovery'=>[
                        'url'        => route('animal.admin.recovery'),
                        'title'      => __('Recovery'),
                        'permission' => 'animal_view',
                    ],
                ]
            ]
        ];
    }

    public static function getBookableServices()
    {
        if(!Animal::isEnable()) return [];
        return [
            'animal'=>Animal::class
        ];
    }

    public static function getMenuBuilderTypes()
    {
        if(!Animal::isEnable()) return [];
        return [
            'animal'=>[
                'class' => Animal::class,
                'name'  => __("Animal"),
                'items' => Animal::searchForMenu(),
                'position'=>51
            ]
        ];
    }

    public static function getUserMenu()
    {
        $res = [];
        if(Animal::isEnable()){
            $res['animal'] = [
                'url'   => route('animal.vendor.index'),
                'title'      => __("Manage Animals"),
                'icon'       => Animal::getServiceIconFeatured(),
                'position'   => 30,
                'permission' => 'animal_view',
                'children' => [
                    [
                        'url'   => route('animal.vendor.index'),
                        'title'  => __("All Animals"),
                    ],
                    'organisation'=>[
                        'url'        => route('animal.vendor.organisation'),
                        'title'      => __("Hunting organization"),
                        'permission' => 'animal_create_hunting',
                    ],
                ]
            ];
        }
        return $res;
    }

//    public static function getTemplateBlocks(){
//        if(!Animal::isEnable()) return [];
//        return [
//            'form_search_animal'=>"\\Modules\\Animal\\Blocks\\FormSearchCar",
//            'list_animal'=>"\\Modules\\Animal\\Blocks\\ListAnimal",
//            'animal_term_featured_box'=>"\\Modules\\Animal\\Blocks\\AnimalTermFeaturedBox",
//        ];
//    }
}
