<?php
namespace Modules\Animals\User;

use App\Http\Responses\NotFoundResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Animals\DTO\UpdateEntityData;
use Modules\Animals\Models\Animal;
use Modules\Animals\Requests\UpdateEntityRequest;
use Modules\FrontendController;

class TrophyCostController extends FrontendController
{
    protected Animal $animalClass;

    public function __construct(Animal $animalClass)
    {
        parent::__construct();
        $this->setActiveMenu(route('animal.vendor.trophy_cost'));
        $this->animalClass = $animalClass;
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

        $list_animals = $this->animalClass
            ->forHotel($userHotelId)
            ->withPreparationsForHotel($userHotelId);

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

    public function updateTrophy(UpdateEntityRequest $request): JsonResponse|NotFoundResponse
    {
        $this->checkPermission('animal_create_hunting');

        $data = new UpdateEntityData($request->validated());
        $entity = $data->getEntity();

        $trophy = $entity->forHotel(get_user_hotel_id())->where('id', $entity->id)->first();

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

        $fine = $entity->forHotel(get_user_hotel_id())->where('id', $entity->id)->first();

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

        $preparation = $entity->forHotel(get_user_hotel_id())->where('id', $entity->id)->first();

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
