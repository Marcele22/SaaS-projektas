<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subscriptions\ActivateSubscriptionRequest;
use App\Http\Requests\Subscriptions\StoreCustomerSubscriptionRequest;
use App\Models\CustomerSubscription;
use App\Services\Subscriptions\SubscriptionLifecycleService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class CustomerSubscriptionController extends Controller
{
    public function __construct(
        protected SubscriptionLifecycleService $subscriptionLifecycleService
    ) {
    }

    public function index(): JsonResponse
    {
        $subscriptions = CustomerSubscription::query()
            ->with(['plan', 'scheduleTemplates', 'workoutSessions'])
            ->latest()
            ->get();

        return response()->json($subscriptions);
    }

    public function store(StoreCustomerSubscriptionRequest $request): JsonResponse
    {
        $subscription = $this->subscriptionLifecycleService->createPending(
            $request->validated()
        );

        return response()->json(
            $subscription->load('plan'),
            201
        );
    }

    public function show(CustomerSubscription $subscription): JsonResponse
    {
        return response()->json(
            $subscription->load(['plan', 'scheduleTemplates', 'workoutSessions'])
        );
    }

    public function activate(
        ActivateSubscriptionRequest $request,
        CustomerSubscription $subscription
    ): JsonResponse {
        $startsAt = $request->filled('starts_at')
            ? Carbon::parse($request->string('starts_at'))
            : null;

        $subscription = $this->subscriptionLifecycleService->activate($subscription, $startsAt);

        return response()->json($subscription);
    }

    public function cancel(CustomerSubscription $subscription): JsonResponse
    {
        $subscription = $this->subscriptionLifecycleService
            ->cancelWithCleanup($subscription);

        return response()->json($subscription);
    }

    public function markPaymentFailed(CustomerSubscription $subscription): JsonResponse
    {
        $subscription = $this->subscriptionLifecycleService
            ->markPaymentFailed($subscription);

        return response()->json($subscription);
    }
}