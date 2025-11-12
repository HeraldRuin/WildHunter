<?php


namespace Modules\Order\Helpers;


use Illuminate\Support\Facades\Cache;

class CartApiManagement extends CartManager
{
    protected static $_cart_id;

    public static function cart_id(){

        if(!static::$_cart_id) static::$_cart_id = request('cart_id');

        return static::$_cart_id;

    }
}
