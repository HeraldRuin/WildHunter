<?php

namespace Modules\Attendance\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\AdminController;
use Modules\Attendance\Models\AddetionalPrice;
use Modules\User\Models\Role;

class MealController extends AdminController
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

        $query = AddetionalPrice::query()
            ->where('type', AddetionalPrice::FOOD)
            ->orderBy('id');

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
                    'name' => __('Питание'),
                    'class' => 'active',
                ],
            ],
            'page_title' => __('Питание'),
        ];

        return view('Attendance::admin.meal.index', $data);
    }

    public function create()
    {
        $this->authorizeService('additional_system_create');

        $data = [
            'row' => new AddetionalPrice(),
            'breadcrumbs' => [
                [
                    'name' => __('Питание'),
                    'url' => route('meal.admin.index'),
                ],
                [
                    'name' => __('Add meal'),
                    'class' => 'active',
                ],
            ],
            'page_title' => __('Add meal'),
        ];

        return view('Attendance::admin.meal.detail', $data);
    }

    public function edit($id)
    {
        $this->authorizeService('additional_system_update');

        $row = $this->findFood($id);
        if (empty($row)) {
            return redirect(route('meal.admin.index'));
        }

        $data = [
            'row' => $row,
            'breadcrumbs' => [
                [
                    'name' => __('Питание'),
                    'url' => route('meal.admin.index'),
                ],
                [
                    'name' => __('Edit meal'),
                    'class' => 'active',
                ],
            ],
            'page_title' => __('Edit meal'),
        ];

        return view('Attendance::admin.meal.detail', $data);
    }

    public function store(Request $request, $id)
    {
        if (is_demo_mode()) {
            return back()->with('error', __('DEMO MODE: You are not allowed to change data'));
        }

        $id = (int) $id;

        if ($id > 0) {
            $this->authorizeService('additional_system_update');
            $row = $this->findFood($id);
            if (empty($row)) {
                return redirect(route('meal.admin.index'));
            }
        } else {
            $this->authorizeService('additional_system_create');
            $row = new AddetionalPrice();
            $row->user_id = Auth::id();
            $row->type = AddetionalPrice::FOOD;
        }

        $request->merge([
            'name' => trim((string) $request->input('name')),
        ]);

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:191',
            ],
        ], [
            'name.required' => __('Enter the meal name'),
        ]);

        $row->name = $request->input('name');
        $row->type = AddetionalPrice::FOOD;
        $row->save();

        return redirect(route('meal.admin.edit', $row->id))
            ->with('success', $id > 0 ? __('Meal updated') : __('Meal created'));
    }

    private function findFood($id): ?AddetionalPrice
    {
        return AddetionalPrice::query()
            ->where('type', AddetionalPrice::FOOD)
            ->find($id);
    }
}
