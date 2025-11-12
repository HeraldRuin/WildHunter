<?php


namespace Modules\Order\Resources\Admin;


use App\Resources\BaseJsonResource;
use Modules\Product\Resources\ProductResource;

class OrderItemResource extends BaseJsonResource
{
    public function toArray($request)
    {
        $model = $this->product;
        return [
            'id'=>$this->id,
            'product_id'=>$this->object_id,
            'qty'=>$this->qty,
            'price'=>(float) $model->getBuyablePrice(),
            'title'=>!empty($model->getBuyableName()) ? $model->getBuyableName().' - #'.$this->object_id : '',
            'product'=> $model ? new ProductResource($model,['variations','price']) : null,
            'variation_id'=>$this->variation_id
        ];
    }

}
