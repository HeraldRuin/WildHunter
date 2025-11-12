<?php

namespace Themes\BC\Tour\Pages\Components;

use App\BaseComponent;
use \Modules\Tour\Models\TourCategory;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use \Modules\Tour\Models\Tour;
use \Modules\Location\Models\Location;
use \Modules\Core\Models\Attributes;

class FilterForMap extends BaseComponent
{
    
    #[Url]
    public $location_id;

    #[Url]
    public $price_range;

    #[Url]
    public $review_score = [];

    #[Url]
    public $cat_id = '';

    #[Url]
    public $attrs = [];

    #[Computed]
    public function tour_category()
    {
        $tourCategoryClass = app(TourCategory::class);
        return $tourCategoryClass::where('status', 'publish')->with(['translation'])->get()->toTree();
    }

    #[Computed]
    public function tour_location()
    {
        $locationClass = app(Location::class);
        return $locationClass::where('status', 'publish')->with(['translation'])->limit(1000)->get()->toTree();
    }

    #[Computed]
    public function min_max_price()
    {
        $tourClass = app(Tour::class);
        return $tourClass::getMinMaxPrice();
    }
    
    #[Computed]
    public function attrsList()
    {
        $attributesClass = app(Attributes::class);
        return $attributesClass::where('service', 'tour')->orderBy("position","desc")->with(['terms'=>function($query){
            $query->withCount('tour');
        },'translation'])->get();
    }

    public function setFilter($key,$val){
        $this->$key = $val;
        $this->emitSearch();
    }

    protected function emitSearch(){
        $this->dispatch('search', $this->getFilters());
    }

    protected function getFilters(){
        return [
            'location_id'=>$this->location_id,
            'price_range'=>$this->price_range,
            'cat_id'=>$this->cat_id,
            'attrs' => $this->attrs,
        ];
    }
    
    public function render()
    {
        return view('Tour::frontend.components.filter.for-map');
    }
}