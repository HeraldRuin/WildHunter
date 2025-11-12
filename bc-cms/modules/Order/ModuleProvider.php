<?php

namespace Modules\Order;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Helpers\AdminMenuManager;
use Modules\ModuleServiceProvider;
use Modules\Order\Gateways\OfflinePaymentGateway;
use Modules\Order\Gateways\PaypalGateway;
use Modules\Order\Gateways\StripeCheckoutGateway;
use Modules\Order\Helpers\CartManager;
use Modules\Order\Helpers\PaymentGatewayManager;
use Modules\User\Helpers\PermissionHelper;

class ModuleProvider extends ModuleServiceProvider
{

    public function boot()
    {

        $this->publishes([
            __DIR__ . '/Config/config.php' => config_path('order.php'),
        ]);

        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

        AdminMenuManager::register("orders", [$this, 'getAdminMenu']);
        AdminMenuManager::register_group('sale', __("Sales"), 20);

        PaymentGatewayManager::register('offline', OfflinePaymentGateway::class);
        PaymentGatewayManager::register('paypal', PaypalGateway::class);
        PaymentGatewayManager::register('stripe', StripeCheckoutGateway::class);
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/Config/config.php', 'order'
        );
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(EventServiceProvider::class);

        $this->app->singleton(CartManager::class, function () {
            return new CartManager();
        });

        
        PermissionHelper::add([
            'order_view',
            'order_create',
            'order_update',
            'order_delete',
            'order_manage_others',
        ]);
    }

    public static function getAdminMenu()
    {
        return [
            'order' => [
                "position" => 45,
                'url' => route('order.admin.index'),
                'title' => __("Orders"),
                'icon' => 'fa fa-dashboard',
                'permission' => 'order_view',
                'group' => 'sale'
            ]
        ];
    }
}
