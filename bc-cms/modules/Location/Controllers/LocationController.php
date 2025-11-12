<?php
namespace Modules\Location\Controllers;

use App\Http\Controllers\Controller;
use Modules\Location\Models\Location;
use Illuminate\Http\Request;
use Modules\Location\Helpers\AddressHelper;

class LocationController extends Controller
{
    public $location;
    public function __construct(Location $location)
    {
        $this->location = $location;
    }

    public function index(Request $request)
    {

    }

    public function detail(Request $request, $slug)
    {
        $row = $this->location::where('slug', $slug)->where("status", "publish")->first();;
        if (empty($row)) {
            return redirect('/');
        }
        $adminbar_buttons = [];
        if(is_admin()){
            $adminbar_buttons[] = [
                'label' => __('Edit Location'),
                'url' => route('location.admin.edit', ['id' => $row->id]),
                'icon' => 'edit',
            ];
        }
        $translation = $row->translate();
        $data = [
            'row' => $row,
            'translation' => $translation,
            'seo_meta' => $row->getSeoMetaWithTranslation(app()->getLocale(), $translation),
            'breadcrumbs'       => [
                [
                    'name'  => $translation->name,
                    'class' => 'active'
                ],
            ],
            'adminbar_buttons' => $adminbar_buttons,
        ];
        $this->setActiveMenu($row);
        return view('Location::frontend.detail', $data);
    }

    public function searchForSelect2( Request $request ){
        $search = $request->query('search');
        $query = Location::select('bc_locations.*', 'bc_locations.name as title')->where("bc_locations.status","publish");
        if ($search) {
            $query->where('bc_locations.name', 'like', '%' . $search . '%');

            if( setting_item('site_enable_multi_lang') && setting_item('site_locale') != app()->getLocale() ){
                $query->leftJoin('bc_location_translations', function ($join) use ($search) {
                    $join->on('bc_locations.id', '=', 'bc_location_translations.origin_id');
                });
                $query->orWhere(function($query) use ($search) {
                    $query->where('bc_location_translations.name', 'LIKE', '%' . $search . '%');
                });
            }

        }
        $res = $query->orderBy('name', 'asc')->limit(20)->get();
        if(!empty($res) and count($res)){
            $list_json = [];
            foreach ($res as $location) {
                $translate = $location->translate();
                $list_json[] = [
                    'id' => $location->id,
                    'title' => $translate->name,
                ];
            }
            return $this->sendSuccess(['data'=>$list_json]);
        }
        return $this->sendError(__("Location not found"));
    }

    public function stateList(AddressHelper $addressHelper, Request $request){

        $country = $request->get('country');

        if(!$country){
            return $this->sendError(__("Country is required"));
        }

        $collection = collect($addressHelper->getStates(strtolower($country)));

        // format collection to be used in select2
        $collection = $collection->map(function($item){
            return [
                'id' => $item['state_code'],
                'text' => $item['name']
            ];
        });

        return $this->sendSuccess(['results'=>$collection]);
    }
}
