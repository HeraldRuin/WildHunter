<?php
namespace App;

use Livewire\Attributes\Url;
use Livewire\Component;

class BaseAdminPage extends BaseComponent
{
    #[Url]
    public $lang;


    public function setLang($lang)
    {
        $this->lang = $lang;
    }
}