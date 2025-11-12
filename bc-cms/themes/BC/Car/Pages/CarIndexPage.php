<?php

namespace Themes\BC\Car\Pages;

use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\BaseComponent;
use \Modules\Car\Models\Car;
use Livewire\Attributes\On;

class CarIndexPage extends BaseComponent
{

    use WithPagination;

    #[Url]
    public $s;

    #[Url]
    public $location_id;

    #[Url]
    public $price_range;

    #[Url]
    public $review_score = [];

    #[Url]
    public $cat_id = [];

    #[Url]
    public $attrs = [];

    #[Url]
    public $start;

    #[Url]
    public $end;

    #[Url]
    public $orderby;

    #[Url]
    public $service_name;

    #[Url]
    public $map_lat;

    #[Url]
    public $map_lng;

    #[Url]
    public $map_place;

    #[Url(as: '_layout')]
    public $layout = '';


    public function render()
    {

        $carClass = app()->make(Car::class);
        $rows = $this->getQuery()->paginate(20);
        $data = [
            'rows' => $rows,
            'seo_meta' => $carClass::getSeoMetaForPageList(),
        ];

        if($this->layout === 'map'){
            $data['markers'] = $this->getMarkers($rows);
            $data['body_class'] = 'has-search-map';
            $data['html_class'] = 'full-page';
            $data['container_class'] = 'container-fluid';
            $data['header_right_menu'] = true;

            $this->dispatch('update-markers', markers: $data['markers']);
        }

        $data = $this->filterViewData($data);

        if($this->layout === 'map'){
            return view('Car::frontend.search-map',$data)->extends('Layout::app', $data);
        }
        return view('Car::frontend.search',$data)->extends('Layout::app', $data);
    }

    protected function getQuery()
    {
        return app()->make(Car::class)->search($this->getFilters());
    }

    protected function filterViewData($data)
    {
        return $data;
    }

    // Get all filters for search query
    protected function getFilters()
    {

        return [
            'location_id' => $this->location_id,
            'price_range' => $this->price_range,
            'review_score' => $this->review_score,
            'cat_id' => $this->cat_id,
            'attrs' => $this->attrs,
            'start' => $this->start,
            'end' => $this->end,
            'orderby' => $this->orderby,
            'service_name' => $this->service_name,
            'map_lat' => $this->map_lat,
            'map_lng' => $this->map_lng,
            'map_place' => $this->map_place,
        ];
    }


    #[On('search')]
    public function search($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
        $this->resetPage();
    }

    protected function getMarkers($rows)
    {
        $markers = [];
        foreach ($rows as $row) {
            $markers[] = [
                "id"      => $row->id,
                "title"   => $row->title,
                "lat"     => (float)$row->map_lat,
                "lng"     => (float)$row->map_lng,
                "infobox" => view('Car::frontend.layouts.search.loop-grid', ['row' => $row,'disable_lazyload'=>1,'wrap_class'=>'infobox-item'])->render(),
                'marker' => get_file_url(setting_item("car_icon_marker_map"),'full') ?? url('images/icons/png/pin.png'),
            ];
        }
        return $markers;
    }
}
