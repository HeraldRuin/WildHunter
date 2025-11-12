<?php


namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductVariation;

class CartItem extends OrderItem
{

    protected $attributes = [
        "qty"=>1,
        "meta"=>"",
        "object_id"=>"",
        "object_model"=>"",
        "price"=>0,
        "discount_amount"=>0, // counpon discount amount
        "shipping_amount"=>0, // shipping amount nếu có
        "variation_id"=>"",
    ];

    protected $casts = [
        "qty"=>"integer",
        "price"=>"float",
        "meta"=>"array",
    ];

    public static function fromProduct(Product $model,$qty = 1,$price = 0, $meta = [],$variation_id = ''){

        $item = new self();
        $item->object_id = $model->id;
        $item->object_model = $model->type;
        $item->qty = $qty;
        $item->title = $model->title;
        $item->price = $variation_id ? ProductVariation::find($variation_id)->sale_price ?? 0 : min($model->price,$model->sale_price) ;
        $item->vendor_id = $model->author_id;
        $item->variation_id = (int) $variation_id;
        $item->status = 'draft';
        return $item;
    }

    public static function fromModel(Product $model,$qty = 1,$price = 0, $meta = [],$variation_id = ''){

        $item = new self();

        $item->class_name = get_class($model);

        $item->product_id = $model->id;
        $item->qty = $qty;
        $item->title = $model->name_for_cart;
        $item->price = $price ? $price : $model->price_for_cart ;
        $item->object_id = $model->id;
        $item->object_model = $model->type;
        $item->meta = $meta;
        $item->author = $model->author->display_name;
        $item->variation_id = $variation_id;

        return $item;
    }

    public static function fromAttribute($id,$title = '', $qty = 1, $price = 0, $meta = [],$variation_id = ''){
        $item = new self();

        $item->object_id = $id;
        $item->object_model = $meta['object_model'] ?? '';
        $item->object_author_id = $meta['object_author_id'] ?? 0;
        $item->qty = $qty;
        $item->title = $title;
        $item->meta = $meta;
        $item->price = $price;
        $item->variation_id = $variation_id;
        $item->status = 'draft';

        return $item;
    }

    public function variation(){
        return $this->belongsTo(ProductVariation::class,'variation_id');
    }

    protected function generateId(){
        if(!$this->id)
        $this->id = uniqid().rand(0,99999);
    }

    public function subtotal(): Attribute
    {
       return Attribute::make(
           get:function($value){
               return $this->price * $this->qty + $this->extra_price_total;
           }
       );
    }
    public function subtotalDiscount(): Attribute
    {
        return Attribute::make(
            get:function($value){
                return $this->price * $this->qty + $this->extra_price_total - $this->discount_amount;
            }
        );
    }

    public function getDetailUrl(){
        if ($this->product) {
            return $this->product->getDetailUrl();
        }
        return '';
    }

    public function extraPriceTotal(): Attribute
    {
        return Attribute::make(
            get:function($value){
                $t = 0;
                if(!empty($this->meta['extra_prices']))
                {
                    foreach ($this->meta['extra_prices'] as $extra_price){
                        $t += (float)($extra_price['price']);
                    }
                }
                return $t;
            }
        );
    }

    public static function fromArray($data){
        $item = new self();
        foreach ($data as $k=>$v){
            if($k == 'model') continue;
            if($k == 'variation') continue;
            $item->setAttribute($k,$v);
        }
        return $item;
    }

    public function toArray()
    {
        return $this->attributesToArray();
    }
}
