<?php

use App\Http\Controllers\Tenant\AppointmentController;
use App\Http\Controllers\Tenant\BranchController;
use App\Http\Controllers\Tenant\LabBankController;
use App\Http\Controllers\Tenant\Auth\LoginController as TenantLoginController;
use App\Http\Controllers\Tenant\BillingController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\PatientController;
use App\Http\Controllers\Tenant\RoleController;
use App\Http\Controllers\Tenant\SettingsController;
use App\Http\Controllers\Tenant\StaffController;
use App\Http\Controllers\Tenant\TestCatalogController;
use App\Http\Controllers\Tenant\TestOrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('{lab_slug}')->middleware(['tenant', 'tenant.active'])->name('tenant.')->group(function () {

    // Auth
    Route::get('/login', [TenantLoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [TenantLoginController::class, 'login'])->name('login.post');
    Route::post('/logout', [TenantLoginController::class, 'logout'])->name('logout');

    // Protected tenant routes
    Route::middleware(['auth', 'demo.guard'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Staff management (Lab Admin only)
        Route::middleware('can:manage-staff')->group(function () {
            Route::resource('staff', StaffController::class);
            Route::resource('roles', RoleController::class);

            // Branch management (Enterprise plans only)
            Route::middleware('plan.limit:branch-feature')->group(function () {
                Route::post('branches', [BranchController::class, 'store'])
                     ->middleware('plan.limit:branch')->name('branches.store');
                Route::resource('branches', BranchController::class)->except('store');
                Route::post('branches/{branch}/reset-password', [BranchController::class, 'resetPassword'])
                     ->name('branches.reset-password');
            });
        });

        // Patients (Receptionist + Admin)
        Route::middleware('plan.limit:patient')->group(function () {
            Route::post('patients', [PatientController::class, 'store'])->name('patients.store');
        });
        Route::resource('patients', PatientController::class)->except('store');
        Route::post('patients/{patient}/reset-password', [PatientController::class, 'resetPassword'])->name('patients.reset-password');

        // Appointments
        Route::resource('appointments', AppointmentController::class);

        // Test Catalog
        Route::resource('tests', TestCatalogController::class);

        // Test Orders
        Route::resource('orders', TestOrderController::class);
        Route::patch('orders/{order}/status', [TestOrderController::class, 'updateStatus'])->name('orders.status');
        Route::patch('orders/{order}/items/{item}/result', [TestOrderController::class, 'updateResult'])->name('orders.result');

        // Billing
        Route::resource('billing', BillingController::class)->parameters(['billing' => 'invoice']);
        Route::post('billing/{invoice}/payments', [BillingController::class, 'addPayment'])->name('billing.payment.add');
        Route::delete('billing/{invoice}/payments/{payment}', [BillingController::class, 'deletePayment'])->name('billing.payment.delete');
        Route::get('billing/{invoice}/pdf', [BillingController::class, 'downloadPdf'])->name('billing.pdf');

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings/password', [SettingsController::class, 'updatePassword'])
             ->middleware('can:manage-settings')->name('settings.password');
        Route::post('/settings/smtp', [SettingsController::class, 'updateSmtp'])->name('settings.smtp');
        Route::post('/settings/branding', [SettingsController::class, 'updateBranding'])->name('settings.branding');
        Route::post('/settings/smtp/test', [SettingsController::class, 'testSmtp'])->name('settings.smtp.test');
        Route::get('/settings/domain', [SettingsController::class, 'customDomain'])->name('settings.domain');
        Route::get('/settings/appearance', [SettingsController::class, 'appearance'])->name('settings.appearance');
        Route::post('/settings/appearance', [SettingsController::class, 'updateAppearance'])->name('settings.appearance.save');
        Route::post('/settings/appearance/reset', [SettingsController::class, 'resetAppearance'])->name('settings.appearance.reset');

        // PDF Template Builder
        Route::get('/settings/template-builder', [SettingsController::class, 'templateBuilder'])->name('settings.template-builder');
        Route::post('/settings/template-builder/{type}', [SettingsController::class, 'saveTemplate'])->name('settings.template-builder.save');

        // Bank management (in settings)
        Route::get('/settings/banks', [LabBankController::class, 'index'])->name('settings.banks');
        Route::post('/settings/banks', [LabBankController::class, 'store'])->name('settings.banks.store');
        Route::patch('/settings/banks/{bank}', [LabBankController::class, 'update'])->name('settings.banks.update');
        Route::delete('/settings/banks/{bank}', [LabBankController::class, 'destroy'])->name('settings.banks.destroy');

        // Report PDF
        Route::get('orders/{order}/report', [TestOrderController::class, 'downloadReport'])->name('orders.report');
    });
});
