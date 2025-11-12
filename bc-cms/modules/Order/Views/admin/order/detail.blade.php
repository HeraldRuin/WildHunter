@extends ('admin.layouts.app')
@section ('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{$order->id ? __("Edit Order: #:order_id",['order_id'=>$order->id]) : __("Create new order")}}</h1>
        </div>
        @include('Layout::admin.message')
        <div class="row" id="bc_order_form" v-cloak>
            <div class="col-md-9">
                @include('Order::admin.order.detail.customer')
                @include('Order::admin.order.detail.items')
            </div>
            <div class="col-md-3">
                <div class="panel">
                    <div class="panel-title"><strong>{{__("Publish")}}</strong></div>
                    <div class="panel-body">
                        @if($order->gateway)
                            <h6>{{__("Payment via: :name",['name'=>$order->gateway_name])}}</h6>
                            @if($order->pay_date)
                                <h6>{{__("Paid on: :time",['time'=>display_datetime($order->pay_date)])}}</h6>
                            @endif
                            <hr>
                        @endif
                        <div class="form-group">
                            <label >{{__("Status")}}</label>
                            <select v-model="status" class="form-select form-control">
                                @foreach($statues as $status_id=>$text)
                                    <option value="{{$status_id}}">{{$text}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label >{{__("Order date")}}</label>
                            <bc-datepicker placeholder="{{__("Please select")}}" v-model="order_date" :settings="created_at_settings"></bc-datepicker>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn btn-success" @click="save"><i class="fa fa-save"></i> {{__("Save changes")}}
                            <i v-show="saving" class="fa fa-spinner fa-pulse fa-fw"></i>
                        </button>
                        <div class="mt-3" v-show="message.content" v-bind:class="!message.success ? 'text-danger' : 'text-success'" v-html="message.content"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script.body')
    @include('Layout::admin.components.datepicker')
    @include('Layout::admin.components.select2')
    @include('Order::admin.order.detail.components.modal-address')
    @include('Order::admin.order.detail.components.item')
    <script>
        BC.routes.customer = {
            getForSelect2:"{{route('customer.admin.getForSelect2',['need_address'=>1])}}"
        };
        BC.routes.product = {
            getForSelect2: "{!! route('product.admin.getForSelect2',['need_variations'=>1,'select2'=>1]) !!}"
        }
        BC.routes.order = {
            store:'{!! route('order.admin.store',['order'=>$order]) !!}'
        }
        var bc_order = {!! json_encode(new \Modules\Order\Resources\Admin\OrderResource($order,['items','shipping_methods','tax_lists','price'])) !!}
        var bc_country_list = {!! json_encode(get_country_lists()) !!}
    </script>
    <script src="{{asset('module/order/admin/detail.js')}}"></script>
@endpush
