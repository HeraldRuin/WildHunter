<?php
namespace Modules\Blog\Admin;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\AdminController;
use Modules\Core\Helpers\AdminMenuManager;

class BlogController extends AdminController
{
    public function __construct()
    {
        AdminMenuManager::setActive('blog');
    }

    public function index(Request $request)
    {
        $this->checkPermission('page_view');

        $rows = new LengthAwarePaginator(
            [],
            0,
            20,
            max(1, (int) $request->input('page', 1)),
            [
                'path'  => $request->url(),
                'query' => $request->query(),
            ]
        );

        $data = [
            'rows'        => $rows,
            'page_title'  => __('All Blogs'),
            'breadcrumbs' => [
                [
                    'name' => __('Blog'),
                    'url'  => route('blog.admin.index'),
                ],
                [
                    'name'  => __('All'),
                    'class' => 'active',
                ],
            ],
        ];

        return view('Blog::admin.index', $data);
    }

    public function create()
    {
        $this->checkPermission('page_view');

        $data = [
            'page_title'  => __('Add Blog'),
            'breadcrumbs' => [
                [
                    'name' => __('Blog'),
                    'url'  => route('blog.admin.index'),
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
