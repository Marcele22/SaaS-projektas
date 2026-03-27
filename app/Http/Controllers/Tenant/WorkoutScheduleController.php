<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schedules\GenerateWorkoutScheduleRequest;
use App\Models\CustomerSubscription;
use App\Services\Schedules\WorkoutScheduleGenerator;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class WorkoutScheduleController extends Controller
{
    public function __construct(
        protected WorkoutScheduleGenerator $workoutScheduleGenerator
    ) {
    }

    public function generate(
        GenerateWorkoutScheduleRequest $request,
        CustomerSubscription $subscription
    ): JsonResponse {
        if (! $this->workoutScheduleGenerator->canGenerate($subscription)) {
            return response()->json([
                'error' => 'Subscription is not active or expired',
            ], 422);
        }

        $validated = $request->validated();

        if (isset($validated['year'], $validated['month'])) {
            $createdCount = $this->workoutScheduleGenerator->generateForMonth(
                $subscription,
                (int) $validated['year'],
                (int) $validated['month']
            );

            return response()->json([
                'message' => 'Workout schedule generated.',
                'created_count' => $createdCount,
            ]);
        }

        $fromDate = isset($validated['from_date'])
            ? Carbon::parse($validated['from_date'])
            : null;

        $toDate = isset($validated['to_date'])
            ? Carbon::parse($validated['to_date'])
            : null;

        $createdCount = $this->workoutScheduleGenerator->generateForSubscription(
            $subscription,
            $fromDate,
            $toDate
        );

        return response()->json([
            'message' => 'Workout schedule generated.',
            'created_count' => $createdCount,
        ]);
    }

    public function preview(
        GenerateWorkoutScheduleRequest $request,
        CustomerSubscription $subscription
    ): JsonResponse {
        $validated = $request->validated();

        if (isset($validated['year'], $validated['month'])) {
            $fromDate = Carbon::create(
                (int) $validated['year'],
                (int) $validated['month'],
                1
            )->startOfMonth();

            $toDate = $fromDate->copy()->endOfMonth();
        } else {
            $fromDate = isset($validated['from_date'])
                ? Carbon::parse($validated['from_date'])
                : null;

            $toDate = isset($validated['to_date'])
                ? Carbon::parse($validated['to_date'])
                : null;
        }

        $slots = $this->workoutScheduleGenerator->previewSlots(
            $subscription,
            $fromDate,
            $toDate
        );

        return response()->json($slots);
    }
}