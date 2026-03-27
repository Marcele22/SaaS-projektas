<?php

use App\Http\Controllers\Stripe\StripeWebhookController;
use App\Http\Controllers\Tenant\CustomerSubscriptionController;
use App\Http\Controllers\Tenant\StripeCheckoutController;
use App\Http\Controllers\Tenant\SubscriptionPlanController;
use App\Http\Controllers\Tenant\WeeklyScheduleTemplateController;
use App\Http\Controllers\Tenant\WorkoutScheduleController;
use App\Http\Controllers\Tenant\WorkoutSessionController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

Route::middleware([
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->prefix('tenant')->group(function () {
    Route::get('/plans', [SubscriptionPlanController::class, 'index']);

    Route::get('/subscriptions', [CustomerSubscriptionController::class, 'index']);
    Route::post('/subscriptions', [CustomerSubscriptionController::class, 'store']);
    Route::get('/subscriptions/{subscription}', [CustomerSubscriptionController::class, 'show']);
    Route::post('/subscriptions/{subscription}/activate', [CustomerSubscriptionController::class, 'activate']);
    Route::post('/subscriptions/{subscription}/cancel', [CustomerSubscriptionController::class, 'cancel']);
    Route::post('/subscriptions/{subscription}/payment-failed', [CustomerSubscriptionController::class, 'markPaymentFailed']);
    Route::post('/subscriptions/{subscription}/checkout', [StripeCheckoutController::class, 'create']);

    Route::put('/subscriptions/{subscription}/templates', [WeeklyScheduleTemplateController::class, 'replace']);

    Route::post('/subscriptions/{subscription}/schedule/generate', [WorkoutScheduleController::class, 'generate']);
    Route::get('/subscriptions/{subscription}/schedule/preview', [WorkoutScheduleController::class, 'preview']);

    Route::get('/sessions', [WorkoutSessionController::class, 'index']);
    Route::get('/sessions/{workoutSession}', [WorkoutSessionController::class, 'show']);
});