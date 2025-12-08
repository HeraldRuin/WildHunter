<?php

namespace Themes\BC\Animal;

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Themes\BC\Animal\Pages\Components\SearchForm;
use Themes\BC\Animal\Pages\Components\Filter;
use Themes\BC\Animal\Pages\Components\FilterForMap;

class ModuleProvider extends \Modules\ModuleServiceProvider
{
    public function boot()
    {
        Route::middleware('web')
            ->group(function () {
                $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
            });


        Livewire::component('animal::search-form', SearchForm::class);
        Livewire::component('animal::filter', Filter::class);
        Livewire::component('animal::filter-for-map', FilterForMap::class);
    }
}
