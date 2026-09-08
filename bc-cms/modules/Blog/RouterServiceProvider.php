<?php
namespace Modules\Blog;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouterServiceProvider extends ServiceProvider
{
    protected $adminModuleNamespace = 'Modules\Blog\Admin';

    public function boot()
    {
        parent::boot();
    }

    public function map()
    {
        $this->mapAdminRoutes();
    }

    protected function mapAdminRoutes()
    {
        Route::middleware(['web', 'dashboard'])
            ->namespace($this->adminModuleNamespace)
            ->prefix(config('admin.admin_route_prefix') . '/module/blog')
            ->group(__DIR__ . '/Routes/admin.php');
    }
}
