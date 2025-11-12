<?php


namespace Modules\Order\Resources\Frontend;


use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Product\Resources\ProductResource;
use Modules\User\Resources\UserResource;

class CartItemResource extends JsonResource
{

    public function toArray($request)
    {
        $model = $this->product;
        $variations = [];
        if ($variation = $this->variation)
        {
            $terms = $variation->terms;
            foreach ($terms as $term){
                $variations[] = [
                    'attribute'=>$term->attribute->name,
                    'name'=>$term->name
                ];
            }
        }
        return [
            'id'=>$this->id,
            'qty'=>$this->qty,
            'price'=>$this->price,
            'subtotal'=>$this->subtotal,
            'author'=>$model ? new UserResource($model->author) : [],
            'product'=>$model ? new ProductResource($model) : [],
            'variation_id'=>$this->variation_id,
            'variation'=>$variations ?? []
        ];
    }
}
