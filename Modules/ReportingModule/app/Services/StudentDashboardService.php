<?php

namespace Modules\ReportingModule\Services;

use Illuminate\Support\Facades\Cache;
use Modules\LearningModule\Enums\EnrollmentStatus;
use Modules\LearningModule\Models\Enrollment;

/**
 * Service for Student Dashboard.
 */
class StudentDashboardService
{
    protected StudentAnalyticsService $studentAnalyticsService;

    public function __construct(StudentAnalyticsService $studentAnalyticsService)
    {
        $this->studentAnalyticsService = $studentAnalyticsService;
    }

    public function getStudentDashboard(int $studentId): array
    {
        $cacheKey = "student_dashboard_{$studentId}";

        return Cache::remember($cacheKey, 300, function () use ($studentId) {
            $enrollments = Enrollment::where('student_id', $studentId)
                ->with(['course', 'course.courseCategory'])
                ->get();

            $activeEnrollments = $enrollments->where('enrollment_status', EnrollmentStatus::ACTIVE);
            $completedEnrollments = $enrollments->where('enrollment_status', EnrollmentStatus::COMPLETED);
            $avgProgress = $enrollments->avg('progress_percentage') ?? 0;

            $recentCourses = $enrollments
                ->sortByDesc('enrolled_at')
                ->take(5)
                ->map(function ($enrollment) {
                    return [
                        'course_id' => $enrollment->course_id,
                        'title' => $enrollment->course->title ?? 'Unknown',
                        'course_category' => $enrollment->course->courseCategory->name ?? null,
                        'progress' => (float) $enrollment->progress_percentage,
                        'status' => $enrollment->enrollment_status->value,
                        'enrolled_at' => $enrollment->enrolled_at?->toDateTimeString(),
                    ];
                })
                ->values()
                ->toArray();

            return [
                'summary' => [
                    'total_courses' => $enrollments->count(),
                    'active_courses' => $activeEnrollments->count(),
                    'completed_courses' => $completedEnrollments->count(),
                    'average_progress' => round($avgProgress, 2),
                ],
                'recent_courses' => $recentCourses,
                'progress_by_course' => $this->studentAnalyticsService->getStudentProgressByCourse($studentId),
            ];
        });
    }
}
