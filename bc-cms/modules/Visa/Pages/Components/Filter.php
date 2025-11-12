<?php
namespace Modules\Visa\Pages\Components;

use App\BaseComponent;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Modules\Visa\Models\VisaService;
use Modules\Visa\Models\VisaType;

class Filter extends BaseComponent
{
    #[Url]
    public $price_range;

    #[Url]
    public $review_score = [];

    public function render()
    {
        $visaClass = app()->make(VisaService::class);
        $data = [
            'min_max_price' => $visaClass::getMinMaxPrice(),
        ];
        return view('Visa::frontend.components.filter.index', $data);
    }

    #[Computed]
    public function visa_types()
    {
        $visaTypeClass = app(VisaType::class);
        return $visaTypeClass::where('status', 'publish')->with(['translation'])->limit(100)->get();
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
        $this->dispatch('search',[
            'price_range' => $this->price_range,
            'review_score' => $this->review_score,
        ]);
    }

    // Trigger search when the filter is changed
    public function updated($prop)
    {
        $this->dispatchFilters();
    }

}
