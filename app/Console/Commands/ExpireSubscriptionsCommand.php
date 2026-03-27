<?php

namespace App\Console\Commands;

use App\Models\CustomerSubscription;
use App\Services\Subscriptions\SubscriptionLifecycleService;
use Illuminate\Console\Command;

class ExpireSubscriptionsCommand extends Command
{
    protected $signature = 'subscriptions:expire';
    protected $description = 'Mark ended active subscriptions as expired';

    public function handle(SubscriptionLifecycleService $subscriptionLifecycleService): int
    {
        $subscriptions = CustomerSubscription::query()
            ->where('status', CustomerSubscription::STATUS_ACTIVE)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now())
            ->get();

        foreach ($subscriptions as $subscription) {
            $subscriptionLifecycleService->markExpired($subscription);
        }

        $this->info("Expired {$subscriptions->count()} subscriptions.");

        return self::SUCCESS;
    }
}