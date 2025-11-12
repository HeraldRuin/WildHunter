<?php


namespace Modules\Order\Api\V1;


use Modules\Order\Helpers\CartApiManagement;
use Modules\Order\Helpers\CartManager;

class CouponController extends \Themes\Base\Controllers\Order\CouponController
{

    public function __construct(CartApiManagement $cartApiManagement)
    {
        app()->bind(CartManager::class,CartApiManagement::class);
        parent::__construct($cartApiManagement);
    }
}
