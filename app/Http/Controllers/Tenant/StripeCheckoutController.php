<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\CustomerSubscription;
use App\Services\Stripe\StripeCheckoutService;
use Illuminate\Http\JsonResponse;

class StripeCheckoutController extends Controller
{
    public function create(
        CustomerSubscription $subscription,
        StripeCheckoutService $stripeCheckoutService
    ): JsonResponse {
        $url = $stripeCheckoutService->createCheckout($subscription);

        return response()->json([
            'checkout_url' => $url,
        ]);
    }
}