<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Configuration\Exceptions;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            '*/gateway_callback/*',
            '*/callback/*',
            '*/order/confirm/*',
        ]);

        // Redirect to installer if not installed
        $middleware->append(\App\Http\Middleware\RedirectToInstaller::class);

        $middleware->web([
            \App\Http\Middleware\RedirectForMultiLanguage::class,
            \App\Http\Middleware\SetLanguageForAdmin::class,
            \App\Http\Middleware\SetCurrentCurrency::class,
            \App\Http\Middleware\RequireChangePassword::class,
            \App\Http\Middleware\AttachTraceId::class,
        ]);
        $middleware->api([
            \App\Http\Middleware\MayAuthenticateWithSanctum::class,
        ], [
            \App\Http\Middleware\SetLanguageForApi::class,
            \App\Http\Middleware\RequireChangePassword::class,
        ]);

        $middleware->alias([
            "dashboard" => \App\Http\Middleware\Dashboard::class,
            "translation_manager" => \App\Http\Middleware\TranslationManager::class,
            "system_log_view" => \App\Http\Middleware\CheckForLogPermission::class,
            "set_language_for_api" => \App\Http\Middleware\SetLanguageForApi::class,
            "pro_plan" => \App\Pro\Middlewares\ProPlan::class,
        ]);

        // Sanctum Middleware
        $middleware->statefulApi();
        $middleware->throttleApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $e, $request) {

            if ($e instanceof \App\Exceptions\BaseException) {
                $message = $e->getDomain() . '.errors.' . $e->getErrorCode();

                return response()->json([
                    'success' => false,
                    'message' => __($message),
                    'error_code' => $e->getErrorCode(),
                    'trace_id' => $request->attributes->get('trace_id')
                ], $e->getStatus());
            }

            return null;
        });
    })->create();
