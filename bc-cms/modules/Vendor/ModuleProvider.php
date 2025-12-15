<?php
namespace Modules\Vendor;

use Illuminate\Support\ServiceProvider;
use Modules\ModuleServiceProvider;
use Modules\Vendor\Models\VendorPayout;

class ModuleProvider extends ModuleServiceProvider
{

    public function boot(){
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
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

    public static function getAdminMenu()
    {
        $count = VendorPayout::countInitial();
        return [
            'payout'=>[
                "position"=>70,
                'url'        => route('vendor.admin.payout.index'),
                'title'      => __("Payouts :count",['count'=>$count ? sprintf('<span class="badge badge-warning">%d</span>',$count) : '']),
                'icon'       => 'icon ion-md-card',
                'permission' => 'user_create',
                'group' => 'system'
            ]
        ];
    }


    public static function getTemplateBlocks(){
        return [
            'vendor_register_form'=>"\\Modules\\Vendor\\Blocks\\VendorRegisterForm",
            'vendor_list'=>"\\Modules\\Vendor\\Blocks\\ListVendor",
        ];
    }
    public static function getUserMenu()
    {
        return [];
    }
}
