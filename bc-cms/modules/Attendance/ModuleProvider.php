<?php
namespace Modules\Attendance;
use Illuminate\Support\Facades\Auth;
use Modules\Attendance\Models\Attendance;
use Modules\Attendance\RouterServiceProvider;
use Modules\Core\Helpers\SitemapHelper;
use Modules\ModuleServiceProvider;
use Modules\User\Helpers\PermissionHelper;
use Modules\User\Models\Role;

class ModuleProvider extends ModuleServiceProvider
{

    public function boot(SitemapHelper $sitemapHelper){
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

        if(is_installed() and Attendance::isEnable()){

            $sitemapHelper->add("attendance",[app()->make(Attendance::class),'getForSitemap']);
        }

        PermissionHelper::add([
            // attendance
            'attendance_view',
            'attendance_create',
            'attendance_update',
            'attendance_delete',
            'attendance_manage_others',
            'attendance_manage_attributes',
            'additional_system_view',
            'additional_system_create',
            'additional_system_update',
            'additional_system_delete',
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
        $user = Auth::user();
        if (!$user || (!$user->hasRole(Role::SUPERADMIN) && !$user->hasPermission('additional_system_view'))) {
            return [];
        }

        return [
            'additional_system' => [
                'position' => 51,
                'url' => route('additional_system.admin.index'),
                'title' => __('System services'),
                'icon' => 'ion-ios-list',
                'group' => 'catalog',
                'children' => [
                    'all' => [
                        'url' => route('additional_system.admin.index'),
                        'title' => __('All system services'),
                        'position' => 10,
                    ],
                    'create' => [
                        'url' => route('additional_system.admin.create'),
                        'title' => __('Add system service'),
                        'position' => 20,
                    ],
                ],
            ],
        ];
    }

    public static function getBookableServices()
    {
//        if(!Attendance::isEnable()) return [];
        return [
        ];
    }

    public static function getMenuBuilderTypes()
    {
        if(!Attendance::isEnable()) return [];
        return [
            'attendance'=>[
                'class' => Attendance::class,
                'name'  => __("Attendance"),
                'items' => Attendance::searchForMenu(),
                'position'=>51
            ]
        ];
    }

    public static function getUserMenu()
    {
        $res = [];
        if(Attendance::isEnable()){
            $res['attendance'] = [
                'url'   => route('animal.vendor.organisation'),
                'title'      => __("Manage Attendances"),
                'icon'       => Attendance::getServiceIconFeatured(),
                'position'   => 30,
                'permission' => 'attendance_view',
                'children' => [
                    'organisation'=>[
                        'url'        => route('animal.vendor.organisation'),
                        'title'      => __("Hunting organization"),
                        'permission' => 'attendance_create',
                    ],
                    'trophy_cost'=>[
                        'url'        => route('animal.vendor.trophy_cost'),
                        'title'      => __("Trophy and Fines"),
                        'permission' => 'attendance_create',
                    ],
                    'additional_services'=>[
                        'url'        => route('additionals.index'),
                        'title'      => __("Additional services"),
                        'permission' => 'attendance_create',
                    ],
                ]
            ];
        }
        return $res;
    }
}
