<?php

namespace Themes\BC\Car\Pages\Components;

use App\BaseComponent;
use Modules\Location\Models\Location;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

class SearchForm extends BaseComponent
{
    #[Url]
    public $location_id;

    #[Url]
    public $start;

    #[Url]
    public $end;

    #[Url]
    public $service_name;

    #[Url]
    public $map_lat;

    #[Url]
    public $map_lng;

    #[Url]
    public $map_place;

    public $shouldRedirect = false;

    public function render()
    {
        $data = [
            'list_location' => $this->list_location,
        ];
        return view('Car::frontend.components.search-form.index', $data);
    }

    #[Computed]
    public function list_location()
    {
        $locationClass = app(Location::class);
        return $locationClass::where('status', 'publish')->with(['translation'])->limit(1000)->get()->toTree();
    }

    public function placeholder()
    {
        return <<<'HTML'
        <form>
            <!-- Loading spinner... -->
             <span>...</span>
        </form>
        HTML;
    }

    public function submit()
    {
        $data = [
            'location_id' => $this->location_id,
            'start' => $this->start,
            'end' => $this->end,
            'service_name' => $this->service_name,
            'map_lat' => $this->map_lat,
            'map_lng' => $this->map_lng,
            'map_place' => $this->map_place,
        ];
        if($this->shouldRedirect){
            return $this->redirectRoute('car.search', $data);
        }
        $this->dispatch('search', $data);
    }
}
