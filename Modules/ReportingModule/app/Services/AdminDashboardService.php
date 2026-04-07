<?php

namespace Modules\ReportingModule\Services;

use Illuminate\Support\Facades\Cache;
use Modules\LearningModule\Enums\EnrollmentStatus;
use Modules\LearningModule\Models\Course;
use Modules\LearningModule\Models\Enrollment;
use Modules\UserMangementModule\Models\User;

/**
 * Service for dashboard data aggregation.
 *
 * After deleting organizations and programs, this service is now focused on
 * platform-wide reporting for admins.
 */
class AdminDashboardService
{
    protected CourseAnalyticsService $courseAnalyticsService;
    protected StudentAnalyticsService $studentAnalyticsService;

    public function __construct(
        CourseAnalyticsService $courseAnalyticsService,
        StudentAnalyticsService $studentAnalyticsService
    ) {
        $this->courseAnalyticsService = $courseAnalyticsService;
        $this->studentAnalyticsService = $studentAnalyticsService;
    }

    public function getAdminDashboard(): array
    {
        $cacheKey = 'admin_dashboard';

        return Cache::remember($cacheKey, 300, function () {
            $totalStudents = User::whereHas('studentProfile')->count();
            $activeStudents = Enrollment::where('enrollment_status', EnrollmentStatus::ACTIVE)
                ->distinct('student_id')
                ->count('student_id');

            $totalCourses = Course::count();
            $publishedCourses = Course::whereNotNull('published_at')->count();

            $totalEnrollments = Enrollment::count();
            $completedEnrollments = Enrollment::where('enrollment_status', EnrollmentStatus::COMPLETED)->count();
            $completionRate = $totalEnrollments > 0
                ? round(($completedEnrollments / $totalEnrollments) * 100, 2)
                : 0;

            $popularCourses = Course::withCount('enrollments')
                ->orderBy('enrollments_count', 'desc')
                ->take(10)
                ->get()
                ->map(function ($course) {
                    return [
                        'course_id' => $course->course_id,
                        'title' => $course->title,
                        'enrollments_count' => $course->enrollments_count,
                    ];
                })->values()->toArray();

            return [
                'summary' => [
                    'total_students' => $totalStudents,
                    'active_students' => $activeStudents,
                    'total_courses' => $totalCourses,
                    'published_courses' => $publishedCourses,
                    'total_enrollments' => $totalEnrollments,
                    'completion_rate' => $completionRate,
                ],
                'popular_courses' => $popularCourses,
                'learning_gaps' => $this->courseAnalyticsService->identifyLearningGaps(),
                'course_analytics' => [
                    'popularity_report' => $this->courseAnalyticsService->generatePopularityReport([]),
                ],
                'student_analytics' => [
                    'performance_report' => $this->studentAnalyticsService->generatePerformanceReport([]),
                    'completion_rates' => $this->studentAnalyticsService->getCompletionRates([]),
                    'learning_time_analysis' => $this->studentAnalyticsService->getLearningTimeAnalysis([]),
                ],
            ];
        });
    }
}
