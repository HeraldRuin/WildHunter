<?php
namespace Modules\Animals;
use Modules\Animals\Models\Animal;
use Modules\Animals\RouterServiceProvider;
use Modules\ModuleServiceProvider;
use Modules\User\Helpers\PermissionHelper;

class ModuleProvider extends ModuleServiceProvider
{

    public function boot(){

        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

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
        if(!Animal::isEnable()) return [];
        return [
            'animal'=>[
                "position"=>45,
                'url'        => route('animal.admin.index'),
                'title'      => __('Animal'),
                'icon'       => 'ion-logo-model-s',
                'permission' => 'car_view',
                'group'      => 'catalog',
//                'children'   => [
//                    'add'=>[
//                        'url'        => route('car.admin.index'),
//                        'title'      => __('All Cars'),
//                        'permission' => 'car_view',
//                    ],
//                    'create'=>[
//                        'url'        => route('car.admin.create'),
//                        'title'      => __('Add new Car'),
//                        'permission' => 'car_create',
//                    ],
//                    'attribute'=>[
//                        'url'        => route('car.admin.attribute.index'),
//                        'title'      => __('Attributes'),
//                        'permission' => 'car_manage_attributes',
//                    ],
//                    'availability'=>[
//                        'url'        => route('car.admin.availability.index'),
//                        'title'      => __('Availability'),
//                        'permission' => 'car_create',
//                    ],
//                    'recovery'=>[
//                        'url'        => route('car.admin.recovery'),
//                        'title'      => __('Recovery'),
//                        'permission' => 'car_view',
//                    ],
//                ]
            ]
        ];
    }

    public static function getBookableServices()
    {
        if(Animal::isEnable()) return [];
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
                'title'      => __("Manage Animal"),
                'icon'       => Animal::getServiceIconFeatured(),
                'position'   => 70,
                'permission' => 'animal_view',
                'children' => [
                    [
                        'url'   => route('animal.vendor.index'),
                        'title'  => __("All Animals"),
                    ],
                    [
                        'url'   => route('animal.vendor.create'),
                        'title'      => __("Add Animal"),
                        'permission' => 'animal_create',
                    ],
                    [
                        'url'        => route('animal.vendor.availability.index'),
                        'title'      => __("Availability"),
                        'permission' => 'animal_create',
                    ],
                    [
                        'url'   => route('animal.vendor.recovery'),
                        'title'      => __("Recovery"),
                        'permission' => 'animal_create',
                    ],
                ]
            ];
        }
        return $res;
    }

//    public static function getTemplateBlocks(){
//        if(!Animal::isEnable()) return [];
//        return [
//            'form_search_car'=>"\\Modules\\Animal\\Blocks\\FormSearchCar",
//            'list_car'=>"\\Modules\\Animal\\Blocks\\ListCar",
//            'car_term_featured_box'=>"\\Modules\\Animal\\Blocks\\CarTermFeaturedBox",
//        ];
//    }
}
