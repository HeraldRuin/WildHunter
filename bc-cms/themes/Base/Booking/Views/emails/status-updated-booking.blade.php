@extends('Email::layout')
@section('content')

    <div class="b-container">
        <div class="b-panel">
            @switch($to)
                @case ('admin')
                <h3 class="email-headline"><strong>{{__('Hello Administrator')}}</strong></h3>
                <p>{{__('The booking status has been updated')}}</p>
                @break

                @case ('vendor')
                <h3 class="email-headline"><strong>{{__('Hello :name',['name'=>$booking->vendor->nameOrEmail ?? ''])}}</strong></h3>
                <p>{{__('The booking status has been updated')}}</p>
                @break


                @case ('customer')
                    <h3 class="email-headline"><strong>{{__('Hello :name',['name'=>$booking->first_name ?? ''])}}</strong></h3>
                    <p>{{__('Your booking status has been updated')}}</p>
                    @if(!empty($customMessage))
                        <hr>
                        <p>{{ $customMessage }}</p>
                    @endif
                    @break

            @endswitch

            @if(!empty($service->email_new_booking_file) && view()->exists($service->email_new_booking_file))
                @include($service->email_new_booking_file)
            @elseif(!empty($service))
                @php
                    $serviceType = class_basename(get_class($service));
                    $possibleViews = [
                        'Animal' => 'Animals::emails.new_booking_detail',
                        'Hotel' => 'Hotel::emails.new_booking_detail',
                    ];
                    $viewName = $possibleViews[$serviceType] ?? null;
                @endphp
                @if($viewName && view()->exists($viewName))
                    @include($viewName)
                @else
                    <p>{{__('Booking details')}}: #{{ $booking->id }}</p>
                    <p>{{__('Status')}}: {{ $booking->status_name }}</p>
                @endif
            @else
                <p>{{__('Booking details')}}: #{{ $booking->id }}</p>
                <p>{{__('Status')}}: {{ $booking->status_name }}</p>
            @endif
        </div>
    </div>
@endsection
