<?php

use App\Http\Middleware\EnsurePlanLimit;
use App\Http\Middleware\EnsureTenantActive;
use App\Http\Middleware\ResolveTenant;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant'          => ResolveTenant::class,
            'tenant.active'   => EnsureTenantActive::class,
            'plan.limit'      => EnsurePlanLimit::class,
            'auth.superadmin' => \App\Http\Middleware\AuthSuperadmin::class,
            'branch.active'   => \App\Http\Middleware\EnsureBranchActive::class,
            'demo.guard'      => \App\Http\Middleware\PreventDemoDestruction::class,
        ]);

        // Send unauthenticated users to the login page of the panel they were
        // trying to reach (there is no generic 'login' route in this app).
        $middleware->redirectGuestsTo(function ($request) {
            $slug = $request->route('lab_slug');

            if (!$slug) {
                return route('superadmin.login');
            }
            if ($request->routeIs('branch.*')) {
                return route('branch.login', $slug);
            }
            if ($request->routeIs('patient.*')) {
                return route('patient.login', $slug);
            }
            return route('tenant.login', $slug);
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
