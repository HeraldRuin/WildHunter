<?php

namespace Modules\Weapon;

use Modules\Weapon\Models\WeaponType;
use Modules\Weapon\RouterServiceProvider;
use Modules\Core\Helpers\SitemapHelper;
use Modules\ModuleServiceProvider;
use Modules\User\Helpers\PermissionHelper;

class ModuleProvider extends ModuleServiceProvider
{

    public function boot(SitemapHelper $sitemapHelper){
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

        if(is_installed() and WeaponType::isEnable()){

            $sitemapHelper->add("weapon",[app()->make(WeaponType::class),'getForSitemap']);
        }

        PermissionHelper::add([
            // weapon
            'weapon_view',
            'weapon_create',
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

    public static function getAdminMenu(): array
    {
        return [
            'weapon'=>[
                "position"=>50,
                'url'        => route('weapon.admin.index'),
                'title'      => __("Weapon"),
                'icon'       => 'ion-ios-play-circle',
                'permission' => 'weapon_view',
                'group'      => 'catalog',
                'children'   => [
                    'add'=>[
                        'url'        => route('weapon.admin.index'),
                        'title'      => __('All Weapons'),
                        'permission' => 'weapon_view',
                    ],
                    'create'=>[
                        'url'        => route('weapon.admin.create'),
                        'title'      => __('Add new weapon'),
                        'permission' => 'weapon_create',
                    ],
                    'caliber'=>[
                        'url'        => route('caliber.admin.index'),
                        'title'      => __("Caliber"),
                        'permission' => 'caliber_view',
                    ],
                ]
            ]
        ];
    }

    public static function getBookableServices(): array
    {
//        if(!WeaponType::isEnable()) return [];
        return [
            'weapon'=>WeaponType::class
        ];
    }

    public static function getMenuBuilderTypes(): array
    {
//        if(!WeaponType::isEnable()) return [];
        return [
            'weapon'=>[
                'class' => WeaponType::class,
                'name'  => __("Weapon"),
                'items' => WeaponType::searchForMenu(),
                'position'=>51
            ]
        ];
    }

    public static function getUserMenu(): array
    {
        return [
        ];
    }
}
