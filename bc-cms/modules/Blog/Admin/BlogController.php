<?php
namespace Modules\Blog\Admin;

use Illuminate\Http\Request;
use Modules\AdminController;
use Modules\Blog\Models\Blog;
use Modules\Core\Helpers\AdminMenuManager;

class BlogController extends AdminController
{
    public function __construct()
    {
        AdminMenuManager::setActive('blog');
    }

    public function index(Request $request)
    {
        $this->checkPermission('blog_view');

        $query = Blog::query()->orderBy('id', 'desc');

        if ($search = $request->query('s')) {
            $query->where('title', 'LIKE', '%' . $search . '%');
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $data = [
            'rows'        => $query->paginate(20),
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
        $this->checkPermission('blog_create');

        $row = new Blog();
        $row->fill([
            'status'       => 'draft',
            'content_json' => ['blocks' => []],
        ]);

        return view('Blog::admin.editor', $this->editorData($row, __('Add Blog')));
    }

    public function edit(Request $request, $id)
    {
        $this->checkPermission('blog_update');

        $row = Blog::find($id);
        if (empty($row)) {
            return redirect(route('blog.admin.index'));
        }

        if (empty($row->content_json)) {
            $row->content_json = ['blocks' => []];
        }

        return view('Blog::admin.editor', $this->editorData($row, __('Edit Blog')));
    }

    public function store(Request $request, $id)
    {
        if (is_demo_mode()) {
            return response()->json(['success' => false, 'message' => __('DEMO MODE: Disable update')], 403);
        }

        if ($id > 0) {
            $this->checkPermission('blog_update');
            $row = Blog::find($id);
            if (empty($row)) {
                return response()->json(['success' => false, 'message' => __('Blog not found')], 404);
            }
        } else {
            $this->checkPermission('blog_create');
            $row = new Blog();
            $row->status = 'draft';
        }

        $row->title = $request->input('title', '');
        $row->slug = $request->input('slug', '');
        $row->status = $request->input('status', 'draft');
        $row->image_id = $request->input('image_id');
        $row->excerpt = $request->input('excerpt', '');

        $contentJson = $request->input('content_json');
        if (is_string($contentJson)) {
            $contentJson = json_decode($contentJson, true);
        }
        $row->content_json = is_array($contentJson) ? $contentJson : ['blocks' => []];

        $row->save();

        return response()->json([
            'success'  => true,
            'message'  => $id > 0 ? __('Blog updated') : __('Blog created'),
            'id'       => $row->id,
            'url'      => route('blog.admin.edit', ['id' => $row->id]),
            'save_url' => route('blog.admin.store', ['id' => $row->id]),
        ]);
    }

    public function bulkEdit(Request $request)
    {
        if (is_demo_mode()) {
            return redirect()->back()->with('danger', __('DEMO MODE: Disable update'));
        }

        $ids = $request->input('ids');
        $action = $request->input('action');

        if (empty($ids) || !is_array($ids)) {
            return redirect()->back()->with('error', __('No items selected!'));
        }

        if (empty($action)) {
            return redirect()->back()->with('error', __('Please select an action!'));
        }

        foreach ($ids as $id) {
            $row = Blog::find($id);
            if (empty($row)) {
                continue;
            }

            switch ($action) {
                case 'publish':
                    $this->checkPermission('blog_update');
                    $row->status = 'publish';
                    $row->save();
                    break;
                case 'draft':
                    $this->checkPermission('blog_update');
                    $row->status = 'draft';
                    $row->save();
                    break;
                case 'delete':
                    $this->checkPermission('blog_delete');
                    $row->delete();
                    break;
            }
        }

        return redirect()->back()->with('success', __('Updated successfully'));
    }

    protected function editorData(Blog $row, string $pageTitle): array
    {
        return [
            'row'         => $row,
            'page_title'  => $pageTitle,
            'breadcrumbs' => [
                [
                    'name' => __('Blog'),
                    'url'  => route('blog.admin.index'),
                ],
                [
                    'name'  => $pageTitle,
                    'class' => 'active',
                ],
            ],
        ];
    }
}
