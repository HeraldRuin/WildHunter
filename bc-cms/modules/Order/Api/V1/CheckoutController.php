<?php


namespace Modules\Order\Api\V1;


use Modules\Order\Helpers\CartApiManagement;

class CheckoutController extends \Themes\Base\Controllers\Order\CheckoutController
{

    protected $cart_manager;
    public function __construct(CartApiManagement $cart_manager)
    {
        parent::__construct($cart_manager);
    }
}
