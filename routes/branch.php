<?php

use App\Http\Controllers\Branch\Auth\LoginController as BranchLoginController;
use App\Http\Controllers\Branch\CustomerController as BranchCustomer;
use App\Http\Controllers\Branch\DashboardController as BranchDashboard;
use App\Http\Controllers\Branch\InvoiceController as BranchInvoice;
use App\Http\Controllers\Branch\OrderController as BranchOrder;
use Illuminate\Support\Facades\Route;

Route::prefix('{lab_slug}/branch')->middleware(['tenant', 'tenant.active'])->name('branch.')->group(function () {

    // Auth
    Route::get('/login', [BranchLoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [BranchLoginController::class, 'login'])->name('login.post');
    Route::post('/logout', [BranchLoginController::class, 'logout'])->name('logout');

    // Protected branch routes
    Route::middleware(['auth:branch', 'branch.active', 'demo.guard'])->group(function () {
        Route::get('/dashboard', [BranchDashboard::class, 'index'])->name('dashboard');

        // Customers (patients registered by this branch)
        Route::get('/customers', [BranchCustomer::class, 'index'])->name('customers.index');
        Route::get('/customers/create', [BranchCustomer::class, 'create'])->name('customers.create');
        Route::post('/customers', [BranchCustomer::class, 'store'])
             ->middleware('plan.limit:patient')->name('customers.store');
        Route::get('/customers/{patient}', [BranchCustomer::class, 'show'])->name('customers.show');
        Route::get('/customers/{patient}/edit', [BranchCustomer::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{patient}', [BranchCustomer::class, 'update'])->name('customers.update');

        // Test orders
        Route::get('/orders', [BranchOrder::class, 'index'])->name('orders.index');
        Route::get('/orders/create', [BranchOrder::class, 'create'])->name('orders.create');
        Route::post('/orders', [BranchOrder::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [BranchOrder::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/collect', [BranchOrder::class, 'markCollected'])->name('orders.collect');
        Route::get('/orders/{order}/report', [BranchOrder::class, 'downloadReport'])->name('orders.report');

        // Invoices
        Route::get('/invoices', [BranchInvoice::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{invoice}', [BranchInvoice::class, 'show'])->name('invoices.show');
        Route::post('/invoices/{invoice}/payments', [BranchInvoice::class, 'addPayment'])->name('invoices.payment.add');
    });
});
