
<div class="bc_detail_tour">
    @include('Layout::parts.bc')
    <div class="container my-3">
        <div class="d-flex justify-content-between">
            <h3>{{ __("Applicant :index", ['index' => ($guestIndex + 1) . "/" . $total_guests]) }}</h3>
            <div class="d-flex b-gap-2">
                @if($guestIndex > 0)
                <button class="btn btn-primary btn-sm" wire:click="prev">
                    <i class="fa fa-arrow-left"></i>
                    {{ __('Prev Applicant') }}
                </button>
                @endif
                @if($guestIndex < $maxGuestIndex)
                <button class="btn btn-primary btn-sm" wire:click="next">
                    {{ __('Next Applicant') }}
                    <i class="fa fa-arrow-right"></i>
                </button>
                @endif
            </div>
        </div>
        <div class="mt-3">
            @livewire('form::simple-form',['options'=>['provider'=>'visa_application_form'], 'data'=>$this->passengerData], key($guestIndex))
        </div>
    </div>
</div>