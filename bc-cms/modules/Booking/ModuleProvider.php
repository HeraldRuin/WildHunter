<?php
namespace Modules\Booking;

use Illuminate\Support\Facades\Event;
use Modules\Booking\Events\BookingCreatedEvent;
use Modules\Booking\Events\BookingUpdatedEvent;
use Modules\Booking\Events\SetPaidAmountEvent;
use Modules\Booking\Listeners\BookingCreatedListen;
use Modules\Booking\Listeners\BookingUpdateListen;
use Modules\Booking\Listeners\SetPaidAmountListen;
use Modules\Core\Helpers\SitemapHelper;
use Modules\ModuleServiceProvider;
use Modules\Core\Helpers\AdminMenuManager;
use Modules\Booking\Helpers\PaymentGatewayManager;
use Modules\Booking\Gateways\OfflinePaymentGateway;
use Modules\Booking\Gateways\PaypalGateway;
use Modules\Booking\Gateways\StripeCheckoutGateway;
use Modules\Booking\Gateways\PaystackGateway;
use Modules\Booking\Gateways\PayrexxGateway;

class ModuleProvider extends ModuleServiceProvider
{
    public function boot(SitemapHelper $sitemapHelper)
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        Event::listen(BookingCreatedEvent::class,BookingCreatedListen::class);
        Event::listen(BookingUpdatedEvent::class,BookingUpdateListen::class);
        Event::listen(SetPaidAmountEvent::class,SetPaidAmountListen::class);

        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');

        AdminMenuManager::register_group('catalog', __("Catalog"), 100);


        // Register gateways
        PaymentGatewayManager::register('offline', OfflinePaymentGateway::class);
        PaymentGatewayManager::register('paypal', PaypalGateway::class);
        PaymentGatewayManager::register('stripe', StripeCheckoutGateway::class);
        PaymentGatewayManager::register('paystack', PaystackGateway::class);
        PaymentGatewayManager::register('payrexx', PayrexxGateway::class);
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouterServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
    }

}
