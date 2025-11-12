<?php

namespace Themes\BC\Boat\Pages\Components;

use App\BaseComponent;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use \Modules\Boat\Models\Boat;
use \Modules\Location\Models\Location;
use \Modules\Core\Models\Attributes;

class FilterForMap extends BaseComponent
{
    #[Url]
    public $location_id;

    #[Url]
    public $price_range;

    #[Url]
    public $attrs = [];

    #[Computed]
    public function locationsList()
    {
        $locationClass = app(Location::class);
        return $locationClass::where('status', 'publish')->with(['translation'])->limit(1000)->get()->toTree();
    }

    #[Computed]
    public function min_max_price()
    {
        $boatClass = app(Boat::class);
        return $boatClass::getMinMaxPrice();
    }
    
    #[Computed]
    public function attrsList()
    {
        $attributesClass = app(Attributes::class);
        return $attributesClass::where('service', 'boat')->orderBy("position","desc")->with(['terms'=>function($query){
            $query->withCount('boat');
        },'translation'])->get();
    }

    public function setFilter($key,$val){
        $this->$key = $val;
        $this->emitSearch();
    }
    public function setFilters($data){
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
        $this->emitSearch();
    }

    // Used for single attr search (this will not display in more filters)
    public function setAttr($attrId, $slug){
        if(empty($slug) and isset($this->attrs[$attrId])){
            unset($this->attrs[$attrId]);
        }else{
            $this->attrs[$attrId] = [$slug];
        }
        $this->emitSearch();
    }

    protected function emitSearch(){
        $this->dispatch('search', $this->getFilters());
    }

    protected function getFilters(){
        return [
            'location_id'=>$this->location_id,
            'price_range'=>$this->price_range,
            'attrs' => $this->attrs,
        ];
    }
    
    public function render()
    {
        return view('Boat::frontend.components.filter.for-map');
    }
}