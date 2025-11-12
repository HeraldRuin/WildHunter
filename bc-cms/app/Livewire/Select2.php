<?php

namespace App\Livewire;

use Livewire\Component;

class Select2 extends Component
{
    public $options = [];
    public $selected = null;
    public $placeholder = '';
    public $name = '';
    public $id = '';
    public $class = '';
    public $label = '';
    public $required = false;
    public $multiple = false;


    public function mount()
    {
        $this->id = 'select2-'.rand(1, 1000000);
    }

    public function updatedSelected($value)
    {
        // You can emit events or handle the selected value here
        $this->emit('select2Changed', $value, $this->name);
    }

    public function render()
    {
        return view('livewire.select2');
    }
    
}
