<?php

namespace Modules\Visa;

use Modules\ModuleServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\User\Helpers\PermissionHelper;
use Modules\Visa\Models\VisaService;
use Livewire\Livewire;
use Modules\Visa\Pages\Components\Filter;
use Modules\Visa\Pages\Components\SearchForm;
use Modules\Visa\Pages\Components\BookingForm;
use Modules\Visa\Helpers\VisaFormSettings;
use Modules\Form\FormBuilder;
use Modules\Visa\Pages\Components\ApplicantFormView;

class ModuleProvider extends ModuleServiceProvider
{
    public function boot()
    {
        $this->registerRoutes();
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        $this->mergeConfigFrom(__DIR__ . '/Configs/config.php', 'visa');

        PermissionHelper::add([
            'visa_view',
            'visa_create',
            'visa_update',
            'visa_delete',
            'visa_manage_others',
        ]);

        // Register Livewire components
        Livewire::component('visa::filter', Filter::class);
        Livewire::component('visa::search-form', SearchForm::class);
        Livewire::component('visa::booking-form', BookingForm::class);
        Livewire::component('visa::applicant-form-view', ApplicantFormView::class);

        FormBuilder::registerProvider('visa_application_form', '\Modules\Visa\Helpers\VisaFormSettings@getForm');

        $this->app->singleton(VisaFormSettings::class);
    }

    public function registerRoutes()
    {
        Route::middleware('web')
        ->group(function(){
            $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
        });

        // Admin
        Route::middleware(['web', 'dashboard'])
            ->prefix(config('admin.admin_route_prefix'))
            ->group(function(){
                $this->loadRoutesFrom(__DIR__ . '/Routes/admin.php');
            });
    }


    static function getAdminMenu(){
        if(VisaService::isEnable()){
            return [];
        }
        return [
            'visa'=>[
                "position"=> 55,
                'url'        => route('visa.admin.index'),
                'title'      => __('Visa'),
                'icon'       => 'ion-md-briefcase',
                'permission' => 'visa_view',
                'group'      => 'catalog',
                'children'   => [
                    'index'=>[
                        'url'        => route('visa.admin.index'),
                        'title'      => __('All Visa'),
                        'permission' => 'visa_view',
                    ],
                    'type'=>[
                        'url'        => route('visa.admin.type.index'),
                        'title'      => __('Visa Type'),
                        'permission' => 'visa_manage_others',
                    ],
                ],
            ],
        ];
    }

    static function getBookableServices(){
        if(!VisaService::isEnable()){
            return [];
        }
        return [
            'visa'=>VisaService::class,
        ];
    }
}
