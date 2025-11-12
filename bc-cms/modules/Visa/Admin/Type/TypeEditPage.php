<?php


namespace Modules\Visa\Admin\Type;

use Modules\Core\Helpers\AdminMenuManager;
use App\BaseAdminPage;
use Modules\Visa\Models\VisaType;
use Livewire\Attributes\Validate;
use Modules\Visa\Admin\Type\HasStoreType;

class TypeEditPage extends BaseAdminPage
{
    use HasStoreType;
    public $row;

    #[Validate('required')]
    public $name;

    #[Validate('required')]
    public $status;

    public $translation;

    public $id = null;

    public function mount($id)
    {
        AdminMenuManager::setActive('visa');
        $this->id = $id;
        $this->row = VisaType::find($id);

        if (!$this->row) {
            $this->sendError(__('Visa Type not found'));
            return redirect()->route('visa.type.index');
        }

        $this->translation = $this->row->translate($this->lang);
        $this->name = $this->translation->name;
        $this->status = $this->row->status;
    }

    public function render()
    {
        $data = [
            'page_title' => __('Edit Visa Type'),
            'row' => $this->row,
            'translation' => $this->translation,
            'enable_multi_lang' => true,
        ];
        return view('Visa::admin.type.edit', $data)->extends('Layout::admin.app', $data);
    }
}