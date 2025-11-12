<?php


namespace Modules\Order\Models;


use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Modules\Order\Models\CouponOrder;
use Modules\Order\Helpers\CartManager;

class Cart extends Order
{

    public static function findByCode($code){
        return parent::whereCode($code)->first();
    }

    // For Cart use method not property
    // TODO: Think about using property for Cart
    public function total()
    {
        $subTotal = $this->subtotal();
        $discount = $this->discountTotal();
        $shipping = $this->shippingTotal();
        $total = $subTotal + $shipping - $discount;

        return max(0,$total);
    }

    /**
     * Get Subtotal
     *
     * @return float
     */
    // For Cart use method not property
    // TODO: Think about using property for Cart
    public function subtotal()
    {
        return $this->items->sum('subtotal');
    }

    public function discountTotal(){
        return $this->discount_amount;
    }

    public function shippingTotal(){
        return $this->items->sum('shipping_amount') + $this->shipping_amount;
    }

    /**
     * Create draft cart
     *
     * @return Cart
     */
    public static function createDraft(){
        $cart = new self();
        $cart->customer_id = auth()->id();
        $cart->status = Order::DRAFT;
        $cart->locale = app()->getLocale();
        $cart->save();

        return $cart;
    }



    public function addItem(CartItem $item){
        $this->items()->save($item);
        $this->syncTotal();
        $this->save();
        $this->load('items');
        return true;
    }


    /**
     * Calculate all total, tax, discount, shipping ...
     * and ready for checkout
     * 
     * Cart to Order
     * @param Request $request
     *
     * @return Order
     * @throws \Exception
     */
    public function prepareCheckout()
    {

        // TODO: apply shipping info if needed

        $this->locale = app()->getLocale();
        $this->discount_amount = $this->discountTotal();

        /**
         * @var CartItem[] $items;
         */
        $items = $this->items;
        foreach ($items as $order_item){
            $model = $order_item->product;
            if(!$model){
                $order_item->delete();
                throw new \Exception(__("Product: :id does not exists",['id'=>$order_item->object_id]));
            }
            $order_item->price = $model->getBuyablePrice();
            $order_item->locale = app()->getLocale();
            $order_item->calculateTotal();
            $order_item->calculateCommission();

            // Downloadable
            if($model->download && $model->download_expiry_days){
                $order_item->addMeta('download_expired_at',time() + $model->download_expiry_days * DAY_IN_SECONDS);
            }

            $order_item->save();
        }

        $this->syncTotal();

        //Tax
        $this->syncTaxChange();

        $setting_expired_at = setting_item('product_hold_stock',60);
        if($setting_expired_at){
            $this->expired_at = date('Y-m-d H:i:s',time() + $setting_expired_at * MINUTE_IN_SECONDS);
        }

        $this->save();

        return $this;
    }

    public function items()
    {
        return $this->hasMany(CartItem::class,'order_id');
    }
}
