<?php

namespace Modules\ReportingModule\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Modules\ReportingModule\Services\InstructorDashboardService;
use Modules\ReportingModule\Http\Resources\InstructorDashboardResource;

/**
 * Controller for Instructor Dashboard.
 */
class InstructorDashboardController extends Controller
{
    protected InstructorDashboardService $dashboardService;

    public function __construct(InstructorDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function dashboard(int $instructorId): JsonResponse
    {
        try {
            $dashboard = $this->dashboardService->getInstructorDashboard($instructorId);

            return $this->success(
                new InstructorDashboardResource($dashboard),
                'Instructor dashboard retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->error('Failed to retrieve instructor dashboard.', 500, $e->getMessage());
        }
    }
}
