<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class SubscriptionPlan extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'price',
        'weekly_sessions',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'weekly_sessions' => 'integer',
    ];

    public function subscriptions()
    {
        return $this->hasMany(CustomerSubscription::class);
    }
}
