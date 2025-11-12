
<div class="">
    <div class="">
        <h2 class="title-bar no-border-bottom">{{__('Visa Booking Detail')}}</h2>
    </div>

    <div class="booking-history-manager">
        <div class="tabbable">
            <ul class="nav nav-tabs ht-nav-tabs">
                @foreach($booking->passengers as $index=>$passenger)
                <li class=" @if($passenger->id == $passengerId) active @endif">
                    <a href="#passenger_{{$passenger->id}}" wire:click="setPassenger({{$passenger->id}})">{{__('Applicant :index',['index'=>$index + 1])}}</a>
                </li>
                @endforeach
            </ul>
            <div class="tab-content">
                @if($this->currentPassenger)
                    @livewire('visa::applicant-form-view', ['passenger' => $this->currentPassenger], key($this->currentPassenger->id))
                @endif
            </div>
        </div>
        @if(!count($booking->passengers))
            <div class="alert alert-danger">{{__('No applications found')}}</div>
        @endif
    </div>
</div>