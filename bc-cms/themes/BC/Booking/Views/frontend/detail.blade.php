@extends('layouts.app')
@push('css')
    <link href="{{ asset('themes/bc/dist/frontend/module/booking/css/checkout.css?_ver='.config('app.asset_version')) }}" rel="stylesheet">
@endpush
@section('content')
    <div class="bc-booking-page padding-content" >
        <div class="container">
            @if(!$ifAdminBase)
                @include ('Booking::frontend/global/booking-detail-notice')
            @endif
            <div class="row booking-success-detail">
                <div class="col-md-8">
                    @include ($service->booking_customer_info_file ?? 'Booking::frontend/booking/booking-customer-info', ['ifAdminBase' => $ifAdminBase])
                    <div class="text-center">
                        <a href="{{route('user.booking_history')}}" class="btn btn-primary">{{__('Booking History')}}</a>
                    </div>
                </div>
                <div class="col-md-4">
                    @if($booking_type === 'hotel_animal')
                        @include ('Hotel::frontend/booking/detailHotelAnimal')
                    @else
                        @include ($service->checkout_booking_detail_file ?? '')
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
