<?php

namespace App\Providers;

use Livewire\Livewire;
use Illuminate\Support\ServiceProvider;
use App\Livewire\Select2;


class LivewireServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Livewire::component('select2', Select2::class);
    }
}
