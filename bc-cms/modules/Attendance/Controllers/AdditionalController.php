<?php
namespace Modules\Attendance\Controllers;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Responses\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Attendance\Application\UseCases\Addetionals\AdditionalResource;
use Modules\Attendance\DTO\StoreAdditionalData;
use Modules\Attendance\DTO\UpdateAdditionalData;
use Modules\Attendance\Models\AddetionalPrice;
use Modules\Attendance\Requests\StoreAdditionalRequest;
use Modules\Attendance\Requests\UpdateAdditionalRequest;
use Modules\Attendance\Services\AddetionalService;

class AdditionalController extends Controller
{
    protected string $indexView = 'Attendance::user.additional';

    public function __construct(protected AddetionalService $addetionalService) {}

    public function index()
    {
        $additionals = AddetionalPrice::query()
            ->forUser(Auth::id())
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

        return view($this->indexView, compact('additionals','breadcrumbs','page_title'));
    }

    public function store(StoreAdditionalRequest $request): JsonResponse
    {
        $data = StoreAdditionalData::fromRequest($request);
        $addetional = $this->addetionalService->store($data, get_user_hotel_id(), Auth::id());

        return new SuccessResponse(data: new AdditionalResource($addetional));
    }

    /**
     * @throws NotFoundException
     */
    public function update(UpdateAdditionalRequest $request, $id): JsonResponse
    {
        $data = UpdateAdditionalData::fromRequest($request);
        $this->addetionalService->update($data, $id, get_user_hotel_id(), Auth::id());

        return new SuccessResponse();
    }

    /**
     * @throws NotFoundException
     */
    public function destroy($id): JsonResponse
    {
        $this->addetionalService->delete($id, get_user_hotel_id(), Auth::id());

        return new SuccessResponse();
    }
}
