<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class CustomerSubscription extends Model
{
    use BelongsToTenant;

    protected $table = 'customer_subscription';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'subscription_plan_id',
        'start_date',
        'end_date',
        'status',
        'payment',
        'stripe_subscription_id',
        'next_billing_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'next_billing_date' => 'date',
        'payment' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function sessions()
    {
        return $this->hasMany(WorkoutSession::class);
    }
}
