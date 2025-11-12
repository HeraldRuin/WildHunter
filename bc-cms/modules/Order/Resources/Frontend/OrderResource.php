<?php
namespace Modules\Order\Resources\Frontend;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Product\Models\ShippingZoneMethod;
use Modules\Product\Models\TaxRate;

class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'subtotal_amount' => (float) $this->subtotal,
            'discount_amount' => (float) $this->discount_amount,
            'shipping_available' => ShippingZoneMethod::countMethodAvailable() == 0 ? false : true,
            'tax_available' => TaxRate::isEnable(),
        ];
    }
}
