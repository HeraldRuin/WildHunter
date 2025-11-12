<?php
namespace Modules\Course;
use Modules\Course\Models\Course;
use Modules\ModuleServiceProvider;
use Modules\User\Helpers\PermissionHelper;
use Modules\Course\EventServiceProvider;

class ModuleProvider extends ModuleServiceProvider
{

    public function boot(){

        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

        PermissionHelper::add([
            'course_view',
            'course_create',
            'course_update',
            'course_delete',
            'course_manage_others',
            'course_manage_attributes',
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
        $this->app->register(EventServiceProvider::class);
    }

    public static function getServices()
    {
        return [
            'course' => Course::class,
        ];
    }

    public static function getAdminMenu()
    {
        if(!Course::isEnable()) return [];
        return [
            'course'=>[
                "position"=>45,
                'url'        => route('course.admin.index'),
                'title'      => __('Courses'),
                'icon'       => 'ion-md-briefcase',
                'permission' => 'course_view',
                'children'   => [
                    'add'=>[
                        'url'        => route('course.admin.index'),
                        'title'      => __('All Courses'),
                        'permission' => 'course_view',
                    ],
                    'create'=>[
                        'url'        => route('course.admin.create'),
                        'title'      => __('Add new course'),
                        'permission' => 'course_create',
                    ],
                    'category'=>[
                        'url'        => route('course.admin.category.index'),
                        'title'      => __('Categories'),
                        'permission' => 'course_manage_attributes',
                    ],
                    'level'=>[
                        'url'        => route('course.admin.level.index'),
                        'title'      => __('Levels'),
                        'permission' => 'course_manage_attributes',
                    ],
                    'attribute'=>[
                        'url'        => route('course.admin.attribute.index'),
                        'title'      => __('Attributes'),
                        'permission' => 'course_manage_attributes',
                    ],

                ]
            ]
        ];
    }

    public static function getMenuBuilderTypes()
    {
        if(!Course::isEnable()) return [];
        return [
            'course'=>[
                'class' => Course::class,
                'name'  => __("Courses"),
                'items' => Course::searchForMenu(),
                'position'=>51
            ]
        ];
    }

    public static function getUserMenu()
    {
        if(!Course::isEnable()) return [];
        return [
            'course' => [
                'url'   => route('course.vendor.index'),
                'title'      => __("Manage Course"),
                // 'icon'       => Course::getServiceIconFeatured(),
                'position'   => 31,
                'permission' => 'course_view',
                'children' => [
                    [
                        'url'   => route('course.vendor.index'),
                        'title'  => __("All Courses"),
                    ],
                    [
                        'url'   => route('course.vendor.create'),
                        'title'      => __("Add Course"),
                        'permission' => 'course_create',
                    ],
                ]
            ],
        ];
    }

    public static function getTemplateBlocks(){
        if(!Course::isEnable()) return [];
        return [
            'list_course'=>"\\Modules\\Course\\Blocks\\ListCourses",
        ];
    }
}
