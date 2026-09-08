<?php
namespace Modules\Blog;

use Modules\ModuleServiceProvider;

class ModuleProvider extends ModuleServiceProvider
{
    public function register()
    {
        $this->app->register(RouterServiceProvider::class);
    }

    public static function getAdminMenu()
    {
        return [
            'blog' => [
                'position'   => 30,
                'url'        => route('blog.admin.create'),
                'title'      => __('Blog'),
                'icon'       => 'fa fa-newspaper-o',
                'permission' => 'page_view',
                'group'      => 'content',
                'children'   => [
                    'blog_create' => [
                        'url'        => route('blog.admin.create'),
                        'title'      => __('Add Blog'),
                        'permission' => 'page_view',
                    ],
                ],
            ],
        ];
    }
}
