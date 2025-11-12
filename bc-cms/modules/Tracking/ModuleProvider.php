<?php
namespace Modules\Tracking;
use Modules\ModuleServiceProvider;

class ModuleProvider extends ModuleServiceProvider
{

    public function boot(){

        // Disable for now
        // $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

    }
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        // $this->app->register(RouterServiceProvider::class);
        $this->app->singleton('tracker', function () {
            return app()->make(Tracker::class);
        });
    }
}
