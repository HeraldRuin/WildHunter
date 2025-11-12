<?php
$passengers = $booking->passengers;
if(!count($passengers)) return;
?>
<div class="accordion gateways-table my-3" id="passengers_info">
    <a target="_blank" href="{{ route('visa.user.booking-detail', ['code' => $booking->code]) }}" class="btn btn-primary btn-sm">{{__('View applications details')}} <i class="fa fa-arrow-right"></i></a>
</div>
