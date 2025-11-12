<?php

namespace Modules\Visa\Admin\Type;

use Modules\Core\Helpers\AdminMenuManager;
use App\BaseAdminPage;
use Modules\Visa\Models\VisaType;
use Livewire\Attributes\Validate;
use Modules\Visa\Admin\Type\HasStoreType;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class TypePage extends BaseAdminPage
{
    use HasStoreType;
    use WithPagination;

    #[Validate('required')]
    public $name;

    #[Validate('required')]
    public $status = 'publish';

    #[Url]
    public $s;

    public $ids = [];

    public $action;

    public $row = null;

    public $id = null;

    public function mount()
    {
        AdminMenuManager::setActive('visa');
        $this->row = app()->make(VisaType::class);
    }

    public function render()
    {
        $visaType = app()->make(VisaType::class);
        $translation = $visaType->translate(request('lang'));

        $query  = $visaType->query();

        if($this->s){
            $query->where('name', 'like', '%'.$this->s.'%');
        }

        $data = [
            'page_title' => __('Visa Types'),
            'rows'=>$query->paginate(20),
            'translation'=>$translation,
        ];
        return view('Visa::admin.type.index', $data)->extends('Layout::admin.app', $data);
    }


    public function bulkEdit()
    {
        $this->checkPermission('visa_manage_others');

        $this->validate([
            'ids' => 'required|array',
            'action' => 'required',
        ]);

        $ids = $this->ids;
        $action = $this->action;

        $visaType = app()->make(VisaType::class);

        if($action == 'publish'){
            $visaType->whereIn('id', $ids)->update(['status' => 'publish']);
        }elseif($action == 'draft'){
            $visaType->whereIn('id', $ids)->update(['status' => 'draft']);
        }elseif($action == 'delete'){
            $visaType->whereIn('id', $ids)->delete();
        }
        $this->sendSuccess(__('Visa Type saved'));

        // Reset form
        $this->ids = [];
        $this->action = '';
    }
}