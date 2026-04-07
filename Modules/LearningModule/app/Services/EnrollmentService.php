<?php

namespace Modules\LearningModule\Services;

use App\Traits\CachesQueries;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\LearningModule\Enums\EnrollmentStatus;
use Modules\LearningModule\Models\Course;
use Modules\LearningModule\Models\Enrollment;
use Modules\LearningModule\Models\Lesson;
use Modules\LearningModule\Models\Unit;
use Modules\UserMangementModule\Models\User;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Service class for managing enrollment business logic.
 * Handles enrollment creation, status management, progress tracking, and completion.
 */
class EnrollmentService
{
    use CachesQueries;

    /**
     * Course service instance.
     */
    protected CourseService $courseService;

    /**
     * Create a new enrollment service instance.
     */
    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    /**
     * Ensure the learner has a completed student profile before enrollment.
     */
    private function ensureStudentProfileCompleted(int $learnerId): void
    {
        $learner = User::findOrFail($learnerId);

        if (! $learner->studentProfile()->exists()) {
            throw new HttpException(422, 'Student information is incomplete. Please complete your profile first.');
        }
    }

    /**
     * Enroll a learner in a course.
     *
     * @param Course $course
     * @param int $learnerId
     * @param string $enrollmentType
     * @param int|null $enrolledBy
     * @return Enrollment|null
     */
    public function enroll(Course $course, int $learnerId, string $enrollmentType = 'self', ?int $enrolledBy = null): ?Enrollment
    {
        if (! $this->courseService->isAvailableForEnrollment($course)) {
            Log::warning('Attempted to enroll in course that is not available', [
                'course_id' => $course->course_id,
                'learner_id' => $learnerId,
            ]);

            throw new HttpException(422, 'Course is not available for enrollment. It must be published and have an active course category.');
        }

        $existingEnrollment = Enrollment::where('learner_id', $learnerId)
            ->where('course_id', $course->course_id)
            ->first();

        if ($existingEnrollment) {
            $statusValue = $existingEnrollment->enrollment_status instanceof EnrollmentStatus
                ? $existingEnrollment->enrollment_status->value
                : $existingEnrollment->enrollment_status;

            if (in_array($statusValue, [EnrollmentStatus::DROPPED->value, EnrollmentStatus::SUSPENDED->value], true)) {
                return $this->updateStatus($existingEnrollment, EnrollmentStatus::ACTIVE);
            }

            Log::warning('Attempted to enroll learner who is already enrolled', [
                'course_id' => $course->course_id,
                'learner_id' => $learnerId,
            ]);

            throw new HttpException(422, 'This learner is already enrolled in this course.');
        }

        try {
            return DB::transaction(function () use ($course, $learnerId, $enrollmentType, $enrolledBy) {
                $enrolledByValue = $enrollmentType === 'self'
                    ? $learnerId
                    : ($enrolledBy ?? Auth::id());

                $this->ensureStudentProfileCompleted($learnerId);

                $enrollment = Enrollment::create([
                    'learner_id' => $learnerId,
                    'course_id' => $course->course_id,
                    'enrollment_type' => $enrollmentType,
                    'enrollment_status' => EnrollmentStatus::ACTIVE->value,
                    'enrolled_by' => $enrolledByValue,
                    'enrolled_at' => now(),
                    'progress_percentage' => 0.00,
                ]);

                $this->clearEnrollmentCache($learnerId, $course->course_id);

                Log::info('Enrollment created', [
                    'enrollment_id' => $enrollment->enrollment_id,
                    'learner_id' => $learnerId,
                    'course_id' => $course->course_id,
                    'enrollment_type' => $enrollmentType,
                    'enrolled_by' => $enrolledByValue,
                ]);

                return $enrollment;
            });
        } catch (HttpException $e) {
            throw $e;
        } catch (ModelNotFoundException $e) {
            Log::error('Course not found for enrollment', [
                'course_id' => $course->course_id,
                'learner_id' => $learnerId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new HttpException(422, 'Learner or related resource not found.');
        } catch (\Exception $e) {
            Log::error('Failed to enroll learner', [
                'course_id' => $course->course_id,
                'learner_id' => $learnerId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if (config('app.debug')) {
                throw new HttpException(422, 'Enrollment failed: ' . $e->getMessage());
            }

            return null;
        }
    }

    /**
     * Calculate enrollment progress percentage.
     * Only calculates and returns the progress, does not update the enrollment.
     *
     * @param Enrollment $enrollment
     * @return float|null
     */
    public function calculateProgress(Enrollment $enrollment): ?float
    {
        $course = $enrollment->course;

        if (!$course) {
            Log::warning("Course not found for enrollment when calculating progress", [
                'enrollment_id' => $enrollment->enrollment_id,
            ]);
            return null;
        }

        try {
            // Get total lessons count
            $totalLessons = $this->getTotalLessonsCount($course);
            $completedLessons = 0;

            if ($totalLessons === 0) {
                // If no lessons, progress is 0
                $progress = 0.00;
            } else {
                // Get completed lessons count
                $completedLessons = $this->getCompletedLessonsCount($enrollment);

                $progress = round(($completedLessons / $totalLessons) * 100, 2);
            }

            Log::info("Enrollment progress calculated", [
                'enrollment_id' => $enrollment->enrollment_id,
                'progress_percentage' => $progress,
                'completed_lessons' => $completedLessons,
                'total_lessons' => $totalLessons,
            ]);

            return $progress;
        } catch (\Exception $e) {
            Log::error("Failed to calculate enrollment progress", [
                'enrollment_id' => $enrollment->enrollment_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Update enrollment progress and handle course completion.
     *
     * @param Enrollment $enrollment
     * @return Enrollment|null
     */
    public function updateProgress(Enrollment $enrollment): ?Enrollment
    {
        try {
            $progress = $this->calculateProgress($enrollment);

            if ($progress === null) {
                return null;
            }

            // Update enrollment progress
            $enrollment->update(['progress_percentage' => $progress]);

            // Clear enrollment cache after progress update
            $this->clearEnrollmentCache($enrollment->learner_id, $enrollment->course_id);

            // Check if all lessons are completed and handle completion
            $this->handleCourseCompletion($enrollment);

            return $enrollment->fresh();
        } catch (\Exception $e) {
            Log::error("Failed to update enrollment progress", [
                'enrollment_id' => $enrollment->enrollment_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Get total lessons count for a course.
     *
     * @param Course $course
     * @return int
     */
    protected function getTotalLessonsCount(Course $course): int
    {
        return Lesson::whereHas('unit', function ($query) use ($course) {
            $query->where('course_id', $course->course_id);
        })->count();
    }

    /**
     * Get completed lessons count for an enrollment.
     * Counts lessons that have been completed by the learner in this enrollment.
     * Excludes soft-deleted lessons and units to match getTotalLessonsCount().
     *
     * @param Enrollment $enrollment
     * @return int
     */
    protected function getCompletedLessonsCount(Enrollment $enrollment): int
    {
        return Lesson::whereHas('unit', function ($query) use ($enrollment) {
            $query->where('course_id', $enrollment->course_id);
        })->whereHas('completedByEnrollments', function ($query) use ($enrollment) {
            $query->where('enrollment_id', $enrollment->enrollment_id);
        })->count();
    }

    /**
     * Check if learner is enrolled in course.
     *
     * @param Course|int $course
     * @param int $learnerId
     * @return bool
     */
    public function isEnrolled($course, int $learnerId): bool
    {
        // to handle the case where the course is an integer or a course model
        $courseId = is_int($course) ? $course : $course->course_id;
        $cacheKey = "enrollment.check.{$learnerId}.{$courseId}";

        return $this->remember($cacheKey, 300, function () use ($learnerId, $courseId) {
            return Enrollment::where('learner_id', $learnerId)
                ->where('course_id', $courseId)
                ->where('enrollment_status', EnrollmentStatus::ACTIVE->value)
                ->exists();
        }, ['enrollments', "learner.{$learnerId}", "course.{$courseId}"]);
    }

    /**
     * Get enrollment for learner and course.
     *
     * @param Course|int $course
     * @param int $learnerId
     * @return Enrollment|null
     */
    public function getEnrollment($course, int $learnerId): ?Enrollment
    {
        // to handle the case where the course is an integer or a course model
        $courseId = is_int($course) ? $course : $course->course_id;
        $cacheKey = "enrollment.{$learnerId}.{$courseId}";

        return $this->remember($cacheKey, 300, function () use ($learnerId, $courseId) {
            return Enrollment::where('learner_id', $learnerId)
                ->where('course_id', $courseId)
                ->first();
        }, ['enrollments', "learner.{$learnerId}", "course.{$courseId}"]);
    }

    /**
     * Get active enrollments for a course.
     *
     * @param Course $course
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveEnrollmentsForCourse(Course $course)
    {
        return Enrollment::where('course_id', $course->course_id)
            ->where('enrollment_status', EnrollmentStatus::ACTIVE->value)
            ->with(['learner', 'enrolledBy'])
            ->orderBy('enrolled_at', 'desc')
            ->get();
    }

    /**
     * Get active enrollments for a learner.
     *
     * @param int $learnerId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveEnrollmentsForLearner(int $learnerId)
    {
        $cacheKey = "enrollments.learner.{$learnerId}.active";

        return $this->remember($cacheKey, 600, function () use ($learnerId) {
            return Enrollment::where('learner_id', $learnerId)
                ->where('enrollment_status', EnrollmentStatus::ACTIVE->value)
                ->with(['course', 'enrolledBy'])
                ->orderBy('enrolled_at', 'desc')
                ->get();
        }, ['enrollments', "learner.{$learnerId}"]);
    }

    /**
     * Clear enrollment related cache.
     * Uses Redis tags for efficient bulk invalidation.
     *
     * @param int $learnerId
     * @param int $courseId
     * @return void
     */
    protected function clearEnrollmentCache(int $learnerId, int $courseId): void
    {
        // Use Redis tags for efficient bulk invalidation
        $this->flushTags(["learner.{$learnerId}", "course.{$courseId}"]);
    }

    /**
     * Get detailed progress information for an enrollment.
     *
     * @param Enrollment $enrollment
     * @return array
     * @throws \Exception
     */
    public function getProgressDetails(Enrollment $enrollment): ?array
    {
        $course = $enrollment->course;

        if (!$course) {
            Log::warning("Course not found for enrollment when getting progress details", [
                'enrollment_id' => $enrollment->enrollment_id,
            ]);
            return null;
        }

        try {
            $totalUnits = Unit::where('course_id', $course->course_id)->count();
            $totalLessons = $this->getTotalLessonsCount($course);
            $completedLessons = $this->getCompletedLessonsCount($enrollment);

            return [
                'enrollment_id' => $enrollment->enrollment_id,
                'progress_percentage' => (float)$enrollment->progress_percentage,
                'total_units' => $totalUnits,
                'total_lessons' => $totalLessons,
                'completed_lessons' => $completedLessons,
                'remaining_lessons' => max(0, $totalLessons - $completedLessons),
                'is_completed' => $enrollment->enrollment_status === EnrollmentStatus::COMPLETED,
            ];
        } catch (\Exception $e) {
            Log::error("Failed to get progress details", [
                'enrollment_id' => $enrollment->enrollment_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Check if all lessons in a course are completed and update enrollment accordingly.
     *
     * @param Enrollment $enrollment
     * @return void
     */
    protected function handleCourseCompletion(Enrollment $enrollment): void
    {
        $course = $enrollment->course;

        if (!$course) {
            Log::warning("Course not found for enrollment when checking completion", [
                'enrollment_id' => $enrollment->enrollment_id,
            ]);
            return;
        }

        try {
            $totalLessons = $this->getTotalLessonsCount($course);
            $completedLessons = $this->getCompletedLessonsCount($enrollment);

            // If all lessons are completed, update progress to 100% and set completed_at
            if ($totalLessons > 0 && $completedLessons === $totalLessons) {
                $updateData = ['progress_percentage' => 100.00];

                // Set completed_at if not already set
                // Note: We don't check exams/assignments here as per user's request
                if (!$enrollment->completed_at) {
                    $updateData['completed_at'] = now();
                }

                // Update enrollment status to completed if not already
                if ($enrollment->enrollment_status !== EnrollmentStatus::COMPLETED->value) {
                    $updateData['enrollment_status'] = EnrollmentStatus::COMPLETED->value;
                }

                $enrollment->update($updateData);

                Log::info("Course completion handled for enrollment", [
                    'enrollment_id' => $enrollment->enrollment_id,
                    'course_id' => $course->course_id,
                    'total_lessons' => $totalLessons,
                    'completed_lessons' => $completedLessons,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to check course completion", [
                'enrollment_id' => $enrollment->enrollment_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
