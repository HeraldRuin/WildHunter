<?php

namespace Modules\Attendance\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Modules\AdminController;
use Modules\Attendance\Models\AddetionalPrice;
use Modules\Hotel\Models\Hotel;
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

        $query = $this->foodQuery()->orderBy('id');

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
            $row->hotel_id = $this->hotelIdForCurrentUser();
            $row->is_system = $row->hotel_id === null;
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
            'place' => [
                'required',
                Rule::in(array_keys(AddetionalPrice::placeOptions())),
            ],
        ], [
            'name.required' => __('Enter the meal name'),
            'place.required' => __('Select the service type'),
            'place.in' => __('Select the service type'),
        ]);

        $row->name = $request->input('name');
        $row->type = AddetionalPrice::FOOD;
        $row->place = $request->input('place');
        $row->save();

        return redirect(route('meal.admin.edit', $row->id))
            ->with('success', $id > 0 ? __('Meal updated') : __('Meal created'));
    }

    private function findFood($id): ?AddetionalPrice
    {
        return $this->foodQuery()->find($id);
    }

    private function foodQuery(): Builder
    {
        $query = AddetionalPrice::query()->where('type', AddetionalPrice::FOOD);
        $user = Auth::user();

        if (!$user || $user->hasRole(Role::SUPERADMIN)) {
            return $query->where('is_system', 1)->whereNull('hotel_id');
        }

        $hotelId = $this->hotelIdForCurrentUser();
        if ($hotelId === null) {
            return $query->whereRaw('0 = 1');
        }

        return $query->where('hotel_id', $hotelId);
    }

    private function hotelIdForCurrentUser(): ?int
    {
        $user = Auth::user();
        if (!$user || $user->hasRole(Role::SUPERADMIN)) {
            return null;
        }

        $hotelId = Hotel::query()->where('admin_base', $user->id)->value('id');

        return $hotelId ? (int) $hotelId : null;
    }
}
