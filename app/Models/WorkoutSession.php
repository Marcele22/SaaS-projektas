<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutSession extends Model
{
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_ATTENDED = 'attended';
    public const STATUS_MISSED = 'missed';

    protected $fillable = [
        'customer_subscription_id',
        'session_date',
        'start_time',
        'end_time',
        'activity',
        'trainer_name',
        'location',
        'status',
        'cancelled_at',
        'attended_at',
    ];

    protected $casts = [
        'session_date' => 'date',
        'cancelled_at' => 'datetime',
        'attended_at' => 'datetime',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(CustomerSubscription::class, 'customer_subscription_id');
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }
}