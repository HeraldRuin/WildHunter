<?php

namespace Themes\BC\Car\Pages\Components;

use App\BaseComponent;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use \Modules\Car\Models\Car;
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
        $carClass = app(Car::class);
        return $carClass::getMinMaxPrice();
    }

    #[Computed]
    public function attrsList()
    {
        $attributesClass = app(Attributes::class);
        return $attributesClass::where('service', 'car')->orderBy("position","desc")->with(['terms'=>function($query){
            $query->withCount('car');
        },'translation'])->get();
    }

    public function setFilter($key, $val)
    {
        $this->$key = $val;
        $this->emitSearch();
    }

    public function setFilters($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
        $this->emitSearch();
    }

    // Used for single attr search (this will not display in more filters)
    public function setAttr($attrId, $slug)
    {
        if (empty($slug) and isset($this->attrs[$attrId])) {
            unset($this->attrs[$attrId]);
        } else {
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
        return view('Car::frontend.components.filter.for-map');
    }
}
