<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Stancl\Tenancy\Database\Models\Tenant;

use App\Models\SubscriptionPlan;
use App\Models\CustomerSubscription;
use App\Models\WeeklyScheduleTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SubscriptionDomainTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function subscription_domain_can_be_created_end_to_end()
    {
        $tenant = Tenant::create([
    'id' => 'test-studio',
    'data' => ['name' => 'Test Studio'],
]);

tenancy()->initialize($tenant);

$user = User::create([
    'name' => 'Jonas',
    'email' => 'jonas@test.lt',
    'password' => bcrypt('secret'),
]);

$plan = SubscriptionPlan::create([
    'name' => '2x per savaitę',
    'price' => 50,
    'weekly_sessions' => 2,
]);

WeeklyScheduleTemplate::create([
    'user_id' => $user->id,
    'weekday' => 1,
    'start_time' => '09:00',
    'end_time' => '10:00',
]);

WeeklyScheduleTemplate::create([
    'user_id' => $user->id,
    'weekday' => 3,
    'start_time' => '17:00',
    'end_time' => '18:00',
]);

$subscription = CustomerSubscription::create([
    'user_id' => $user->id,
    'subscription_plan_id' => $plan->id,
    'start_date' => now()->startOfMonth(),
    'end_date' => now()->endOfMonth(),
    'status' => 'active',
    'payment' => 50,
]);

        $this->assertDatabaseHas('customer_subscription', [
            'id' => $subscription->id,
            'status' => 'active',
        ]);

        $this->assertEquals(2, $plan->weekly_sessions);
        $this->assertCount(2, $user->scheduleTemplates);
    }
}
