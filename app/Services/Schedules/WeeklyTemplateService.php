<?php

namespace App\Services\Schedules;

use App\Models\CustomerSubscription;
use App\Models\WeeklyScheduleTemplate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WeeklyTemplateService
{
    public function replaceTemplates(CustomerSubscription $subscription, array $templates): Collection
    {
        return DB::transaction(function () use ($subscription, $templates) {
            $subscription->scheduleTemplates()->delete();

            $created = collect();

            foreach ($templates as $template) {
                $created->push(
                    WeeklyScheduleTemplate::create([
                        'customer_subscription_id' => $subscription->id,
                        'day_of_week' => $template['day_of_week'],
                        'start_time' => $template['start_time'],
                        'end_time' => $template['end_time'],
                        'activity' => $template['activity'] ?? null,
                        'trainer_name' => $template['trainer_name'] ?? null,
                        'location' => $template['location'] ?? null,
                        'is_active' => $template['is_active'] ?? true,
                    ])
                );
            }

            return $created;
        });
    }
}