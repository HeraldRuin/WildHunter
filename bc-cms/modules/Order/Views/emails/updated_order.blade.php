@extends('Email::layout')
@section('content')
    <div class="b-container">
        <div class="b-panel">
            @switch($email_to)
                @case("customer")
                <h1>{{__("Hello ")}} {{$order->customer->display_name ?? ''}}</h1>
                @break
                @case("admin")
                <h1>{{__("Hello administrator")}}</h1>
                @break
                @case("vendor")
                <h1>{{__("Hello")}} {{$vendor->display_name ?? ''}}</h1>
                @break
            @endswitch
            <p>{{__("Your order has been updated:")}}</p>

            @include('Order::emails.parts.order-detail')
            @include('Order::emails.parts.customer-detail')
            @include('Order::emails.parts.order-address')
        </div>
    </div>
@endsection
