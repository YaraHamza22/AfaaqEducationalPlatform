<?php

namespace Modules\ReportingModule\Services;

use Modules\LearningModule\Enums\EnrollmentStatus;
use Modules\LearningModule\Models\Course;

/**
 * Service for course analytics and reporting.
 * Updated to use course categories after removing programs.
 */
class CourseAnalyticsService
{
    public function generatePopularityReport(array $filters): array
    {
        $query = Course::query();

        if (isset($filters['course_category_id'])) {
            $query->where('course_category_id', $filters['course_category_id']);
        }

        if (isset($filters['date_from'])) {
            $query->where('published_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('published_at', '<=', $filters['date_to']);
        }

        $courses = $query->with(['courseCategory'])
            ->withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->get();

        return [
            'total_courses' => $courses->count(),
            'total_enrollments' => $courses->sum('enrollments_count'),
            'popular_courses' => $courses->take(10)->map(function ($course) {
                return [
                    'course_id' => $course->course_id,
                    'title' => $course->title,
                    'enrollments_count' => $course->enrollments_count,
                    'average_rating' => (float) ($course->average_rating ?? 0),
                ];
            })->values()->toArray(),
            'popularity_by_course_category' => $this->getPopularityByCourseCategory($courses),
        ];
    }

    public function getContentPerformance(?int $courseId): array
    {
        if (!$courseId) {
            return ['error' => 'Course ID is required'];
        }

        $course = Course::with(['enrollments', 'units.lessons'])->find($courseId);

        if (!$course) {
            return ['error' => 'Course not found'];
        }

        $enrollments = $course->enrollments;
        $totalEnrollments = $enrollments->count();
        $completedEnrollments = $enrollments->where('enrollment_status', EnrollmentStatus::COMPLETED)->count();

        return [
            'course_id' => $course->course_id,
            'course_title' => $course->title,
            'total_enrollments' => $totalEnrollments,
            'completed_enrollments' => $completedEnrollments,
            'completion_rate' => $totalEnrollments > 0
                ? round(($completedEnrollments / $totalEnrollments) * 100, 2)
                : 0,
            'average_progress' => round($enrollments->avg('progress_percentage') ?? 0, 2),
            'total_units' => $course->units->count(),
            'total_lessons' => $course->units->sum(fn ($unit) => $unit->lessons->count()),
        ];
    }

    public function getPopularityByCourseCategory($courses): array
    {
        return $courses->groupBy('course_category_id')->map(function ($categoryCourses) {
            return [
                'course_category_id' => $categoryCourses->first()->course_category_id,
                'course_category_name' => $categoryCourses->first()->courseCategory->name ?? 'Unknown',
                'total_courses' => $categoryCourses->count(),
                'total_enrollments' => $categoryCourses->sum('enrollments_count'),
            ];
        })->values()->toArray();
    }

    public function getPopularityByCourseType($courses): array
    {
        return $this->getPopularityByCourseCategory($courses);
    }

    public function getTopPerformingCourses(int $instructorId): array
    {
        return Course::whereHas('instructors', function ($query) use ($instructorId) {
            $query->where('instructor_id', $instructorId);
        })
            ->with(['enrollments' => function ($query) {
                $query->where('enrollment_status', EnrollmentStatus::COMPLETED);
            }])
            ->get()
            ->map(function ($course) {
                $avgProgress = $course->enrollments->avg('progress_percentage') ?? 0;
                return [
                    'course_id' => $course->course_id,
                    'title' => $course->title,
                    'average_progress' => round($avgProgress, 2),
                    'completion_count' => $course->enrollments->count(),
                ];
            })
            ->sortByDesc('average_progress')
            ->take(5)
            ->values()
            ->toArray();
    }

    public function identifyLearningGaps(): array
    {
        $lowCompletionCourses = Course::withCount(['enrollments as completed_count' => function ($query) {
            $query->where('enrollment_status', EnrollmentStatus::COMPLETED);
        }])
            ->withCount('enrollments')
            ->havingRaw('enrollments_count > 0')
            ->get()
            ->filter(function ($course) {
                if ($course->enrollments_count == 0) {
                    return false;
                }
                $completionRate = ($course->completed_count / $course->enrollments_count) * 100;
                return $completionRate < 30;
            })
            ->take(5)
            ->map(function ($course) {
                $completionRate = $course->enrollments_count > 0
                    ? round(($course->completed_count / $course->enrollments_count) * 100, 2)
                    : 0;
                return [
                    'course_id' => $course->course_id,
                    'title' => $course->title,
                    'completion_rate' => $completionRate,
                    'total_enrollments' => $course->enrollments_count,
                ];
            })
            ->values()
            ->toArray();

        $lowProgressCourses = Course::with('enrollments')
            ->get()
            ->map(function ($course) {
                return [
                    'course_id' => $course->course_id,
                    'title' => $course->title,
                    'average_progress' => round($course->enrollments->avg('progress_percentage') ?? 0, 2),
                ];
            })
            ->filter(fn ($course) => $course['average_progress'] < 40)
            ->sortBy('average_progress')
            ->take(5)
            ->values()
            ->toArray();

        return [
            'low_completion_courses' => $lowCompletionCourses,
            'low_progress_courses' => $lowProgressCourses,
        ];
    }
}
