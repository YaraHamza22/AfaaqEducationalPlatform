<?php

namespace Modules\ReportingModule\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Modules\ReportingModule\Services\StudentDashboardService;
use Modules\ReportingModule\Http\Resources\StudentDashboardResource;

/**
 * Controller for Student Dashboard.
 */
class StudentDashboardController extends Controller
{
    protected StudentDashboardService $dashboardService;

    public function __construct(StudentDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function dashboard(int $studentId): JsonResponse
    {
        try {
            $dashboard = $this->dashboardService->getStudentDashboard($studentId);

            return $this->success(
                new StudentDashboardResource($dashboard),
                'Student dashboard retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->error('Failed to retrieve student dashboard.', 500, $e->getMessage());
        }
    }
}
