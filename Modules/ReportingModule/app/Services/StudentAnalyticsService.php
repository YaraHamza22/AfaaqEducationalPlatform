<?php

namespace Modules\ReportingModule\Services;

use Modules\LearningModule\Enums\EnrollmentStatus;
use Modules\LearningModule\Models\Enrollment;

/**
 * Service for student analytics and reporting.
 * Renamed from LearnerAnalyticsService to match the current domain language.
 */
class StudentAnalyticsService
{
    public function generatePerformanceReport(array $filters): array
    {
        $query = Enrollment::query();

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (isset($filters['course_id'])) {
            $query->where('course_id', $filters['course_id']);
        }

        if (isset($filters['date_from'])) {
            $query->where('enrolled_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('enrolled_at', '<=', $filters['date_to']);
        }

        $enrollments = $query->with(['student', 'course'])->get();

        return [
            'total_enrollments' => $enrollments->count(),
            'completed_enrollments' => $enrollments->where('enrollment_status', EnrollmentStatus::COMPLETED)->count(),
            'average_progress' => round($enrollments->avg('progress_percentage') ?? 0, 2),
            'average_completion_time_days' => $this->calculateAverageCompletionTime($enrollments),
            'performance_by_course' => $this->getPerformanceByCourse($enrollments),
        ];
    }

    public function getCompletionRates(array $filters): array
    {
        $query = Enrollment::query();

        if (isset($filters['date_from'])) {
            $query->where('enrolled_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('enrolled_at', '<=', $filters['date_to']);
        }

        $total = $query->count();
        $completed = (clone $query)->where('enrollment_status', EnrollmentStatus::COMPLETED)->count();

        return [
            'total_enrollments' => $total,
            'completed_enrollments' => $completed,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
        ];
    }

    public function getLearningTimeAnalysis(array $filters): array
    {
        $query = Enrollment::query();

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (isset($filters['course_id'])) {
            $query->where('course_id', $filters['course_id']);
        }

        if (isset($filters['enrollment_id'])) {
            $query->where('enrollment_id', $filters['enrollment_id']);
        }

        if (isset($filters['date_from'])) {
            $query->where('enrolled_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('enrolled_at', '<=', $filters['date_to']);
        }

        $enrollments = $query->get();

        $completedEnrollments = $enrollments->where('enrollment_status', EnrollmentStatus::COMPLETED)
            ->whereNotNull('completed_at');

        $totalDays = $completedEnrollments->sum(function ($enrollment) {
            return $enrollment->enrolled_at->diffInDays($enrollment->completed_at);
        });

        $averageDays = $completedEnrollments->count() > 0
            ? round($totalDays / $completedEnrollments->count(), 2)
            : 0;

        return [
            'total_enrollments' => $enrollments->count(),
            'completed_enrollments' => $completedEnrollments->count(),
            'average_completion_days' => $averageDays,
            'total_learning_days' => $totalDays,
        ];
    }

    public function calculateAverageCompletionTime($enrollments): float
    {
        $completed = $enrollments->where('enrollment_status', EnrollmentStatus::COMPLETED)
            ->whereNotNull('completed_at');

        if ($completed->isEmpty()) {
            return 0;
        }

        $totalDays = $completed->sum(function ($enrollment) {
            return $enrollment->enrolled_at->diffInDays($enrollment->completed_at);
        });

        return round($totalDays / $completed->count(), 2);
    }

    public function getPerformanceByCourse($enrollments): array
    {
        return $enrollments->groupBy('course_id')->map(function ($courseEnrollments) {
            $total = $courseEnrollments->count();
            $completed = $courseEnrollments->where('enrollment_status', EnrollmentStatus::COMPLETED)->count();

            return [
                'course_id' => $courseEnrollments->first()->course_id,
                'course_title' => $courseEnrollments->first()->course->title ?? 'Unknown',
                'total_enrollments' => $total,
                'average_progress' => round($courseEnrollments->avg('progress_percentage') ?? 0, 2),
                'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            ];
        })->values()->toArray();
    }

    public function getStudentProgressByCourse(int $studentId): array
    {
        return Enrollment::where('student_id', $studentId)
            ->with('course')
            ->get()
            ->map(function ($enrollment) {
                return [
                    'course_id' => $enrollment->course_id,
                    'course_title' => $enrollment->course->title ?? 'Unknown',
                    'progress' => (float) $enrollment->progress_percentage,
                    'status' => $enrollment->enrollment_status->value,
                ];
            })
            ->toArray();
    }

    public function getLearnerProgressByCourse(int $learnerId): array
    {
        return $this->getStudentProgressByCourse($learnerId);
    }

    public function getSkillsAcquired($completedEnrollments): array
    {
        $coursesByCategory = $completedEnrollments->groupBy(function ($enrollment) {
            return $enrollment->course->courseCategory->name ?? 'Unknown';
        });

        return $coursesByCategory->map(function ($enrollments, $categoryName) {
            return [
                'skill_category' => $categoryName,
                'courses_completed' => $enrollments->pluck('course_id')->unique()->count(),
                'students_count' => $enrollments->pluck('student_id')->unique()->count(),
            ];
        })->values()->toArray();
    }
}
