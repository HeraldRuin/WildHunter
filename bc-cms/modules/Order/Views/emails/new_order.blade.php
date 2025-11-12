@extends('Email::layout')
@section('content')
    <div class="b-container">
        <div class="b-panel">
            @switch($email_to)
                @case("customer")
                    <h1>{{__("Hello ")}} {{$order->customer->display_name ?? ''}}</h1>
                    <p>{{__("Thank you for your order. Here is the order information:")}}</p>
                @break
                @case("admin")
                    <h1>{{__("Hello administrator")}}</h1>
                    <p>{{__("You have new order. Here is the order information:")}}</p>
                @break
                @case("vendor")
                    <h1>{{__("Hello")}} {{$vendor->display_name ?? ''}}</h1>
                    <p>{{__("You have new order. Here is the order information:")}}</p>
                @break
            @endswitch
            @include('Order::emails.parts.order-detail')
            @include('Order::emails.parts.customer-detail')
            @include('Order::emails.parts.order-address')
        </div>
    </div>
@endsection
