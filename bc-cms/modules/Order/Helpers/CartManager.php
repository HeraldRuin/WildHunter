<?php


namespace Modules\Order\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Modules\Coupon\Models\Coupon;
use Modules\Order\Models\CouponOrder;
use Modules\Order\Models\Cart;
use Modules\Order\Models\CartItem;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderItem;
use Modules\Product\Models\Product;
use Modules\Product\Models\ShippingZone;
use Modules\Product\Models\ShippingZoneLocation;
use Modules\Product\Models\ShippingZoneMethod;
use Modules\Product\Models\TaxRate;

class CartManager
{

    protected $session_key='bc_cart_id';

    protected $_cart;


    /**
     * Get Cart
     *
     * @param bool $create_draft Always create a new cart if not exist
     * @return Cart
     */
    public function cart($create_draft = false){

        $cartClass = app(Cart::class);
        if(!$this->_cart){
            $cart_id = $this->cartId();
            if($cart_id){
                $cart = $cartClass::findByCode($cart_id);

                // this is to validate the cart is exist with session key,
                // if not then clear the session key
                if(!$cart){
                    $this->clear();
                }

                // If cart has no customer and user is logged in, set customer to current user
                if(!$cart->customer_id && Auth::check()){
                    $cart->customer_id = Auth::user()->id;
                    $cart->save();
                }

                // Validate cart status
                // clear session if cart does not need checkout, eg: status is completed or failed ... 
                if(!$cart->needCheckout()){
                    $this->clear();
                }

            }
            if(empty($cart)){
                if($create_draft){
                    $cart = $cartClass->createDraft();
                    session([$this->session_key => $cart->code]);
                }else{
                    $cart =  $cartClass;
                }
            }
            $this->_cart = $cart;
        }

        return $this->_cart;
    }

    public function add($product_id, $name = '', $qty = 1, $price = 0,$meta = [], $variation_id = false){

        $cartItemClass = app(CartItem::class);
        $cart = $this->cart(true);

        // Save cart to database if not exist,
        // because we always need an cart id before add item
        if(!$cart->id){
            $cart->save();

            // store cart code to session
            // NOTE: This is very important,
            // because we need to store the cart code to session
            // to get the cart id when we need to find item
            session([$this->session_key => $cart->code]);
        }

        $item = $this->findItem($product_id,$variation_id, $meta['object_model'] ?? false);
        if(!$item){
            if ($product_id instanceof Product){
                $item = $cartItemClass::fromProduct($product_id,$qty,$price,$meta, $variation_id);
            }else{
                $item = $cartItemClass::fromAttribute($product_id,$name,$qty,$price, $meta, $variation_id);
            }

            $item->syncTotal(); //  re-calculate subtotal, total of item

            $this->pushItem($item);

        }else{

            $item->qty += $qty;
            $item->updatePrice(); //  get price from database, in case price has changed
            $item->syncTotal(); //  re-calculate subtotal, total of item

            $cart->syncTotal(); //  update subtotal, total of cart
            $cart->save();
        }

        return $item;
    }

    /**
     * Get Cart Item by ID
     *
     * @param int $cartItemId
     * @return CartItem|null|mixed
     */
    public function item($cartItemId){

       return $this->cart()->items()->where('id',$cartItemId)->first();
    }

    /**
     * Get Cart Item by Product ID and Variation ID
     *
     * @param int|Product $product_id
     * @param int|false $variation_id
     * @param string|false $type
     * @return CartItem|null
     */
    public function findItem($product_id, $variation_id = false, $type = false){

        $cart = $this->cart();
        if(!$cart->id){
            // Always return false if cart not exist (likely a empty cart)
            return false;
        }
        $currentItems  = $cart->items;

        if($product_id instanceof Product){
            $currentItems = $currentItems->where('object_id',$product_id->id);
        }else{
            $currentItems = $currentItems->where('object_id',$product_id);
        }
        if($variation_id){
            $currentItems = $currentItems->where('variation_id',$variation_id);
        }
        if($type){
            $currentItems = $currentItems->where('object_model',$type);
        }
        return $currentItems->first();
    }

