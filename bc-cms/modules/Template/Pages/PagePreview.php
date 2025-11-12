<?php
namespace Modules\Template\Pages;

use App\BaseComponent;
use Livewire\Attributes\Url;
use Modules\Template\Models\Template;

class PagePreview extends BaseComponent
{
    public $template;
    public $translation;

    #[Url]
    public $lang;

    public function mount(Template $template)
    {
        $this->template = $template;
        $this->translation = $template->translate($this->lang);
    }

    public function render()
    {
        return view('Template::frontend.preview')->extends('Layout::app');
    }
}
