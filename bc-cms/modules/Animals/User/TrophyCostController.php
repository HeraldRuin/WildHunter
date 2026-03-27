<?php
namespace Modules\Animals\User;

use App\Http\Responses\NotFoundResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Animals\DTO\UpdateEntityData;
use Modules\Animals\Models\Animal;
use Modules\Animals\Models\AnimalFine;
use Modules\Animals\Models\AnimalPreparation;
use Modules\Animals\Models\AnimalTrophy;
use Modules\Animals\Requests\UpdateEntityRequest;
use Modules\FrontendController;

class TrophyCostController extends FrontendController
{
    protected Animal $animalClass;
    protected AnimalTrophy $animalTrophyClass;

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
            ->with([
                'preparations' => function ($q) use ($userHotelId) {
                    $q->select('id','animal_id','type')
                        ->with(['hotelPrices' => function ($q2) use ($userHotelId) {
                            $q2->where('hotel_id', $userHotelId);
                        }]);
                }
            ]);

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

        return view('Animals::user.trophy_cost', compact('rows', 'userHotelId', 'breadcrumbs', 'page_title', 'request'));
    }

    public function store(Request $request): RedirectResponse
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

    public function storeFines(Request $request)
    {
        $this->checkPermission('attendance_create');

        $request->validate([
            'animal_id' => 'required|exists:bc_animals,id',
            'fines_costs' => 'array',
            'fines_costs.*.id' => 'required|exists:bc_animal_fines,id',
            'fines_costs.*.price' => 'nullable|numeric|min:0',
        ]);

        $animalId = $request->input('animal_id');
        $finesCosts = $request->input('fines_costs', []);

        foreach ($finesCosts as $finesData) {
            if (!empty($finesData['id'])) {
                AnimalFine::where('id', $finesData['id'])
                    ->where('animal_id', $animalId)
                    ->update([
                        'price' => !empty($finesData['price']) ? $finesData['price'] : null,
                    ]);
            }
        }

        return redirect()->back()->with('success', __('Fines costs saved successfully'));
    }

    public function storePreparations(Request $request)
    {
        $this->checkPermission('attendance_create');

        $request->validate([
            'animal_id' => 'required|exists:bc_animals,id',
            'preparation_costs' => 'array',
            'preparation_costs.*.id' => 'required|exists:bc_animal_preparations,id',
            'preparation_costs.*.price' => 'nullable|numeric|min:0',
        ]);

        $animalId = $request->input('animal_id');
        $preparationCosts = $request->input('preparation_costs', []);

        foreach ($preparationCosts as $preparationData) {
            if (!empty($preparationData['id'])) {
                AnimalPreparation::where('id', $preparationData['id'])
                    ->where('animal_id', $animalId)
                    ->update([
                        'price' => !empty($preparationData['price']) ? $preparationData['price'] : null,
                    ]);
            }
        }

        return redirect()->back()->with('success', __('Preparation costs saved successfully'));
    }

    public function updateTrophy(UpdateEntityRequest $request): JsonResponse|NotFoundResponse
    {
        $this->checkPermission('animal_create_hunting');

        $data = new UpdateEntityData($request->validated());
        $entity = $data->getEntity();

        $trophy = $entity->forHotel(get_user_hotel_id())->first();

        if (!$trophy) {
            return new NotFoundResponse(__('Trophy not found'));
        }

        $trophy->setHotelPrice(get_user_hotel_id(), $data->price);
        $trophy->priceForHotel(get_user_hotel_id());

        return $this->sendSuccess([
            'message' => __("Saved Success")
        ]);
    }

    public function updateFine(UpdateEntityRequest $request): JsonResponse|NotFoundResponse
    {
        $this->checkPermission('animal_create_hunting');

        $data = new UpdateEntityData($request->validated());
        $entity = $data->getEntity();

        $fine = $entity->forHotel(get_user_hotel_id())->first();

        if (!$fine) {
            return new NotFoundResponse(__('Fine not found'));
        }

        $fine->setHotelPrice(get_user_hotel_id(), $data->price);
        $fine->priceForHotel(get_user_hotel_id());

        return $this->sendSuccess([
            'message' => __("Saved Success")
        ]);
    }

    public function updatePreparation(UpdateEntityRequest $request): JsonResponse|NotFoundResponse
    {
        $this->checkPermission('animal_create_hunting');

        $data = new UpdateEntityData($request->validated());
        $entity = $data->getEntity();

        $preparation = $entity->forHotel(get_user_hotel_id())->first();

        if (!$preparation) {
            return new NotFoundResponse(__('Preparation not found'));
        }

        $preparation->setHotelPrice(get_user_hotel_id(), $data->price);
        $preparation->priceForHotel(get_user_hotel_id());

        return $this->sendSuccess([
            'message' => __("Saved Success")
        ]);
    }
}
