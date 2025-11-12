<?php

namespace Modules\Order\Controllers;

use App\Http\Controllers\Controller;
use Modules\Order\Helpers\CartManager;

class ToCheckoutController extends Controller
{
    protected $cartManager;

    public function __construct(CartManager $cart_manager)
    {
        $this->cartManager = $cart_manager;
    }

    public function index(){

        $cart = $this->cartManager->cart();

        if ($cart->code)
        {
            return redirect(route('checkout.detail',['code'=>$cart->code]));
        }

        return redirect()->route('cart');
    }
}
