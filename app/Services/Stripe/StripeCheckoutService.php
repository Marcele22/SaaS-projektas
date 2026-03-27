<?php

namespace App\Services\Stripe;

use App\Models\CustomerSubscription;
use App\Models\SubscriptionPlan;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class StripeCheckoutService
{
    public function createCheckout(CustomerSubscription $subscription): string
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $plan = $subscription->plan;

        $session = Session::create([
            'mode' => 'subscription',
            'payment_method_types' => ['card'],
            'customer_email' => $subscription->customer_email,
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => $plan->currency,
                        'product_data' => [
                            'name' => $plan->name,
                        ],
                        'unit_amount' => (int) ($plan->price * 100),
                        'recurring' => [
                            'interval' => 'month',
                        ],
                    ],
                    'quantity' => 1,
                ],
            ],
            'metadata' => [
                'subscription_id' => $subscription->id,
            ],
            'success_url' => config('app.url') . '/success',
            'cancel_url' => config('app.url') . '/cancel',
        ]);

        $subscription->update([
            'stripe_checkout_session_id' => $session->id,
        ]);

        return $session->url;
    }
}