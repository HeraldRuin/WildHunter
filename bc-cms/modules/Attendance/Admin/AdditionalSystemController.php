<?php

namespace Modules\Attendance\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Modules\AdminController;
use Modules\Attendance\Models\AdditionalSystem;
use Modules\User\Models\Role;

class AdditionalSystemController extends AdminController
{
    public function __construct()
    {
        $this->setActiveMenu(route('additional_system.admin.index'));
    }

    protected function authorizeService(string $permission): void
    {
        $user = Auth::user();
        if ($user && ($user->hasRole(Role::SUPERADMIN) || $user->hasPermission($permission))) {
            return;
        }

        abort(403);
    }

    public function index(Request $request)
    {
        $this->authorizeService('additional_system_view');

        $query = AdditionalSystem::query()->orderBy('id');

        if ($search = trim((string) $request->input('s'))) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $data = [
            'rows' => $query->paginate(20)->appends($request->query()),
            'breadcrumbs' => [
                [
                    'name' => __('System services'),
                    'url' => route('additional_system.admin.index'),
                ],
                [
                    'name' => __('All'),
                    'class' => 'active',
                ],
            ],
            'page_title' => __('System services'),
        ];

        return view('Attendance::admin.system.index', $data);
    }

    public function create()
    {
        $this->authorizeService('additional_system_create');

        $data = [
            'row' => new AdditionalSystem(),
            'breadcrumbs' => [
                [
                    'name' => __('System services'),
                    'url' => route('additional_system.admin.index'),
                ],
                [
                    'name' => __('Add system service'),
                    'class' => 'active',
                ],
            ],
            'page_title' => __('Add system service'),
        ];

        return view('Attendance::admin.system.detail', $data);
    }

    public function edit($id)
    {
        $this->authorizeService('additional_system_update');

        $row = AdditionalSystem::query()->find($id);
        if (empty($row)) {
            return redirect(route('additional_system.admin.index'));
        }

        $data = [
            'row' => $row,
            'breadcrumbs' => [
                [
                    'name' => __('System services'),
                    'url' => route('additional_system.admin.index'),
                ],
                [
                    'name' => __('Edit system service'),
                    'class' => 'active',
                ],
            ],
            'page_title' => __('Edit system service'),
        ];

        return view('Attendance::admin.system.detail', $data);
    }

    public function store(Request $request, $id)
    {
        if (is_demo_mode()) {
            return back()->with('error', __('DEMO MODE: You are not allowed to change data'));
        }

        $id = (int) $id;

        if ($id > 0) {
            $this->authorizeService('additional_system_update');
            $row = AdditionalSystem::query()->find($id);
            if (empty($row)) {
                return redirect(route('additional_system.admin.index'));
            }
        } else {
            $this->authorizeService('additional_system_create');
            $row = new AdditionalSystem();
            $row->user_id = Auth::id();
        }

        $request->merge([
            'name' => trim((string) $request->input('name')),
        ]);

        $uniqueName = Rule::unique('bc_additional_systems', 'name');
        if ($row->id) {
            $uniqueName->ignore($row->id);
        }

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                $uniqueName,
            ],
        ], [
            'name.required' => __('Enter the service name'),
            'name.unique' => __('This service already exists'),
        ]);

        $row->name = $request->input('name');
        $row->save();

        if ($id > 0) {
            return redirect(route('additional_system.admin.edit', $row->id))
                ->with('success', __('System service updated'));
        }

        return redirect(route('additional_system.admin.edit', $row->id))
            ->with('success', __('System service created'));
    }

    public function bulkEdit(Request $request)
    {
        $this->authorizeService('additional_system_delete');

        $ids = $request->input('ids');
        $action = $request->input('action');

        if (empty($ids) || !is_array($ids)) {
            return redirect()->back()->with('error', __('No items selected!'));
        }

        if ($action !== 'delete') {
            return redirect()->back()->with('error', __('Please select an action!'));
        }

        AdditionalSystem::query()->whereIn('id', $ids)->delete();

        return redirect()->back()->with('success', __('Deleted success!'));
    }
}
