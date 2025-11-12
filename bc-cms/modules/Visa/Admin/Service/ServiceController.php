<?php
namespace Modules\Visa\Admin\Service;

use Modules\AdminController;
use Modules\Visa\Models\VisaService;
use Modules\Visa\Models\VisaType;
use Modules\Visa\Models\VisaServiceTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends AdminController
{
    public function __construct()
    {
        $this->setActiveMenu('visa');
    }
    public function edit($id)
    {
        $this->checkPermission('visa_update');
        $service = app(VisaService::class)->find($id);
        if(!$service){
            abort(404);
        }

        if(!$this->hasPermission('visa_manage_others') and $service->author_id != auth()->id()){
            abort(403);
        }

        $data = [
            'page_title' => __('Visa Service'),
            'breadcrumbs' => [
                ['name' => __('Visa Service'), 'url' => route('visa.admin.index')],
                ['name' => __('Edit')],
            ],
            'row' => $service,
            'translation' => $service->translate(request()->input('lang')),
            'types' => app(VisaType::class)->all(),
        ];
        return view('Visa::admin.visa.edit', $data);
    }


    public function create()
    {
        $this->checkPermission('visa_create');
        $service = app(VisaService::class);
        $data = [
            'page_title' => __('Visa Service'),
            'breadcrumbs' => [
                ['name' => __('Visa Service'), 'url' => route('visa.admin.index')],
                ['name' => __('Create')],
            ],
            'types' => app(VisaType::class)->all(),
            'row' => $service,
            'translation' => app(VisaServiceTranslation::class),
        ];
        return view('Visa::admin.visa.edit', $data);
    }

    public function store(Request $request, $id = null)
    {
        if (is_demo_mode()) {
            return redirect()->back()->with('danger', __("DEMO MODE: can not add data"));
        }
        if ($id > 0) {
            $this->checkPermission('visa_update');
            $row = app(VisaService::class)::find($id);
            if (empty($row)) {
                return redirect(route('visa.admin.index'));
            }
            if ($row->author_id != Auth::id() and !$this->hasPermission('visa_manage_others')) {
                return redirect(route('visa.admin.index'));
            }
        } else {
            $this->checkPermission('visa_create');
            $row = app(VisaService::class);
            $row->status = "publish";
        }

        $request->validate([
            'title' => 'required',
            'code' => 'required|alpha_dash:ascii|unique:bc_visa_services,code,'.$id,
        ]);

        $dataKeys = [
            'title',
            'code',
            'to_country',
            'type_id',
            'price',
            'content',
            'status',
            'image_id',
            'author_id',
            'multiple_entry',
            'max_stay_days',
            'processing_days',
            'original_price',
            'slug',
        ];

        $row->fillByAttr($dataKeys, $request->input());
        $row->saveOriginOrTranslation($request->input('lang'), true);

        if($id){
            return back()->with('success', __('Visa Service updated'));
        }else{
            return redirect()->route('visa.admin.edit', $row->id)->with('success', __('Visa Service created'));
        }
    }
}