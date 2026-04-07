<?php

namespace Modules\Booking\Gateways;

class PaymentGatewayResolver
{
    public function resolve(): BaseGateway
    {
        return get_active_payment_gateway_object();
    }
}
