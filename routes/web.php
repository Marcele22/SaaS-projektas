<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index']) ->name('home');

Route::post('/payment', [CreditController::class, 'store'])->name('payment');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');
});
Route::get('/payment/success', [CreditController::class, 'success'])->name('credit.success');
Route::get('/payment/cancel', [CreditController::class, 'cancel'])->name('credit.cancel');
