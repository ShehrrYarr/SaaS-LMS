<?php

use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

// Landing page + one-click demo logins
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/demo/{role}', [LandingController::class, 'demoLogin'])->name('demo.login');

// Include modular route files
require __DIR__.'/superadmin.php';
require __DIR__.'/patient.php';
require __DIR__.'/branch.php';
require __DIR__.'/tenant.php';
