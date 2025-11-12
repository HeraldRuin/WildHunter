<?php


namespace Modules\Order\Resources\Admin;


use App\Resources\BaseJsonResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Modules\Order\Models\Order;
use Modules\Product\Models\ShippingZoneMethod;
use Modules\Product\Models\TaxRate;
use Modules\User\Resources\UserResource;

class OrderResource extends BaseJsonResource
{
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'items'=> $this->whenNeed('items',function(){
                return OrderItemResource::collection($this->items);
            }),
            'customer'=> $this->customer ? new UserResource($this->customer,['address']) : ['id'=>'','display_name'=>''],
            'billing'=>$this->getJsonMeta('billing'),
            'shipping'=>$this->getJsonMeta('shipping'),
            'status'=>$this->status ?? Order::DRAFT,
            'order_date'=>$this->order_date ? $this->order_date->format('Y-m-d H:i:s') : '',
            'email'=>$this->email,
            'phone'=>$this->phone,
            'shipping_amount'=>(float)$this->shipping_amount,
            'shipping_method'=>$this->getMeta('shipping_method'),
            'shipping_methods'=> $this->whenNeed('shipping_methods',function(){
                return (new ShippingZoneMethod())->methods();
            }),
            'prices_include_tax'=>setting_item('prices_include_tax'),
            'tax_amount'=>$this->tax_amount,
            'tax_lists'=>(array) $this->whenNeed('tax_lists',function(){
                $meta = collect($this->getJsonMeta('tax'));
                $rows = TaxRate::select("id","name", "tax_rate", "city", "postcode", "country", "state")->get();
                $rows->map(function($item) use($meta){
                    if($meta->where('id',$item->id)->first()) $item->active = 1;
                });
                return $rows->toArray();
            }),
            'is_editable'=>$this->isEditable()
        ];
    }
}
