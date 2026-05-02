<?php

namespace Modules\AssesmentModule\Builders;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class AttemptBuilder
 *
 * This class extends the Eloquent `Builder` class to provide custom query scope methods for filtering and querying attempts.
 * It includes methods for filtering attempts by status (`in_progress`, `submitted`, `graded`), quiz, student, and other attributes
 * such as `score`, `is_passed`, and `time_spent_seconds`. The `filter()` method dynamically applies multiple filters based on the provided filters array.
 *
 * @package Modules\AssesmentModule\Builders
 */
class AttemptBuilder extends Builder
{
    /**
     * Scope the query to only include attempts with an 'in_progress' status.
     *
     * @return \Modules\AssesmentModule\Builders\AttemptBuilder
     */
    public function inProgress(): AttemptBuilder
    {
        return $this->where('status', 'in_progress');
    }

    /**
     * Scope the query to only include attempts with a 'submitted' status.
     *
     * @return \Modules\AssesmentModule\Builders\AttemptBuilder
     */
    public function submitted(): AttemptBuilder
    {
        return $this->where('status', 'submitted');
    }

    /**
     * Scope the query to only include attempts with a 'graded' status.
     *
     * @return \Modules\AssesmentModule\Builders\AttemptBuilder
     */
    public function graded(): AttemptBuilder
    {
        return $this->where('status', 'graded');
    }

    /**
     * Apply multiple filters to the query based on the provided filters array.
     *
     * The available filters include:
     * - `quiz_id`: Filters by the quiz ID.
     * - `student_id`: Filters by the student ID.
     * - `status`: Filters by the status of the attempt (e.g., `in_progress`, `submitted`, `graded`).
     * - `is_passed`: Filters by the `is_passed` status (boolean).
     * - `graded_by`: Filters by the user who graded the attempt.
     * - `attempt_number`: Filters by the attempt number.
     * - `min_score` and `max_score`: Filters by score range.
     * - `start_at` and `ends_at`: Filters by the start and end time.
     * - `submitted_from` and `submitted_to`: Filters by the submission date range.
     * - `graded_at`: Filters by the grading date.
     * - `min_time_spent` and `max_time_spent`: Filters by time spent on the attempt.
     * - `order`: Orders the results (e.g., `latest`, `oldest`).
     *
     * @param array $filters An array of filters to apply.
     * @return \Modules\AssesmentModule\Builders\AttemptBuilder
     */
    public function filter(array $filters): self
    {
        return $this
            /**** Filter by quiz_id */
            ->when(
                $filters['quiz_id'] ?? null,
                fn (Builder $q, $val) => $q->where('quiz_id', (int)$val)
            )
            /**** Filter by student_id */
            ->when(
                $filters['student_id'] ?? null,
                fn (Builder $q, $val) => $q->where('student_id', (int)$val)
            )
            /**** Filter by status */
            ->when(
                $filters['status'] ?? null,
                fn (Builder $q, $val) => match ((string)$val) {
                    'in_progress' => $q->inProgress(),
                    'submitted' => $q->submitted(),
                    'graded' => $q->graded(),
                    default => $q
                }
            )
            /**** Filter by is_passed */
            ->when(
                array_key_exists('is_passed', $filters),
                fn (Builder $q) => $q->where('is_passed', filter_var($filters['is_passed'], FILTER_VALIDATE_BOOLEAN))
            )
            /**** Filter by graded_by */
            ->when(
                $filters['graded_by'] ?? null,
                fn (Builder $q, $val) => $q->where('graded_by', (int)$val)
            )
            /**** Filter by attempt_number */
            ->when(
                $filters['attempt_number'] ?? null,
                fn (Builder $q, $val) => $q->where('attempt_number', (int)$val)
            )
            /**** Filter by score range */
            ->when(
                $filters['min_score'] ?? null,
                fn (Builder $q, $val) => $q->where('score', '>=', (int)$val)
            )
            ->when(
                $filters['max_score'] ?? null,
                fn (Builder $q, $val) => $q->where('score', '<=', (int)$val)
            )
            /**** Filter by start_at */
            ->when(
                $filters['start_at'] ?? null,
                fn (Builder $q, $val) => $q->whereDate('start_at', '>=', $val)
            )
            /**** Filter by ends_at */
            ->when(
                $filters['ends_at'] ?? null,
                fn (Builder $q, $val) => $q->whereDate('ends_at', '<=', $val)
            )
            /**** Filter by submitted_at date range */
            ->when(
                $filters['submitted_from'] ?? null,
                fn (Builder $q, $val) => $q->whereDate('submitted_at', '<=', $val)
            )
            ->when(
                $filters['submitted_to'] ?? null,
                fn (Builder $q, $val) => $q->whereDate('submitted_at', '>=', $val)
            )
            /**** Filter by graded_at */
            ->when(
                $filters['graded_at'] ?? null,
                fn (Builder $q, $val) => $q->whereDate('graded_at', '<=', $val)
            )
            /**** Filter by time spent range */
            ->when(
                $filters['min_time_spent'] ?? null,
                fn (Builder $q, $val) => $q->where('time_spent_seconds', '>=', (int)$val)
            )
            ->when(
                $filters['max_time_spent'] ?? null,
                fn (Builder $q, $val) => $q->where('time_spent_seconds', '<=', (int)$val)
            )
            /**** Ordering Strategy */
            ->when(
                $filters['order'] ?? null,
                fn (Builder $q, $val) => match ((string)$val) {
                    'latest' => $q->orderByDesc('id'),
                    'oldest' => $q->orderByAsc('id'),
                    default => $q
                }
            );
    }
}
