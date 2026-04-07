<?php

namespace Modules\LearningModule\Observers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\LearningModule\Enums\EnrollmentStatus;
use Modules\LearningModule\Models\Enrollment;
use Modules\LearningModule\Models\Lesson;
use Modules\LearningModule\Services\EnrollmentService;

class LessonObserver
{
    protected EnrollmentService $enrollmentService;

    public function __construct(EnrollmentService $enrollmentService)
    {
        $this->enrollmentService = $enrollmentService;
    }

    /**
     * Handle the Lesson "updating" event.
     * When is_completed changes from false to true, handle lesson completion.
     *
     * @param Lesson $lesson
     * @return void
     */
    public function updating(Lesson $lesson): void
    {
        // Check if is_completed is being set to true
        if ($lesson->isDirty('is_completed') && $lesson->is_completed === true && $lesson->getOriginal('is_completed') !== true) {
            $this->handleLessonCompletion($lesson);
        }
    }

    /**
     * Handle lesson completion logic.
     * 1. Check if lesson is already marked as completed for this enrollment
     * 2. Attach lesson to enrollment with completed_at timestamp
     * 3. Update lesson's is_completed attribute to true (already set)
     * 4. Update progress
     *
     * @param Lesson $lesson
     * @return void
     */
    protected function handleLessonCompletion(Lesson $lesson): void
    {
        try {
            // Get the authenticated user (learner)
            $learnerId = Auth::id();

            if (!$learnerId) {
                Log::warning("No authenticated user when completing lesson", [
                    'lesson_id' => $lesson->lesson_id,
                ]);
                return;
            }

            // Get the course from the lesson via unit
            $unit = $lesson->unit;
            if (!$unit) {
                Log::warning("Unit not found for lesson when completing", [
                    'lesson_id' => $lesson->lesson_id,
                ]);
                return;
            }

            $course = $unit->course;
            if (!$course) {
                Log::warning("Course not found for lesson when completing", [
                    'lesson_id' => $lesson->lesson_id,
                    'unit_id' => $unit->unit_id,
                ]);
                return;
            }

            // Get enrollment for this learner and course
            $enrollment = $this->enrollmentService->getEnrollment($course, $learnerId);

            if (!$enrollment) {
                Log::warning("Enrollment not found when completing lesson", [
                    'lesson_id' => $lesson->lesson_id,
                    'learner_id' => $learnerId,
                    'course_id' => $course->course_id,
                ]);
                return;
            }

            // Check if enrollment is active - only allow lesson completion for active enrollments
            if ($enrollment->enrollment_status !== EnrollmentStatus::ACTIVE) {
                Log::warning("Attempted to complete lesson for non-active enrollment", [
                    'enrollment_id' => $enrollment->enrollment_id,
                    'lesson_id' => $lesson->lesson_id,
                    'learner_id' => $learnerId,
                    'course_id' => $course->course_id,
                    'enrollment_status' => $enrollment->enrollment_status->value,
                ]);
                return;
            }

            // 1. Check if lesson is already marked as completed for this enrollment
            $alreadyCompleted = $enrollment->completedLessons()
                ->where('lesson_id', $lesson->lesson_id)
                ->exists();

            if ($alreadyCompleted) {
                Log::info("Lesson already marked as completed for enrollment", [
                    'enrollment_id' => $enrollment->enrollment_id,
                    'lesson_id' => $lesson->lesson_id,
                ]);
                // Still update progress in case it wasn't updated before
                $this->enrollmentService->updateProgress($enrollment);
                return;
            }

            // 2. Attach lesson to enrollment with completed_at timestamp
            $enrollment->completedLessons()->attach($lesson->lesson_id, [
                'completed_at' => now(),
            ]);

            // 3. Update lesson's is_completed attribute to true (already set, but ensure it's saved)
            $lesson->is_completed = true;
            $lesson->save();

            Log::info("Lesson marked as completed for enrollment via observer", [
                'enrollment_id' => $enrollment->enrollment_id,
                'lesson_id' => $lesson->lesson_id,
            ]);

            // 4. Update progress
            $this->enrollmentService->updateProgress($enrollment);
        } catch (\Exception $e) {
            Log::error("Failed to handle lesson completion in observer", [
                'lesson_id' => $lesson->lesson_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
