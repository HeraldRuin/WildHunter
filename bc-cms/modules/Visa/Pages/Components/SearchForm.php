<?php
namespace Modules\Visa\Pages\Components;

use App\BaseComponent;
use Livewire\Attributes\Url;

class SearchForm extends BaseComponent
{
    #[Url]
    public $to_country;

    #[Url]
    public $visa_type;

    #[Url]
    public $guests;

    public $shouldRedirect = false;

    public function render()
    {
        return view('Visa::frontend.components.search-form.index');
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
            'to_country' => $this->to_country,
            'visa_type' => $this->visa_type,
            'guests' => $this->guests,
        ];
        if($this->shouldRedirect){
            return $this->redirectRoute('visa.search', $data);
        }
        $this->dispatch('search', $data);
    }
}
