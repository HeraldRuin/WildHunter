@extends('layouts.email')
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
            <p>{{__("Your order item has been updated:")}}</p>

            @include('order.emails.order-item.order-detail')
        </div>
    </div>
@endsection
