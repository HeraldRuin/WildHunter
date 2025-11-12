<?php

namespace Modules\Order\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class ConfirmController extends Controller
{
    public function index(Request $request, $gateway)
    {
        $gateways = \Modules\Order\Helpers\PaymentGatewayManager::available();
        if (empty($gateways[$gateway])) {
            $this->sendError(__("Payment gateway not found"));
        }
        $gatewayObj = new $gateways[$gateway]($gateway);
        if (!$gatewayObj->isAvailable()) {
            $this->sendError(__("Payment gateway is not available"));
        }
        return $gatewayObj->confirmPayment($request);
    }
}
