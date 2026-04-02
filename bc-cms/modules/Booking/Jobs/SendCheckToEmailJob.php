<?php

namespace Modules\Booking\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendCheckToEmailJob implements ShouldQueue
{
    use Queueable;

    protected string $invoiceId;

    public function __construct($invoiceId)
    {
        $this->invoiceId = $invoiceId;
    }

    public function handle()
    {
        $gatewayObj = get_active_payment_gateway_object();
        $gatewayObj->sendCheckToEmail($this->invoiceId);
    }
}
