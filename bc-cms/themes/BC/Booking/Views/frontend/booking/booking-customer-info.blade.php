<div class="booking-review">
    @if($ifAdminBase)
        <h4 class="booking-review-title">{{__('Client Information')}}</h4>
    @else
        <h4 class="booking-review-title">{{__('Special requirements')}}</h4>
    @endif
    <div class="booking-review-content">
            <div class="form-control" style="min-height:120px; background:#f8f9fa;">
                {{ $booking->customer_notes ?? '' }}
            </div>
    </div>
</div>
