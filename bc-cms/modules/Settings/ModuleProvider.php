<?php
namespace Modules\Settings;

use Modules\Settings\Models\Settings;
use Modules\Settings\RouterServiceProvider;
use Modules\ModuleServiceProvider;
use Modules\User\Helpers\PermissionHelper;

class ModuleProvider extends ModuleServiceProvider
{

    public function boot(){
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

        PermissionHelper::add([
            'settings_view',
            'settings_create',
            'settings_update',
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

    public static function getUserMenu()
    {
        $res = [];
        if(Settings::isEnable()){
            $res['settings'] = [
                'url'   => route('settings.vendor.collection-timer'),
                'title'      => __("Settings"),
                'icon'       => 'icofont-settings',
                'position'   => 70,
                'permission' => 'settings_view',
                'children' => [
                    'timer_collection'=>[
                        'url'        => route('settings.vendor.collection-timer'),
                        'title'      => __("Time collection"),
                        'permission' => 'settings_view',
                    ],
                    'timer_beds'=>[
                        'url'        => route('settings.vendor.beds-timer'),
                        'title'      => __("Time beds"),
                        'permission' => 'settings_view',
                    ],
                ]
            ];
        }
        return $res;
    }
}
