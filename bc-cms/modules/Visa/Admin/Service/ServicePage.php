<?php

namespace Modules\Visa\Admin\Service;

use App\BaseAdminPage;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Modules\Visa\Models\VisaService;

class ServicePage extends BaseAdminPage
{
    use WithPagination;

    #[Url]
    public $s;


    // For Bulk Action
    public $action;

    public $ids = [];

    public function render()
    {
        $services = app()->make(VisaService::class)->query();
        if($this->s){
            $services->where(function ($query) {
                $query->where('title', 'like', '%'.$this->s.'%')
                ->orWhere('code', 'like', '%'.$this->s.'%')
                ->orWhere('to_country', 'like', '%'.$this->s.'%');
            });
        }
        $data = [
            'page_title' => __('Visa Services'),
            'rows' => $services->with(['visaType'])->paginate(20),
            'breadcrumbs' => [
                ['name' => __('Visa'), 'url' => route('visa.admin.index')],
            ],
        ];
        return view('Visa::admin.visa.index',$data)->extends('Layout::admin.app',$data);
    }

    public function search()
    {
        // empty but trigger re-render
    }

    public function bulkEdit()
    {
        $data = $this->validate([
            'action' => 'required',
            'ids' => 'required|array',
        ]);

        $items = app()->make(VisaService::class)->whereIn('id', $this->ids);
        if(!$this->hasPermission('visa_manage_others')){
            $items->where('author_id', auth()->id());
        }
        $items = $items->get();
        if(!count($items)){
            $this->sendError(__('No data found'));
            return;
        }
        
        switch ($this->action) {
            case 'publish':
            case 'draft':
            case 'pending':
                $this->checkPermission('visa_update');
                VisaService::query()->whereIn('id', $items->pluck('id'))->update(['status' => $this->action]);
                break;
            case 'clone':
                $this->checkPermission('visa_create');
                foreach ($items as $item) {
                    $replicate = $item->replicate();
                    $replicate->title = $item->title . ' - ' . __('Clone');
                    $replicate->status = 'draft';
                    $replicate->code = uniqid(); // Random code
                    $replicate->save();
                }
                break;
            case 'delete':
                $this->checkPermission('visa_delete');
                VisaService::query()->whereIn('id', $items->pluck('id'))->delete();
                break;
            default:
                break;
        }
        $this->sendSuccess(__('Visa saved'));

        // Reset form
        $this->ids = [];
        $this->action = '';
    }
}