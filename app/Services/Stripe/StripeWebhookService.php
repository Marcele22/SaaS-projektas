<?php

namespace App\Services\Stripe;

use App\Models\CustomerSubscription;
use App\Services\Schedules\WorkoutScheduleGenerator;
use App\Services\Subscriptions\SubscriptionLifecycleService;
use Illuminate\Support\Facades\DB;

class StripeWebhookService
{
    public function __construct(
        protected SubscriptionLifecycleService $subscriptionLifecycleService,
        protected WorkoutScheduleGenerator $workoutScheduleGenerator
    ) {
    }

    public function handleCheckoutCompleted(array $payload): void
    {
        $session = $payload['data']['object'] ?? null;

        if (! $session) {
            return;
        }

        $subscriptionId = $session['metadata']['subscription_id'] ?? null;

        if (! $subscriptionId) {
            return;
        }

        $subscription = CustomerSubscription::with('plan', 'scheduleTemplates')->find($subscriptionId);

        if (! $subscription) {
            return;
        }

        DB::transaction(function () use ($subscription, $session) {
            $subscription->update([
                'stripe_subscription_id' => $session['subscription'] ?? null,
            ]);

            $subscription = $this->subscriptionLifecycleService->activate($subscription);

            $this->workoutScheduleGenerator->generateForMonth(
                $subscription->load('plan', 'scheduleTemplates'),
                now()->year,
                now()->month
            );
        });
    }

    public function handlePaymentFailed(array $payload): void
    {
        $object = $payload['data']['object'] ?? null;

        if (! $object) {
            return;
        }

        $stripeSubscriptionId = $object['subscription'] ?? null;

        if (! $stripeSubscriptionId) {
            return;
        }

        $subscription = CustomerSubscription::where('stripe_subscription_id', $stripeSubscriptionId)->first();

        if (! $subscription) {
            return;
        }

        $this->subscriptionLifecycleService->markPaymentFailed($subscription);
    }
}