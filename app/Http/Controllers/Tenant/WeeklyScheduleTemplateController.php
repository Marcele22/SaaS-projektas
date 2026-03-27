<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schedules\ReplaceWeeklyTemplatesRequest;
use App\Models\CustomerSubscription;
use App\Services\Schedules\WeeklyTemplateService;
use Illuminate\Http\JsonResponse;

class WeeklyScheduleTemplateController extends Controller
{
    public function __construct(
        protected WeeklyTemplateService $weeklyTemplateService
    ) {
    }

    public function replace(
        ReplaceWeeklyTemplatesRequest $request,
        CustomerSubscription $subscription
    ): JsonResponse {
        $templates = $this->weeklyTemplateService->replaceTemplates(
            $subscription,
            $request->validated()['templates']
        );

        return response()->json($templates);
    }
}