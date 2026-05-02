<?php

namespace Modules\ReportingModule\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Modules\ReportingModule\Services\AdminDashboardService;
use Modules\ReportingModule\Services\CourseAnalyticsService;
use Modules\ReportingModule\Services\StudentAnalyticsService;
use Modules\ReportingModule\Http\Resources\AdminDashboardResource;
use Modules\ReportingModule\Http\Resources\CoursePopularityReportResource;
use Modules\ReportingModule\Http\Resources\StudentPerformanceReportResource;
use Modules\ReportingModule\Http\Requests\Report\GenerateCourseReportRequest;
use Modules\ReportingModule\Http\Requests\Report\GenerateStudentReportRequest;
use Modules\ReportingModule\Http\Requests\Report\GetCompletionRatesRequest;
use Modules\ReportingModule\Http\Requests\Report\GetLearningTimeAnalysisRequest;

/**
 * Controller for the main admin dashboard.
 * After deleting organizations and programs, this controller is now responsible
 * for platform-wide reporting only.
 */
class AdminDashboardController extends Controller
{
    protected AdminDashboardService $dashboardService;
    protected CourseAnalyticsService $courseAnalyticsService;
    protected StudentAnalyticsService $studentAnalyticsService;

    public function __construct(
        AdminDashboardService $dashboardService,
        CourseAnalyticsService $courseAnalyticsService,
        StudentAnalyticsService $studentAnalyticsService
    ) {
        $this->dashboardService = $dashboardService;
        $this->courseAnalyticsService = $courseAnalyticsService;
        $this->studentAnalyticsService = $studentAnalyticsService;
    }

    public function dashboard(): JsonResponse
    {
        try {
            $dashboard = $this->dashboardService->getAdminDashboard();

            return $this->success(
                new AdminDashboardResource($dashboard),
                'Admin dashboard retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->error('Failed to retrieve admin dashboard.', 500, $e->getMessage());
        }
    }

    /**
     * Route alias: GET /api/v1/super-admin/dashboard uses {@see dashboard()}.
     */
    public function index(): JsonResponse
    {
        return $this->dashboard();
    }

    public function generateCoursePopularityReport(GenerateCourseReportRequest $request): JsonResponse
    {
        try {
            $report = $this->courseAnalyticsService->generatePopularityReport($request->validated());
            return $this->success(
                new CoursePopularityReportResource($report),
                'Course popularity report generated successfully.'
            );
        } catch (Exception $e) {
            return $this->error('Failed to generate course popularity report.', 500, $e->getMessage());
        }
    }

    public function identifyLearningGaps(): JsonResponse
    {
        try {
            $learningGapsReport = $this->courseAnalyticsService->identifyLearningGaps();
            return $this->success(
                $learningGapsReport,
                'Learning gaps identified successfully.'
            );
        } catch (Exception $e) {
            return $this->error('Failed to identify learning gaps.', 500, $e->getMessage());
        }
    }

    public function getContentPerformance(int $courseId): JsonResponse
    {
        try {
            $contentPerformance = $this->courseAnalyticsService->getContentPerformance($courseId);
            return $this->success(
                $contentPerformance,
                'Content performance retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->error('Failed to get content performance.', 500, $e->getMessage());
        }
    }

    public function generateStudentPerformanceReport(GenerateStudentReportRequest $request): JsonResponse
    {
        try {
            $performanceReport = $this->studentAnalyticsService->generatePerformanceReport($request->validated());
            return $this->success(
                new StudentPerformanceReportResource($performanceReport),
                'Student performance report generated successfully.'
            );
        } catch (Exception $e) {
            return $this->error('Failed to generate student performance report.', 500, $e->getMessage());
        }
    }

    public function getCompletionRates(GetCompletionRatesRequest $request): JsonResponse
    {
        try {
            $completionRates = $this->studentAnalyticsService->getCompletionRates($request->validated());
            return $this->success(
                $completionRates,
                'Completion rates retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->error('Failed to retrieve completion rates.', 500, $e->getMessage());
        }
    }

    public function getLearningTimeAnalysis(GetLearningTimeAnalysisRequest $request): JsonResponse
    {
        try {
            $learningTimeAnalysis = $this->studentAnalyticsService->getLearningTimeAnalysis($request->validated());
            return $this->success(
                $learningTimeAnalysis,
                'Learning time analysis retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->error('Failed to retrieve learning time analysis.', 500, $e->getMessage());
        }
    }

    /**
     * Backward-compatible method name.
     */
    public function generatePerformanceReport(GenerateStudentReportRequest $request): JsonResponse
    {
        return $this->generateStudentPerformanceReport($request);
    }
}
