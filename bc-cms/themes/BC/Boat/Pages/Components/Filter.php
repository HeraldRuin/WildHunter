<?php

namespace Themes\BC\Boat\Pages\Components;

use App\BaseComponent;
use \Modules\Boat\Models\Boat;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Modules\Core\Models\Attributes;

class Filter extends BaseComponent
{
    #[Url]
    public $price_range;

    #[Url]
    public $review_score = [];

    #[Url]
    public $attrs = [];

    public function render()
    {
        $boatClass = app()->make(Boat::class);
        $data = [
            'boat_min_max_price' => $boatClass::getMinMaxPrice(),
        ];
        return view('Boat::frontend.components.filter.index', $data);
    }


    #[Computed]
    public function boat_attributes()
    {
        $attributesClass = app(Attributes::class);
        return $attributesClass::where('service', 'boat')->orderBy("position", "desc")->with(['terms' => function ($query) {
            $query->withCount('boat');
        }, 'translation'])->get();
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div>
            <!-- Loading spinner... -->
             <span>...</span>
        </div>
        HTML;
    }

    // Send filters to the parent
    protected function dispatchFilters()
    {
        $this->dispatch('search', [
            'price_range' => $this->price_range,
            'review_score' => $this->review_score,
            'attrs' => $this->attrs,
        ]);
    }

    // Trigger search when the filter is changed
    public function updated($prop)
    {
        $this->dispatchFilters();
    }


    // NOTE: Term is 2 dimensional array
    // Cant use wire:model because of this
    // So we need to use wire:change to toggle the term and pass the attrs to the parent
    // If not, attrs will be like this: attrs[2][gymnasium]=true
    // Correct format is: attrs[2][1]=gymnasium
    public function toggleTerm($attrId, $slug)
    {
        if (!empty($this->attrs[$attrId]) and in_array($slug, $this->attrs[$attrId])) {
            // remove slug from array
            foreach ($this->attrs[$attrId] as $key => $value) {
                if ($value == $slug) {
                    unset($this->attrs[$attrId][$key]);
                }
            }
        } else {
            // push slug to array
            $this->attrs[$attrId][] = $slug;
        }

        $this->dispatchFilters();
    }
}
