<?php

namespace Themes\BC\Weapon;

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Themes\BC\Weapon\Pages\Components\SearchForm;
use Themes\BC\Weapon\Pages\Components\Filter;
use Themes\BC\Weapon\Pages\Components\FilterForMap;

class ModuleProvider extends \Modules\ModuleServiceProvider
{
    public function boot()
    {
        Route::middleware('web')
            ->group(function () {
                $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
            });


        Livewire::component('weapon::search-form', SearchForm::class);
        Livewire::component('weapon::filter', Filter::class);
        Livewire::component('weapon::filter-for-map', FilterForMap::class);
    }
}
