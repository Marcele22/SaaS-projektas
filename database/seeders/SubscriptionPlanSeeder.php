<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'description' => '2 sessions per week',
                'price' => 59.00,
                'currency' => 'EUR',
                'duration_days' => 30,
                'weekly_sessions' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Growth',
                'description' => '3 sessions per week',
                'price' => 79.00,
                'currency' => 'EUR',
                'duration_days' => 30,
                'weekly_sessions' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Pro',
                'description' => '5 sessions per week',
                'price' => 119.00,
                'currency' => 'EUR',
                'duration_days' => 30,
                'weekly_sessions' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['name' => $plan['name']],
                $plan
            );
        }
    }
}