<?php
namespace Modules\Blog\Admin;

use Modules\AdminController;
use Modules\Core\Helpers\AdminMenuManager;

class BlogController extends AdminController
{
    public function __construct()
    {
        AdminMenuManager::setActive('blog');
    }

    public function create()
    {
        $this->checkPermission('page_view');

        $data = [
            'page_title'  => __('Add Blog'),
            'breadcrumbs' => [
                [
                    'name' => __('Blog'),
                    'url'  => route('blog.admin.create'),
                ],
                [
                    'name'  => __('Add Blog'),
                    'class' => 'active',
                ],
            ],
        ];

        return view('Blog::admin.create', $data);
    }
}
