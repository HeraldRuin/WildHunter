<?php
namespace Modules\Form;

use App\BaseComponent;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Modules\Form\Traits\HasFormFeatures;

class SimpleForm extends BaseComponent
{
    use HasFormFeatures;
    public $options = [];
    public $stepIndex = 0;
    public $maxStepIndex = 0;

    public $data = [];


    public function mount()
    {
        // setup default value
        $fields = $this->formFlatten;
        if(!empty($fields)) {
            foreach ($fields as $field) {
                if(isset($field['std']) and !isset($this->data[$field['id']])){
                    $this->data[$field['id']] = $field['std'];
                }
            }
        }

        // If data is empty, calculate maxStepIndex
        $this->calculateMaxStepIndex();
    }

    protected function calculateMaxStepIndex()
    {
        if(empty($this->data) || empty($this->steps)){
            return;
        }

        // validate each step ?
        foreach ($this->steps as $index => $step) {
            $validator = $this->validateFields($step['children'] ?? []);
            if($validator->fails()){
                // When validate failed, set maxStepIndex to current step
                $this->maxStepIndex = $index;

                break;
            }else{
                // When validate passed, set maxStepIndex to next step
                $this->maxStepIndex = min(count($this->steps) - 1, $index + 1);
            }
        }
    }

    #[Computed]
    public function form()
    {
        if(!empty($this->options['provider'])){
            $formBuilder = app(FormBuilder::class);
            $provider = $formBuilder::getProvider($this->options['provider']);
            if($provider){
                return app()->call($provider);
            }
        }

        return [];
    }

    #[Computed]
    public function steps()
    {
        // Step is always the first level
        return collect($this->form)->where('type', 'step')->toArray();
    }

    #[Computed]
    public function currentStep()
    {
        return $this->steps[$this->stepIndex] ?? [];
    }

    public function setStep($stepIndex)
    {
        if($stepIndex <= $this->maxStepIndex){
            $this->stepIndex = $stepIndex;
        }
    }

    public function render()
    {
        return view('Form::frontend.simple-form');
    }

    public function saveStep()
    {
        $fields = $this->getFieldsFlatten($this->currentStep);
        if(empty($fields)){

            // move to next step
            $this->stepIndex++;
            $this->maxStepIndex = max($this->maxStepIndex, $this->stepIndex);
            return;
        }
        
        $validator = $this->validateFields($fields);
        if($validator){
            $validator->validate();
        }

        $data = $validator->validated();

        // Check if there is no next step
        if($this->stepIndex == (count($this->steps) - 1)){
            $this->dispatch('formSaved', $data, true);

            // Reset state
            $this->stepIndex = 0;
            $this->maxStepIndex = 0;
            unset($this->currentStep); // reset computed property
            $this->data = [];

        }else{
            $this->stepIndex++;
            $this->maxStepIndex = max($this->maxStepIndex, $this->stepIndex);
            $this->dispatch('formSaved', $data, false);

            // Unset computed properties
            // If not, re-render wont work
            unset($this->currentStep);
        }
    }

    #[Computed]
    public function formFlatten()
    {
        if(empty($this->form)){
            return [];
        }
        $fields = [];
        foreach ($this->form as $item) {
            $fields = array_merge($fields, $this->getFieldsFlatten($item));
        }
        return $fields;
    }

    // Validate fields array
    protected function validateFields($fields)
    {
        if(empty($fields)){
            return false;
        }
        foreach ($fields as $field) {
            $fields = array_merge($fields, $this->getFieldsFlatten($field));
        }
        
        // Get rules arrays from fields
        $rules = collect($fields)->filter(function ($field) {
            return !empty($field['rules']);
        })->mapWithKeys(function ($field) {
            return [$field['id'] => $field['rules']];
        })->toArray();

        $customLabelsForValidation = collect($fields)->filter(function ($field) {
            return !empty($field['label']);
        })->mapWithKeys(function ($field) {
            return [$field['id'] => $field['label']];
        })->toArray();

        return Validator::make($this->data, $rules, [], $customLabelsForValidation);
    }
}