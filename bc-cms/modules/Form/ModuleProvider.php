<?php

namespace Modules\Form;

use Modules\ModuleServiceProvider;
use Modules\User\Helpers\PermissionHelper;
use Illuminate\Support\Facades\Route;
use Modules\Form\Fields\TextField;
use Modules\Form\Fields\TextareaField;
use Modules\Form\Fields\RadioField;
use Modules\Form\Fields\CheckboxField;
use Modules\Form\Fields\DropdownField;
use Modules\Form\Fields\DateField;
use Livewire\Livewire;

class ModuleProvider extends ModuleServiceProvider
{
    public function boot()
    {
        // $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/Views', 'Form');

        $this->registerRoutes();

        // PermissionHelper::add([
        //     'form_view',
        //     'form_create',
        //     'form_edit',
        //     'form_delete',
        //     'form_manage_others',

        //     'form_submission_view',
        //     'form_submission_create',
        //     'form_submission_edit',
        //     'form_submission_manage_others',

        // ]);

        $this->app->singleton(FormBuilder::class, function ($app) {
            return new FormBuilder();
        });

        // $this->registerFormFields();

        Livewire::component('form::simple-form', SimpleForm::class);
    }

    public function registerRoutes()
    {
        // Route::middleware(['web', 'dashboard'])
        //     ->prefix('admin/module/form')
        //     ->group(function () {
        //         $this->loadRoutesFrom(__DIR__ . '/Routes/admin.php');
        //     });
            
        Route::middleware(['web'])
            ->group(function () {
                $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
            });
    }

    public function registerFormFields()
    {
        FormBuilder::registerGroup('basic', __('Basic'), 10);

        FormBuilder::registerType('input', TextField::class, 'basic');
        FormBuilder::registerType('textarea', TextareaField::class, 'basic');
        FormBuilder::registerType('radio', RadioField::class, 'basic');
        FormBuilder::registerType('checkbox', CheckboxField::class, 'basic');
        FormBuilder::registerType('dropdown', DropdownField::class, 'basic');
        FormBuilder::registerType('date', DateField::class, 'basic');
    }
}