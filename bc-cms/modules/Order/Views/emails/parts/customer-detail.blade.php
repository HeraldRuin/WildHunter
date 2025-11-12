<h3><strong>{{__("Customer Details:")}}</h3>
<p><strong>{{__("Display Name:")}}</strong> {{$order->first_name.' '.$order->last_name}}</p>
<p><strong>{{__("Email:")}}</strong> {{$order->email ?? ''}}</p>
<p><strong>{{__("Phone:")}}</strong> {{$order->phone ?? ''}}</p>
<br>
