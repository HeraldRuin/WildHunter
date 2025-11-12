<?php


namespace Modules\Order\Api\V1;


use Illuminate\Http\Request;
use Modules\Order\Helpers\CartApiManagement;

class CartController extends \Themes\Base\Controllers\Order\CartController
{

    public function __construct(CartApiManagement $cart_manager)
    {
        parent::__construct($cart_manager);
    }

}
