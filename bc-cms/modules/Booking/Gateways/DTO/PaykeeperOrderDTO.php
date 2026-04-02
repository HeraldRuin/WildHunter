<?php

namespace Modules\Booking\Gateways\DTO;

class PaykeeperOrderDTO
{
    public function __construct(
        public float|string $payAmount,
        public string $clientId,
        public int|string $orderId,
        public string $serviceName,
        public string $clientEmail,
        public string $clientPhone,
        public string $expiry,
    ) {}

    public function toArray(): array
    {
        return [
            'pay_amount'   => $this->payAmount,
            'clientid'     => $this->clientId,
            'orderid'      => $this->orderId,
            'service_name' => $this->serviceName,
            'client_email' => $this->clientEmail,
            'client_phone' => $this->clientPhone,
            'expiry'       => $this->expiry,
        ];
    }
}
