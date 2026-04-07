<?php
namespace Modules\AssessmentModule\Models\Builders;
use Illuminate\Database\Eloquent\Builder;

class QuizBuilder extends Builder
{
     /**
     * Scope the query to only include quizzes with a 'published' status.
     *
     * @return \Modules\AssessmentModule\Models\Builders\QuizBuilder
     */
    public function published(): self
    {
        return $this->where('status','published');
    }
    /**
     * Scope the query to only include quizzes with a 'draft' status.
     *
     * @return \Modules\AssessmentModule\Models\Builders\QuizBuilder
     */
    public function draft(): self 
    {
         return $this->where('status','draft');
    }

    /**
     * Scope the query to include quizzes that are currently available (based on `available_from` and `due_date`).
     *
     * Quizzes are considered available if:
     * - `available_from` is either null or less than or equal to the current time.
     * - `due_date` is either null or greater than or equal to the current time.
     *
     * @return \Modules\AssessmentModule\Models\Builders\QuizBuilder
     */
    public function availableNow(): self
    {
        return $this->where(function ($q) {
            $q->whereNull('available_from')
                ->orWhere('available_from', '<=', now());
        })->where(function ($q) {
            $q->whereNull('due_date')
                ->orWhere('due_date', '>=', now());
        });
    }

    /**
     * Scope the query to only include quizzes for a specific course.
     *
     * @param int $courseId The ID of the course.
     * @return \Modules\AssessmentModule\Models\Builders\QuizBuilder
     */
    public function forCourse(int $courseId): self
    {
        return $this->where('course_id', $courseId);
    }

    /**
     * Scope the query to only include quizzes for a specific instructor.
     *
     * @param int $instructorId The ID of the instructor.
     * @return \Modules\AssessmentModule\Models\Builders\QuizBuilder
     */
    public function forInstructor(int $instructorId): self
    {
        return $this->where('instructor_id', $instructorId);
    }

    /**
     * Apply multiple filters to the query based on the provided filters array.
     *
     * The available filters include:
     * - `course_id`: Filters by the course ID.
     * - `instructor_id`: Filters by the instructor ID.
     * - `type`: Filters by the quiz type (e.g., 'quiz', 'assignment', 'practice').
     * - `status`: Filters by the quiz status (e.g., 'published', 'draft').
     * - `order`: Orders the results by `id` (e.g., 'latest', 'oldest').
     * - `available_now`: Filters by quizzes that are available now based on `available_from` and `due_date`.
     *
     * @param array $filters An array of filters to apply.
     * @return \Modules\AssessmentModule\Models\Builders\QuizBuilder
     */
    public function filter(array $filters)
    {
        return $this
            /**** Filter by Course */
            ->when(
                $filters['course_id'] ?? null,
                fn (Builder $q, $val) => $q->forCourse((int)$val)
            )
            /***** Filter by Instructor */
            ->when(
                $filters['instructor_id'] ?? null,
                fn (Builder $q, $val) => $q->forInstructor((int)$val)
            )
            /***** Filter by Type */
            ->when(
                $filters['type'] ?? null,
                fn (Builder $q, $val) => match ((string)$val) {
                    'quiz' => $q->where('type', 'quiz'),
                    'assignment' => $q->where('type', 'assignment'),
                    'practice' => $q->where('type', 'practice'),
                    default => $q
                }
            )
            /***** Filter by Status */
            ->when(
                $filters['status'] ?? null,
                fn (Builder $q, $val) => match ((string) $val) {
                    'published' => $q->where('status', 'published'),
                    'draft' => $q->where('status', 'draft'),
                    default => $q
                }
            )
            /**** Ordering */
            ->when(
                $filters['order'] ?? null,
                fn (Builder $q, $val) => match ((string)$val) {
                    'latest' => $q->latest('id'),
                    'oldest' => $q->oldest('id'),
                    default => $q
                }
            )
            /**** Filter by Available Now */
            ->when(
                filter_var($filters['available_now'] ?? false, FILTER_VALIDATE_BOOLEAN),
                fn (Builder $q) => $q->availableNow()
            );
    }
}