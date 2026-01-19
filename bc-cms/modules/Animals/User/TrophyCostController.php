<?php
namespace Modules\Animals\User;

use Illuminate\Http\Request;
use Modules\Animals\Models\Animal;
use Modules\Animals\Models\AnimalTrophy;
use Modules\FrontendController;

class TrophyCostController extends FrontendController
{
    protected $animalClass;
    protected $animalTrophyClass;

    public function __construct(Animal $animalClass, AnimalTrophy $animalTrophyClass)
    {
        parent::__construct();
        $this->setActiveMenu(route('animal.vendor.trophy_cost'));
        $this->animalClass = $animalClass;
        $this->animalTrophyClass = $animalTrophyClass;
    }

    public function callAction($method, $parameters)
    {
        if(!Animal::isEnable())
        {
            return redirect('/');
        }
        return parent::callAction($method, $parameters);
    }

    public function index(Request $request)
    {
        $this->checkPermission('animal_create_hunting');
        $userHotelId = get_user_hotel_id();

        $list_animals = $this->animalClass::query()
            ->join('bc_hotel_animals as bha', function ($join) use ($userHotelId) {
                $join->on('bha.animal_id', '=', 'bc_animals.id')
                    ->where('bha.hotel_id', '=', $userHotelId);
            })
            ->select([
                'bc_animals.*',
                'bha.status as animal_status'
            ])
            ->with('trophies');

        if($request->query('s')){
            $list_animals->where('bc_animals.title', 'like', '%'.$request->query('s').'%');
        }

        $list_animals->orderBy('bc_animals.id', 'desc');
        $rows = $list_animals->paginate(15);

        $breadcrumbs = [
            [
                'name' => __('Animal'),
                'url'  => route('animal.vendor.index')
            ],
            [
                'name'  => __('Trophy Cost'),
                'class' => 'active'
            ],
        ];
        $page_title = __('Trophy Cost');

        return view('Animals::user.trophy_cost', compact('rows', 'breadcrumbs', 'page_title', 'request'));
    }

    public function store(Request $request)
    {
        $this->checkPermission('animal_create_hunting');
        
        $request->validate([
            'animal_id' => 'required|exists:bc_animals,id',
            'trophy_costs' => 'array',
            'trophy_costs.*.id' => 'required|exists:bc_animal_trophies,id',
            'trophy_costs.*.price' => 'nullable|numeric|min:0',
        ]);

        $animalId = $request->input('animal_id');
        $trophyCosts = $request->input('trophy_costs', []);

        // Обновляем только цены существующих трофеев (админ базы может только менять цену)
        foreach ($trophyCosts as $trophyData) {
            if (!empty($trophyData['id'])) {
                AnimalTrophy::where('id', $trophyData['id'])
                    ->where('animal_id', $animalId) // Дополнительная проверка безопасности
                    ->update([
                        'price' => !empty($trophyData['price']) ? $trophyData['price'] : null,
                    ]);
            }
        }

        return redirect()->back()->with('success', __('Trophy costs saved successfully'));
    }

    public function updateSingle(Request $request)
    {
        $this->checkPermission('animal_create_hunting');
        
        $request->validate([
            'trophy_id' => 'required|exists:bc_animal_trophies,id',
            'price' => 'nullable|numeric|min:0',
        ]);

        $trophyId = $request->input('trophy_id');
        $price = $request->input('price');
        $userHotelId = get_user_hotel_id();

        // Проверяем, что трофей принадлежит животному, связанному с отелем админа
        $trophy = AnimalTrophy::where('id', $trophyId)
            ->whereHas('animal', function($query) use ($userHotelId) {
                $query->whereHas('hotels', function($q) use ($userHotelId) {
                    $q->where('hotel_id', $userHotelId);
                });
            })
            ->first();

        if (!$trophy) {
            return response()->json([
                'status' => false,
                'message' => __('Trophy not found')
            ], 404);
        }

        $trophy->update([
            'price' => !empty($price) ? $price : null,
        ]);

        return $this->sendSuccess([
            'message' => __("Saved Success")
        ]);
    }
}
