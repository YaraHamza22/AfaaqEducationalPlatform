<?php

namespace Modules\AssesmentModule\Builders;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class QuestionBuilder
 *
 * This class extends the Eloquent `Builder` class to provide custom query scope methods for filtering and querying questions.
 * It includes methods for filtering questions by `quiz_id`, `is_required`, and `order_index`, 
 * and supports dynamic filtering based on a set of criteria such as search terms, question type, and ordering.
 *
 * @package Modules\AssesmentModule\Models\Builders
 */
class QuestionBuilder extends Builder
{
    /**
     * Scope the query to only include questions for a specific quiz.
     *
     * @param int $quizId The ID of the quiz to filter by.
     * @return \Modules\AssesmentModule\Builders\QuestionBuilder
     */
    public function forQuiz(int $quizId): self
    {
        return $this->where('quiz_id', $quizId);
    }

    /**
     * Scope the query to only include required questions.
     *
     * @return \Modules\AssesmentModule\Builders\QuestionBuilder
     */
    public function required(): self
    {
        return $this->where('is_required', true);
    }

    /**
     * Scope the query to order questions by their `order_index` field.
     *
     * @return \Modules\AssesmentModule\Builders\QuestionBuilder
     */
    public function ordered(): self
    {
        return $this->orderBy('order_index');
    }

    /**
     * Apply multiple filters to the query based on the provided filters array.
     *
     * The available filters include:
     * - `search`: Filters questions based on a search term in the English translation of `question_text`.
     * - `quiz_id`: Filters questions by the quiz ID.
     * - `type`: Filters questions by type (e.g., 'mcq', 'true_false', 'text').
     * - `is_required`: Filters questions based on whether they are required (boolean).
     * - `order`: Orders the questions by `order_index` in ascending or descending order.
     *
     * @param array $filters An array of filters to apply.
     * @return \Modules\AssesmentModule\Builders\QuestionBuilder 
     */
    public function filter(array $filters): self
    {
        return $this
            /**** Direct search filter */
            ->when($filters['search'] ?? null,
                fn (Builder $q, $val) =>
                    $q->where('question_text->en', 'like', '%' . (string)$val . '%')
            )
            /**** Filter by quiz_id */
            ->when(
                $filters['quiz_id'] ?? null,
                fn (Builder $q, $val) => $q->forQuiz((int)$val)
            )
            /**** Filter by question type */
            ->when(
                $filters['type'] ?? null,
                fn (Builder $q, $val) => match ((string)$val) {
                    'mcq' => $q->where('type', 'mcq'),
                    'true_false' => $q->where('type', 'true_false'),
                    'text' => $q->where('type', 'text'),
                    default => $q
                }
            )
            /**** Filter by required status */
            ->when(
                filter_var($filters['is_required'] ?? null, FILTER_VALIDATE_BOOLEAN),
                fn (Builder $q) => $q->required()
            )
            /**** Ordering strategy */
            ->when(
                $filters['order'] ?? null,
                fn (Builder $q, $val) => match ((string)$val) {
                    'asc' => $q->orderBy('order_index', 'asc'),
                    'desc' => $q->orderBy('order_index', 'desc'),
                    default => $q
                }
            );
    }
}
