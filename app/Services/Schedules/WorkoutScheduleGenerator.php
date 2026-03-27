<?php

namespace App\Services\Schedules;

use App\Models\CustomerSubscription;
use App\Models\WorkoutSession;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WorkoutScheduleGenerator
{
    public function canGenerate(CustomerSubscription $subscription): bool
    {
        return $subscription->status === CustomerSubscription::STATUS_ACTIVE
            && $subscription->starts_at
            && $subscription->ends_at
            && now()->between($subscription->starts_at, $subscription->ends_at);
    }

    public function generateForSubscription(
        CustomerSubscription $subscription,
        ?Carbon $fromDate = null,
        ?Carbon $toDate = null
    ): int {
        if (! $this->canGenerate($subscription)) {
            throw new InvalidArgumentException('Subscription is not active or expired.');
        }

        $plan = $subscription->plan;
        $templates = $subscription->scheduleTemplates()
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        if ($templates->isEmpty()) {
            return 0;
        }

        $periodStart = $fromDate
            ? $fromDate->copy()->startOfDay()
            : $subscription->starts_at->copy()->startOfDay();

        $periodEnd = $toDate
            ? $toDate->copy()->endOfDay()
            : $subscription->ends_at->copy()->endOfDay();

        if ($periodStart->lt($subscription->starts_at)) {
            $periodStart = $subscription->starts_at->copy()->startOfDay();
        }

        if ($periodEnd->gt($subscription->ends_at)) {
            $periodEnd = $subscription->ends_at->copy()->endOfDay();
        }

        if ($periodStart->gt($periodEnd)) {
            return 0;
        }

        return DB::transaction(function () use ($subscription, $templates, $periodStart, $periodEnd, $plan) {
            $createdCount = 0;
            $period = CarbonPeriod::create($periodStart, $periodEnd);

            foreach ($period as $date) {
                $date = Carbon::parse($date);
                $dayOfWeek = (int) $date->dayOfWeekIso;

                $matchingTemplates = $templates->where('day_of_week', $dayOfWeek);

                if ($matchingTemplates->isEmpty()) {
                    continue;
                }

                $weekStart = $date->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
                $weekEnd = $date->copy()->endOfWeek(Carbon::SUNDAY)->toDateString();

                $scheduledThisWeek = WorkoutSession::query()
                    ->where('customer_subscription_id', $subscription->id)
                    ->whereBetween('session_date', [$weekStart, $weekEnd])
                    ->count();

                if ($scheduledThisWeek >= $plan->weekly_sessions) {
                    continue;
                }

                foreach ($matchingTemplates as $template) {
                    $scheduledThisWeek = WorkoutSession::query()
                        ->where('customer_subscription_id', $subscription->id)
                        ->whereBetween('session_date', [$weekStart, $weekEnd])
                        ->count();

                    if ($scheduledThisWeek >= $plan->weekly_sessions) {
                        break;
                    }

                    $exists = WorkoutSession::query()
                        ->where('customer_subscription_id', $subscription->id)
                        ->where('session_date', $date->toDateString())
                        ->where('start_time', $template->start_time)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    WorkoutSession::create([
                        'customer_subscription_id' => $subscription->id,
                        'session_date' => $date->toDateString(),
                        'start_time' => $template->start_time,
                        'end_time' => $template->end_time,
                        'activity' => $template->activity,
                        'trainer_name' => $template->trainer_name,
                        'location' => $template->location,
                        'status' => WorkoutSession::STATUS_SCHEDULED,
                    ]);

                    $createdCount++;
                }
            }

            return $createdCount;
        });
    }

    public function generateForMonth(CustomerSubscription $subscription, int $year, int $month): int
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return $this->generateForSubscription($subscription, $start, $end);
    }

    public function previewSlots(
        CustomerSubscription $subscription,
        ?Carbon $fromDate = null,
        ?Carbon $toDate = null
    ): Collection {
        $templates = $subscription->scheduleTemplates()
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        if ($templates->isEmpty() || ! $subscription->starts_at || ! $subscription->ends_at) {
            return collect();
        }

        $periodStart = $fromDate
            ? $fromDate->copy()->startOfDay()
            : $subscription->starts_at->copy()->startOfDay();

        $periodEnd = $toDate
            ? $toDate->copy()->endOfDay()
            : $subscription->ends_at->copy()->endOfDay();

        if ($periodStart->lt($subscription->starts_at)) {
            $periodStart = $subscription->starts_at->copy()->startOfDay();
        }

        if ($periodEnd->gt($subscription->ends_at)) {
            $periodEnd = $subscription->ends_at->copy()->endOfDay();
        }

        if ($periodStart->gt($periodEnd)) {
            return collect();
        }

        $slots = collect();
        $period = CarbonPeriod::create($periodStart, $periodEnd);

        foreach ($period as $date) {
            $date = Carbon::parse($date);
            $dayOfWeek = (int) $date->dayOfWeekIso;

            foreach ($templates->where('day_of_week', $dayOfWeek) as $template) {
                $slots->push([
                    'session_date' => $date->toDateString(),
                    'start_time' => $template->start_time,
                    'end_time' => $template->end_time,
                    'activity' => $template->activity,
                    'trainer_name' => $template->trainer_name,
                    'location' => $template->location,
                ]);
            }
        }

        return $slots->values();
    }
}