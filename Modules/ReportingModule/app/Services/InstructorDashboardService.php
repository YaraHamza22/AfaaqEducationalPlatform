<?php

namespace Modules\ReportingModule\Services;

use Illuminate\Support\Facades\Cache;
use Modules\LearningModule\Enums\EnrollmentStatus;
use Modules\LearningModule\Models\Course;

/**
 * Service for Instructor Dashboard.
 */
class InstructorDashboardService
{
    public function getInstructorDashboard(int $instructorId): array
    {
        $cacheKey = "instructor_dashboard_{$instructorId}";

        return Cache::remember($cacheKey, 300, function () use ($instructorId) {
            $courses = Course::whereHas('instructors', function ($q) use ($instructorId) {
                $q->where('instructor_id', $instructorId);
            })->with(['enrollments'])->get();

            $totalStudents = 0;
            $nonUniqueStudents = collect();
            $courseStats = [];

            foreach ($courses as $course) {
                $enrollments = $course->enrollments;
                $nonUniqueStudents = $nonUniqueStudents->merge($enrollments->pluck('student_id'));
                $totalStudents = $nonUniqueStudents->unique()->count();

                $courseStats[] = [
                    'course_id' => $course->course_id,
                    'title' => $course->title,
                    'total_students' => $enrollments->count(),
                    'active_students' => $enrollments->where('enrollment_status', EnrollmentStatus::ACTIVE)->count(),
                    'completed_students' => $enrollments->where('enrollment_status', EnrollmentStatus::COMPLETED)->count(),
                    'average_progress' => round($enrollments->avg('progress_percentage') ?? 0, 2),
                ];
            }

            return [
                'summary' => [
                    'total_courses' => $courses->count(),
                    'total_students' => $totalStudents,
                    'pending_assignments' => 0,
                ],
                'course_statistics' => $courseStats,
                'top_performing_courses' => $this->getTopPerformingCourses($courses),
            ];
        });
    }

    private function getTopPerformingCourses($courses): array
    {
        return $courses->map(function ($course) {
            $completedEnrollments = $course->enrollments->where('enrollment_status', EnrollmentStatus::COMPLETED);
            $avgProgress = $completedEnrollments->avg('progress_percentage') ?? 0;

            return [
                'course_id' => $course->course_id,
                'title' => $course->title,
                'average_progress' => round($avgProgress, 2),
                'completion_count' => $completedEnrollments->count(),
            ];
        })
            ->sortByDesc('average_progress')
            ->take(5)
            ->values()
            ->toArray();
    }
}
