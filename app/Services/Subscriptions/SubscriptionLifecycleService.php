<?php

namespace App\Services\Subscriptions;

use App\Models\CustomerSubscription;
use App\Models\SubscriptionPlan;
use App\Models\WorkoutSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SubscriptionLifecycleService
{
    public function createPending(array $data): CustomerSubscription
    {
        $plan = SubscriptionPlan::findOrFail($data['subscription_plan_id']);

        return DB::transaction(function () use ($data, $plan) {
            return CustomerSubscription::create([
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'subscription_plan_id' => $plan->id,
                'status' => CustomerSubscription::STATUS_PENDING,
                'starts_at' => null,
                'ends_at' => null,
            ]);
        });
    }

    public function activate(CustomerSubscription $subscription, ?Carbon $startsAt = null): CustomerSubscription
    {
        $startsAt ??= now();

        $plan = $subscription->plan;

        return DB::transaction(function () use ($subscription, $startsAt, $plan) {
            $subscription->update([
                'status' => CustomerSubscription::STATUS_ACTIVE,
                'starts_at' => $startsAt,
                'ends_at' => (clone $startsAt)->addDays($plan->duration_days),
            ]);

            return $subscription->fresh();
        });
    }

    public function cancelWithCleanup(CustomerSubscription $subscription): CustomerSubscription
    {
        return DB::transaction(function () use ($subscription) {

            $subscription->update([
                'status' => CustomerSubscription::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);

            // Delete future sessions
            WorkoutSession::query()
                ->where('customer_subscription_id', $subscription->id)
                ->whereDate('session_date', '>=', now()->toDateString())
                ->delete();

            return $subscription->fresh();
        });
    }

    public function markExpired(CustomerSubscription $subscription): CustomerSubscription
    {
        return DB::transaction(function () use ($subscription) {
            $subscription->update([
                'status' => CustomerSubscription::STATUS_EXPIRED,
            ]);

            return $subscription->fresh();
        });
    }

    public function markPaymentFailed(CustomerSubscription $subscription): CustomerSubscription
    {
        return DB::transaction(function () use ($subscription) {
            $subscription->update([
                'status' => CustomerSubscription::STATUS_PAYMENT_FAILED,
            ]);

            return $subscription->fresh();
        });
    }

    public function canGenerate(CustomerSubscription $subscription): bool
    {
        return $subscription->status === CustomerSubscription::STATUS_ACTIVE
            && $subscription->starts_at
            && $subscription->ends_at
            && now()->between($subscription->starts_at, $subscription->ends_at);
    }
}