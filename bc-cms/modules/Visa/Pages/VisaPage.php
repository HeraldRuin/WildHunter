<?php
namespace Modules\Visa\Pages;
use App\BaseComponent;
use Modules\Visa\Models\VisaService;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class VisaPage extends BaseComponent
{
    use WithPagination;

    #[Url]
    public $s;

    #[Url (as:'_layout')]
    public $layout = '';

    #[Url]
    public $guests;

    #[Url]
    public $to_country;

    #[Url]
    public $visa_type;

    #[Url]
    public $price_range;

    #[Url]
    public $review_score;

    public function render()
    {
        $visaClass = app()->make(VisaService::class);
        $data = [
            'rows'=>$this->getQuery()->with(['visaType'])->paginate(setting_item('visa_page_limit_item',20)),
            'seo_meta'=>$visaClass::getSeoMetaForPageList(),
            'layout'=>$this->layout,
        ];

        $data = $this->filterViewData($data);

        return view('Visa::frontend.index',$data)->extends('Layout::app',$data);
    }

    protected function getQuery(){
        return app()->make(VisaService::class)->search($this->getFilters());
    }

    protected function filterViewData($data){
        return $data;
    }

    // Get all filters for search query
    protected function getFilters(){

        return [
            's'=>$this->s,
            'to_country'=>$this->to_country,
            'visa_type'=>$this->visa_type,
            'price_range'=>$this->price_range,
            'review_score'=>$this->review_score,
        ];
    }

    #[On('search')]
    public function search($data)
    {
        foreach($data as $key => $value){
            $this->$key = $value;
        }
        $this->resetPage();
    }
}