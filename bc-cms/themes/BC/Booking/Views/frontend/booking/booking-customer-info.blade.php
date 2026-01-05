<div class="booking-review">
    @if($ifAdminBase)
        <h4 class="booking-review-title">{{__('Client Information')}}</h4>
    @else
        <h4 class="booking-review-title">{{__('Your Information')}}</h4>
    @endif
    <div class="booking-review-content">
        <div class="review-section">
            <div class="info-form">
                <ul>
                    <li class="info-first-name">
                        <div class="label">{{__('First name')}}</div>
                        <div class="val">{{$booking->first_name}}</div>
                    </li>
                    <li class="info-last-name">
                        <div class="label">{{__('Last name')}}</div>
                        <div class="val">{{$booking->last_name}}</div>
                    </li>
                    <li class="info-email">
                        <div class="label">{{__('Email')}}</div>
                        <div class="val">{{$booking->email}}</div>
                    </li>
                    <li class="info-phone">
                        <div class="label">{{__('Phone')}}</div>
                        <div class="val">{{$booking->phone}}</div>
                    </li>
                    <li class="info-address">
                        <div class="label">{{__('Address')}}</div>
                        <div class="val">{{$booking->address}}</div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
