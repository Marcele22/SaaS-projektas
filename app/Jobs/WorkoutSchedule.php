<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\WorkoutSession;

class WorkoutSchedule implements ShouldQueue
{
    use Queueable;
    protected $data;
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    
    public function handle(): void
    {
        WorkoutSession::create($this->data);
    }
}
