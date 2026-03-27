<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\WorkoutSession;
use Illuminate\Http\JsonResponse;

class WorkoutSessionController extends Controller
{
    public function index(): JsonResponse
    {
        $sessions = WorkoutSession::query()
            ->with('subscription.plan')
            ->orderBy('session_date')
            ->orderBy('start_time')
            ->get();

        return response()->json($sessions);
    }

    public function show(WorkoutSession $workoutSession): JsonResponse
    {
        return response()->json(
            $workoutSession->load('subscription.plan')
        );
    }
}