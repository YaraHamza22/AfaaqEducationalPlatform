<?php

namespace Modules\AssesmentModule\Builders;

use Illuminate\Database\Eloquent\Builder;
use Modules\AssesmentModule\Enums\QuizStatus;
use Modules\AssesmentModule\Enums\QuizType;

class QuizBuilder extends Builder
{
    /**
     * Scope the query to only include published quizzes.
    */

    public function published(): self
    {
        return $this->where('status', QuizStatus::PUBLISHED->value);
    }

    /**
     * Scope the query to only include draft quizzes.
     */
    public function draft(): self
    {
        return $this->where('status', QuizStatus::DRAFT->value);
    }

    /**
     * Scope the query to include quizzes that are currently available.
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
     */
    public function forCourse(int $courseId): self
    {
        return $this
            ->where('quizable_type', QuizType::COURSE->value)
            ->where('quizable_id', $courseId);
    }

    /**
     * Scope the query to only include quizzes for a specific unit.
     */
    public function forUnit(int $unitId): self
    {
        return $this
            ->where('quizable_type', QuizType::UNIT->value)
            ->where('quizable_id', $unitId);
    }

    /**
     * Scope the query to only include quizzes for a specific lesson.
     */
    public function forLesson(int $lessonId): self
    {
        return $this
            ->where('quizable_type', QuizType::LESSON->value)
            ->where('quizable_id', $lessonId);
    }

    /**
     * Scope the query to only include quizzes for a specific quizable target.
     */
    public function forQuizable(QuizType $type, int $id): self
    {
        return $this
            ->where('quizable_type', $type->value)
            ->where('quizable_id', $id);
    }

    /**
     * Scope the query to only include quizzes for a specific instructor.
     */
    public function forInstructor(int $instructorId): self
    {
        return $this->where('instructor_id', $instructorId);
    }

    /**
     * Apply multiple filters to the query.
     */
    public function filter(array $filters): self
    {
        return $this
            /**** Filter by Course (legacy support) */
            ->when(
                $filters['course_id'] ?? null,
                fn (Builder $q, $val) => $q->forCourse((int) $val)
            )

            /**** Filter by Unit */
            ->when(
                $filters['unit_id'] ?? null,
                fn (Builder $q, $val) => $q->forUnit((int) $val)
            )

            /**** Filter by Lesson */
            ->when(
                $filters['lesson_id'] ?? null,
                fn (Builder $q, $val) => $q->forLesson((int) $val)
            )

            /**** Filter by quizable_type + quizable_id */
            ->when(
                ($filters['quizable_type'] ?? null) && ($filters['quizable_id'] ?? null),
                function (Builder $q) use ($filters) {
                    $type = QuizType::tryFrom((string) $filters['quizable_type']);

                    return $type
                        ? $q->forQuizable($type, (int) $filters['quizable_id'])
                        : $q;
                }
            )

            /**** Filter by quizable_type only */
            ->when(
                $filters['quizable_type'] ?? null,
                function (Builder $q, $val) {
                    $type = QuizType::tryFrom((string) $val);

                    return $type
                        ? $q->where('quizable_type', $type->value)
                        : $q;
                }
            )

            /**** Filter by quizable_id only */
            ->when(
                $filters['quizable_id'] ?? null,
              fn (Builder $q, $val) => $q->where('quizable_id', (int) $val)
            )

            /**** Filter by Instructor */
            ->when(
                $filters['instructor_id'] ?? null,
                fn (Builder $q, $val) => $q->forInstructor((int) $val)
            )

            /**** Filter by Status */
            ->when(
                $filters['status'] ?? null,
                function (Builder $q, $val) {
                    $status = QuizStatus::tryFrom((string) $val);

                    return $status
                        ? $q->where('status', $status->value)
                        : $q;
                }
            )

            /**** Ordering */
            ->when(
                $filters['order'] ?? null,
                fn (Builder $q, $val) => match ((string) $val) {
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