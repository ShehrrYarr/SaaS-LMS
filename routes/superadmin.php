<?php

use App\Http\Controllers\Superadmin\AuthController as SuperadminAuthController;
use App\Http\Controllers\Superadmin\DashboardController;
use App\Http\Controllers\Superadmin\PlanController;
use App\Http\Controllers\Superadmin\SettingsController;
use App\Http\Controllers\Superadmin\TenantController;
use Illuminate\Support\Facades\Route;

// Auth (no guard required)
Route::prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/login', [SuperadminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [SuperadminAuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [SuperadminAuthController::class, 'logout'])->name('logout');

    // Protected superadmin routes
    Route::middleware('auth.superadmin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('plans', PlanController::class);
        Route::resource('tenants', TenantController::class);
        Route::patch('tenants/{tenant}/status', [TenantController::class, 'updateStatus'])->name('tenants.status');
        Route::post('tenants/{tenant}/reset-password', [TenantController::class, 'resetAdminPassword'])->name('tenants.reset-password');
        Route::post('tenants/{tenant}/toggle-demo', [TenantController::class, 'toggleDemo'])->name('tenants.toggle-demo');

        Route::get('settings', [SettingsController::class, 'index'])->name('settings');
        Route::post('settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
        Route::post('settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    });
});
