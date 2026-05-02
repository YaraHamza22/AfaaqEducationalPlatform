<?php

namespace Modules\AssesmentModule\Builders;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class AnswerBuilder
 *
 * This class extends the Eloquent `Builder` class to provide custom query scope methods for filtering and querying answers.
 * It includes methods for filtering answers by attributes such as `attempt_id`, `question_id`, `selected_option_id`, 
 * `answer_text`, `is_correct`, `score`, and other related fields. The `filter()` method applies multiple filters 
 * dynamically based on the provided filters array.
 *
 * @package Modules\AssesmentModule\Builders
 */
class AnswerBuilder extends Builder
{
    /**
     * Scope the query to only include correct answers.
     *
     * This method filters the query to only include answers that are marked as correct (i.e., `is_correct` is `true`).
     *
     * @return \Modules\AssesmentModule\Builders\AnswerBuilder
     */
    public function correct(): self
    {
        return $this->where('is_correct', true);
    }

    /**
     * Apply multiple filters to the query based on the provided filters array.
     *
     * The available filters include:
     * - `quiz_id`: Filters by quiz via the related attempt.
     * - `student_id`: Filters by student via the related attempt.
     * - `attempt_id`: Filters by the attempt ID.
     * - `question_id`: Filters by the question ID.
     * - `selected_option_id`: Filters by the selected option ID.
     * - `answer_text`: Filters by the answer text in the English translation (`answer_text->en`).
     * - `is_correct`: Filters by whether the answer is correct or not (boolean).
     * - `min_score` and `max_score`: Filters by the score range for the question.
     * - `boolean_answer`: Filters by a boolean answer value (true/false).
     * - `graded_at`: Filters by the grading date.
     * - `graded_by`: Filters by the user who graded the answer.
     *
     * @param array $filters An array of filters to apply.
     * @return \Modules\AssesmentModule\Builders\AnswerBuilder
     */
    public function filter(array $filters): self
    {
        return $this
            /**** Filter by quiz_id (via attempt) */
            ->when(
                $filters['quiz_id'] ?? null,
                fn (Builder $q, $val) => $q->whereHas(
                    'attempt',
                    fn (Builder $aq) => $aq->where('quiz_id', (int) $val)
                )
            )
            /**** Filter by student_id (via attempt) */
            ->when(
                $filters['student_id'] ?? null,
                fn (Builder $q, $val) => $q->whereHas(
                    'attempt',
                    fn (Builder $aq) => $aq->where('student_id', (int) $val)
                )
            )
            /**** Filter by attempt_id */
            ->when(
                $filters['attempt_id'] ?? null,
                fn (Builder $q, $val) => $q->where('attempt_id', (int)$val)
            )
            /**** Filter by question_id */
            ->when(
                $filters['question_id'] ?? null,
                fn (Builder $q, $val) => $q->where('question_id', (int)$val)
            )
            /**** Filter by selected_option_id */
            ->when(
                $filters['selected_option_id'] ?? null,
                fn (Builder $q, $val) => $q->where('selected_option_id', (int)$val)
            )
            /**** Filter by answer_text */
            ->when(
                $filters['answer_text'] ?? null,
                fn (Builder $q, $val) =>
                    $q->where('answer_text->en', 'like', '%' . (string)$val . '%')
            )
            /**** Filter by is_correct */
            ->when(
                array_key_exists('is_correct', $filters),
                fn (Builder $q) => $q->where('is_correct', filter_var($filters['is_correct'], FILTER_VALIDATE_BOOLEAN))
            )
            /**** Filter by score range */
            ->when(
                $filters['min_score'] ?? null,
                fn (Builder $q, $val) => $q->where('question_score', '>=', (int)$val)
            )
            ->when(
                $filters['max_score'] ?? null,
                fn (Builder $q, $val) => $q->where('question_score', '<=', (int)$val)
            )
            /**** Filter by boolean_answer */
            ->when(
                array_key_exists('boolean_answer', $filters),
                fn (Builder $q) => $q->where('boolean_answer', filter_var($filters['boolean_answer'], FILTER_VALIDATE_BOOLEAN))
            )
            /**** Filter by graded_at */
            ->when(
                $filters['graded_at'] ?? null,
                fn (Builder $q, $val) => $q->whereDate('graded_at', (string)$val)
            )
            /**** Filter by graded_by */
            ->when(
                $filters['graded_by'] ?? null,
                fn (Builder $q, $val) => $q->where('graded_by', (int)$val)
            );
    }
}