    /**
     * Update Cart Item
     *
     * @param $cart_item_id
     * @param int $qty
     * @param false $price
     * @param false $variation_id
     * @return bool|Collection|null
     */
    public function update($cart_item_id,$qty = 1,$price = false, $meta = [], $variation_id = false){

        $find = $this->item($cart_item_id);
        if($find){
            $find->qty = $qty;

            if($qty <= 0){
                return $this->remove($cart_item_id);
            } else {
                $find->save();
            }
        }

        return null;
    }

    /**
     * Remove cart item by id
     *
     * @param $cart_item_id
     * @return boolean
     *
     */
    public function remove($cart_item_id){
        $this->cart()->items()->where('id',$cart_item_id)->delete();
        $this->cart()->load('items');
        return true;
    }

    /**
     * @return bool
     */
    public function clear(){
        Session::forget($this->session_key);
        $this->_cart = null;
        return true;
    }

    /**
     * Get Cart Items
     *
     * @return CartItem[]
     */
    public function items(){

        return $this->cart()->items ?? [];
    }

    /**
     * Return number of cart items
     *
     * @return int
     */
    public function count(){
        return count($this->items());
    }

    /**
     * Get Subtotal
     *
     * @return float
     */
    public function subtotal(){
        return $this->cart()->subtotal();
    }

    public function discountTotal(){
        return $this->cart()->discountTotal();
    }

    public function shippingTotal(){
        return $this->cart()->shippingTotal();
    }

    /**
     * Get Subtotal
     *
     * @return float
     */
    public function total(){
        return $this->cart()->total;
    }

    public function fragments(){
        return [
            '.header_content .bc-mini-cart'=>view('order.cart.mini-cart')->render(),
        ];
    }

    /**
     * @return Coupon[]|mixed
     */
    public function getCoupon(){
    	return $this->cart()->coupons;
    }

    public function storeCoupon(Coupon $coupon){
    	return $this->cart()->storeCoupon($coupon);
    }
    public function removeCoupon(Coupon $coupon){

        return $this->cart()->removeCoupon($coupon);
    }


    public function pushItem(CartItem $cartItem){
        $this->cart()->addItem($cartItem);
    }


    public function validate(){
        return $this->cart()->validate();
    }
    public function validateItem(CartItem $item,$qty){
        $model = $item->product;
        if($model){
            $model->addToCartValidate($qty,$item->variation_id);
        }

        // validate max quantity
        if($model->getMaxQuantity() && $qty > $model->getMaxQuantity()){
            return false;
        }
        return true;
    }

    public function getMethodShipping($country){
        $data = [
            'status' => 0,
            'shipping_methods' => [],
            'message' => __("There are no shipping options available."),
        ];
        $shipping_methods = [];
        // Method for country
        $zone_location = ShippingZoneLocation::where("location_code",$country)->first();
        if(!empty($zone_location)) {
            $zone = ShippingZone::find($zone_location->zone_id);
            $shipping_methods = $zone->shippingMethodsAvailable;
        }elseif(!empty($shipping_methods_default = ShippingZoneMethod::where("zone_id",0)->where('is_enabled',1)->orderBy("order","asc")->get())){
            // Method default
            $shipping_methods = $shipping_methods_default;
        }
        if(!empty($shipping_methods)){
            foreach ( $shipping_methods as $method){
                $translate = $method->translate();
                $data['status'] = 1;
                $data['message'] = "";
                $data['shipping_methods'][] = [
                    'method_id'=>$method->id,
                    'method_title'=>$translate->title,
                    'method_cost'=>$method->cost,
                ];
            }
        }
        return $data;
    }

    public function addShipping($country , $shipping_method){
        $res = $this->cart()->addShipping($country,$shipping_method);

        $this->cart()->save();

        return $res;
    }

    public function getTaxRate($billing_country , $shipping_country)
    {
        return $this->cart()->getTaxRate($billing_country , $shipping_country);
    }

    public function addTax($billing_country , $shipping_country){
        return $this->cart()->addTax($billing_country,$shipping_country);
    }

    public function cartId(){
        return session($this->session_key);
    }
}
