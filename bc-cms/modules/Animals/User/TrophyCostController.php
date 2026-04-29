<?php
namespace Modules\Animals\User;

use App\Exceptions\BusinessException;
use App\Http\Responses\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Animals\DTO\UpdateEntityData;
use Modules\Animals\Models\Animal;
use Modules\Animals\Requests\UpdateEntityRequest;
use Modules\Animals\Services\AnimalService;
use Modules\FrontendController;

class TrophyCostController extends FrontendController
{
    protected Animal $animalClass;
    protected string $indexView = 'Animals::user.trophy_cost';

    public function __construct(Animal $animalClass, protected AnimalService $animalService)
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

        $rows = $this->animalClass
            ->forHotel($userHotelId)
            ->withPreparationsForHotel($userHotelId)
            ->search($request->query('s'))
            ->orderByDesc('bc_animals.id')
            ->paginate(15);

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

        return view($this->indexView, compact('rows', 'userHotelId', 'breadcrumbs', 'page_title', 'request'));
    }

    /**
     * @throws BusinessException
     */
    public function updateTrophy(UpdateEntityRequest $request): JsonResponse
    {
        $this->checkPermission('animal_create_hunting');

        $data = new UpdateEntityData($request->validated());
        $entity = $data->getEntity();

        $result = $this->animalService->update($data, $entity, 'trophy');

        return new SuccessResponse(code: $result['code'], domain: 'animal');
    }

    /**
     * @throws BusinessException
     */
    public function updateFine(UpdateEntityRequest $request): JsonResponse
    {
        $this->checkPermission('animal_create_hunting');

        $data = new UpdateEntityData($request->validated());
        $entity = $data->getEntity();

        $result = $this->animalService->update($data, $entity, 'fine');

        return new SuccessResponse(code: $result['code'], domain: 'animal');
    }

    /**
     * @throws BusinessException
     */
    public function updatePreparation(UpdateEntityRequest $request): JsonResponse
    {
        $this->checkPermission('animal_create_hunting');

        $data = new UpdateEntityData($request->validated());
        $entity = $data->getEntity();

        $result = $this->animalService->update($data, $entity, 'preparation');

        return new SuccessResponse(code: $result['code'], domain: 'animal');
    }
}
