<?php
namespace Modules\Attendance\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Attendance\Models\AddetionalPrice;
use Modules\Attendance\Models\Attendance;
use Illuminate\Http\Request;
use Modules\Attendance\Requests\StoreAdditionalRequest;
use Modules\Attendance\Requests\UpdateAdditionalRequest;
use Modules\Location\Models\Location;
use Modules\Review\Models\Review;
use Modules\Core\Models\Attributes;
use DB;

class AdditionalController extends Controller
{
    protected $indexView = 'Animal::frontend.user.organisation';

    public function index(Request $request)
    {
        $additionals = AddetionalPrice::query()
            ->where('user_id', Auth::id())
            ->orderByRaw("name = 'Питание' DESC")
            ->get();

        $breadcrumbs = [
            [
                'name' => __('Additionals'),
                'url'  => route('animal.vendor.index')
            ],
            [
                'name'  => __('Additional services'),
                'class' => 'active'
            ],
        ];
        $page_title = __('Additional services');

        return view('Attendance::user.additional',compact('additionals','breadcrumbs','page_title'));
    }

    public function store(StoreAdditionalRequest $request)
    {
        $additional = AddetionalPrice::create([
            ...$request->validated(),
            'hotel_id' => get_user_hotel_id(),
            'user_id'  => auth()->id(),
        ]);

        return response()->json([
            'status' => true,
            'html'   => view('Additional::frontend.partials.additional-row', [
                'additional' => $additional
            ])->render()
        ]);
    }

    public function update(UpdateAdditionalRequest $request, $id)
    {
        $additional = AddetionalPrice::where('id', $id)
            ->where('user_id', auth()->id())
            ->where('hotel_id', get_user_hotel_id())
            ->first();

        if (!$additional) {
            return response()->json([
                'success' => false,
                'message' => 'Услуга не найдена или нет прав на редактирование',
            ], 404);
        }

        $additional->update($request->validated());

        return $this->sendSuccess([
            'message' => __("Updated Success")
        ]);
    }

    public function destroy($id)
    {
        $additional = AddetionalPrice::where('id', $id)
            ->where('user_id', auth()->id())
            ->where('hotel_id', get_user_hotel_id())
            ->first();

        if (!$additional) {
            return response()->json([
                'success' => false,
                'message' => 'Услуга не найдена или нет прав на удаление',
            ], 404);
        }

        $additional->delete();

        return $this->sendSuccess([
            'message' => __("Deleted Success")
        ]);
    }
}
