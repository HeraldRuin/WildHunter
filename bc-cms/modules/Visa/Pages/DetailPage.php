<?php
namespace Modules\Visa\Pages;

use App\BaseComponent;
use Modules\Visa\Models\VisaService;

class DetailPage extends BaseComponent
{
    public $row;
    public $translation;


    public function mount($slug){
        $this->row = app()->make(VisaService::class)->where('slug', $slug)->first();
        if(!$this->row){
            abort(404);
            return;
        }

        // Allow preview for admin and author
        if($this->row->status != 'publish' && ($this->row->author_id != auth()->id() || !is_admin())){
            abort(404);
            return;
        }

        $this->translation = $this->row->translate();
    }

    public function render()
    {
        $adminbar_buttons = [];

        if(is_admin()){
            $adminbar_buttons[] = ['label' => __('Edit Visa'), 'url' => route('visa.admin.edit',['id' => $this->row->id]), 'icon' => 'edit'];
        }
        $data = [
            'row' => $this->row,
            'translation' => $this->translation,
            'seo_meta' => $this->row->getSeoMetaWithTranslation(app()->getLocale(), $this->translation),
            'breadcrumbs'       => [
                [
                    'name'  => __('Visa'),
                    'url'  => route('visa.search'),
                ],
                [
                    'name'  => $this->translation->title,
                    'class' => 'active'
                ]
            ],
            'adminbar_buttons' => $adminbar_buttons
        ];
        return view('Visa::frontend.detail',$data)->extends('Layout::app', $data);
    }
}