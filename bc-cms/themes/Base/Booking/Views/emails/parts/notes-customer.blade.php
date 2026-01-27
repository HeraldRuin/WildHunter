 @php
    $customerNotes = $booking->customer_notes;
@endphp

<div class="b-panel">
    <div class="b-panel-title">{{__('Customer notes')}}</div>
    <div class="mt30">
        <p>{{$customerNotes}}</p>
    </div>
</div>
