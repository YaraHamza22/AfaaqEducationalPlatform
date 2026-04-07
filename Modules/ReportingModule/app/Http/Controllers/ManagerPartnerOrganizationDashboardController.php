<?php

namespace Modules\ReportingModule\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

/**
 * Deprecated: manager/organization dashboard was removed after deleting managers and organizations.
 */
class ManagerPartnerOrganizationDashboardController extends Controller
{
    public function dashboard(int $organizationId): JsonResponse
    {
        return $this->error('Organization reporting was removed after deleting managers and organizations.', 410);
    }

    public function generateCoursePopularityReport(): JsonResponse
    {
        return $this->error('Organization reporting was removed after deleting managers and organizations.', 410);
    }

    public function identifyLearningGaps(): JsonResponse
    {
        return $this->error('Organization reporting was removed after deleting managers and organizations.', 410);
    }

    public function getContentPerformance(int $courseId): JsonResponse
    {
        return $this->error('Organization reporting was removed after deleting managers and organizations.', 410);
    }
}
