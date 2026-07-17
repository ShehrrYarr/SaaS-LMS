<?php

use App\Http\Controllers\Patient\Auth\LoginController as PatientLoginController;
use App\Http\Controllers\Patient\DashboardController as PatientDashboard;
use App\Http\Controllers\Patient\InvoiceController as PatientInvoice;
use App\Http\Controllers\Patient\ReportController as PatientReport;
use Illuminate\Support\Facades\Route;

Route::prefix('{lab_slug}/portal')->middleware(['tenant', 'tenant.active'])->name('patient.')->group(function () {

    // Auth
    Route::get('/login', [PatientLoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [PatientLoginController::class, 'login'])->name('login.post');
    Route::post('/logout', [PatientLoginController::class, 'logout'])->name('logout');

    // Protected patient routes
    Route::middleware('auth:patient')->group(function () {
        Route::get('/dashboard', [PatientDashboard::class, 'index'])->name('dashboard');
        Route::get('/reports', [PatientReport::class, 'index'])->name('reports.index');
        Route::get('/reports/{order}/download', [PatientReport::class, 'download'])->name('reports.download');
        Route::get('/invoices', [PatientInvoice::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{invoice}/download', [PatientInvoice::class, 'download'])->name('invoices.download');
    });
});
