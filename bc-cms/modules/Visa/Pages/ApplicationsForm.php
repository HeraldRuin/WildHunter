<?php   
namespace Modules\Visa\Pages;

use App\BaseComponent;
use Modules\Booking\Models\Booking;
use Modules\Visa\Models\VisaService;
use Modules\Visa\Helpers\VisaFormSettings;
use Modules\Form\SimpleForm;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

class ApplicationsForm extends BaseComponent
{
    public $slug;
    public $code;
    public $guestIndex = 0;
    public $maxGuestIndex = 0;

    public function mount($slug, $code)
    {

        $this->slug = $slug;
        $this->code = $code;

       if(!$this->booking){
           abort(404);
       }

       if(!$this->row){
           abort(404);
       }
    }

    public function render()
    {
        $data = [
            'page_title'=>__('Applications'),
            'total_guests'=>$this->booking->total_guests,
            'breadcrumbs'=>[
                [
                    'name'=>__('Visa'),
                    'url'=>route('visa.search'),
                ],
                [
                    'name'=>$this->translation->title,
                    'url'=>route('visa.detail', ['slug' => $this->slug])
                ],
                [
                    'name'=>__('Applicant :index', ['index' => $this->guestIndex + 1]),
                    'class'=>'active'
                ]
            ]
        ];
        return view('Visa::frontend.applications',$data)->extends('Layout::app',$data);
    }

    #[Computed]
    public function formBuilder()
    {
        return app()->make(SimpleForm::class, [app()->make(VisaFormSettings::class)->getForm()]);
    }

    #[Computed]
    public function row()
    {
        return app()->make(VisaService::class)->whereSlug($this->slug)->whereStatus('publish')->first();
    }
    #[Computed]
    public function translation()
    {
        return $this->row->translate();
    }

    #[Computed]
    public function booking()
    {
        return app()->make(Booking::class)->where('code', $this->code)->whereStatus('draft')->first();
    }

    #[Computed]
    public function passengerData()
    {
        return $this->booking->passengers()->firstOrNew([
            'index'=>$this->guestIndex
        ])->meta;
    }

    // Happen when one step is saved
    #[On('formSaved')]
    public function formSaved($data, $isSavedAll = false)
    {
        // Validate booking again and make sure guestIndex is still valid
        if(!$this->booking || $this->guestIndex >= $this->booking->total_guests){
            abort(404);
        }

        $findPassenger = $this->booking->passengers()->firstOrNew([
            'index'=>$this->guestIndex
        ]);

        if(!$findPassenger->exists){
            $findPassenger->index = $this->guestIndex;
        }
        
        // Store data to booking passengers
        $this->fillPassenger($findPassenger, $data);

        if($isSavedAll){
            $this->formSavedAll();
        }
    }

    protected function formSavedAll()
    {
        // TODO: Validate all passengers data was successfully submitted before checkout?

        if($this->guestIndex >= $this->booking->total_guests - 1){
           // when all passengers are saved, redirect to checkout
           return $this->redirectRoute('booking.checkout', ['code' => $this->booking->code]);
        }

        // when saved all, just increment guestIndex
        $this->guestIndex++;
        $this->maxGuestIndex = max($this->guestIndex, $this->maxGuestIndex);

        // Clear computed
        unset($this->passengerData);
    }

    protected function fillPassenger($passenger, $data)
    {
        // try to map data with passenger attributes, special case for some fields with different name
        $mappedData = $data;
        if(isset($data['birth_date'])){
            $mappedData['dob'] = $data['birth_date'];
        }

        $mainFields = [
            'first_name',
            'last_name',
            'dob',
            'email',
            'phone'
        ];

        $dataToSave = array_intersect_key($mappedData, array_flip($mainFields));
        // Merge rawData with old meta to save extra field
        $dataToSave['meta'] = array_merge($passenger->meta ?? [], $data);

        $passenger->fillByAttr(array_keys($dataToSave), $dataToSave);
        $passenger->save();
        return true;
    }

    public function prev()
    {
        if($this->guestIndex > 0){
            $this->guestIndex--;
            unset($this->passengerData);
        }
    }

    public function next()
    {
        if($this->guestIndex < $this->booking->total_guests - 1){
            $this->guestIndex++;
            unset($this->passengerData);
        }
    }
}
