<?php   
namespace Modules\Visa\Pages\Components;

use Livewire\Component;
use Modules\Form\Traits\HasFormFeatures;
use Livewire\Attributes\Computed;
use Modules\Visa\Helpers\VisaFormSettings;

class ApplicantFormView extends Component
{
    use HasFormFeatures;
    public $passenger;
    public $stepIndex = 0;

    public function render()
    {
        $data = [
            'fieldsMapById' => $this->fieldsMapById,
        ];
        return view('Visa::frontend.components.applicant-form-view',$data);
    }


    #[Computed]
    public function form()
    {
        return app(VisaFormSettings::class)->getForm();
    }

    #[Computed]
    public function steps()
    {
        return collect($this->form)->where('type', 'step')->toArray();
    }

    #[Computed]
    public function fieldsMapById()
    {
        return collect($this->getFieldsFlattenFromArray($this->form, $this->passenger->meta ?? []))->keyBy('id')->toArray();
    }

}
