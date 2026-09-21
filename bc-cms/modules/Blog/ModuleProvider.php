<?php
namespace Modules\Blog;

use Modules\ModuleServiceProvider;

class ModuleProvider extends ModuleServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/Config/blog.php', 'blog');
        $this->app->register(RouteServiceProvider::class);
    }

    public static function getAdminMenu()
    {
        return [
            'blog' => [
                'position'   => 30,
                'url'        => route('blog.admin.index'),
                'title'      => __('Blog'),
                'icon'       => 'fa fa-newspaper-o',
                'permission' => 'blog_view',
                'group'      => 'content',
                'children'   => [
                    'blog_view' => [
                        'url'        => route('blog.admin.index'),
                        'title'      => __('All Blogs'),
                        'permission' => 'blog_view',
                    ],
                    'blog_create' => [
                        'url'        => route('blog.admin.create'),
                        'title'      => __('Add Blog'),
                        'permission' => 'blog_create',
                    ],
                ],
            ],
        ];
    }

    public static function getUserMenu()
    {
        return [
            'blog' => [
                'url'        => route('blog.vendor.index'),
                'title'      => __('Blog'),
                'icon'       => 'fa fa-newspaper-o',
                'position'   => 21,
                'permission' => 'blog_view',
                'children'   => [
                    [
                        'url'        => route('blog.vendor.create'),
                        'title'      => __('Add Blog'),
                        'permission' => 'blog_create',
                    ],
                    [
                        'url'        => route('blog.vendor.index'),
                        'title'      => __('Edit Blogs'),
                        'permission' => 'blog_view',
                    ],
                ],
            ],
        ];
    }
}
