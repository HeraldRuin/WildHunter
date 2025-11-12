<?php

namespace Modules\Order\Controllers;

use App\Http\Controllers\Controller;
use Modules\Order\Models\Order;

class OrderModalController extends Controller
{
    public function index($code)
    {
        $order = app()->make(Order::class)->whereCode($code)->first();
        if (!$order) {
            abort(404);
        }
        if (!auth()->user()->hasPermission('order_view') and $order->customer_id != auth()->id()) {
            abort(404);
        }
        return view('Order::frontend.order.modal', ['order' => $order]);
    }
}
