<?php

namespace Modules\AssesmentModule\Builders;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class QuestionOptionBuilder
 *
 * This class extends the Eloquent `Builder` class to provide custom query scope methods for filtering and querying question options.
 * It includes methods for filtering options by `question_id`, `is_correct`, and `option_text`. 
 * The `filter()` method allows dynamic application of filters based on the provided filters array.
 *
 * @package Modules\AssesmentModule\Builders
 */
class QuestionOptionBuilder extends Builder
{
    /**
     * Scope the query to only include correct options.
     *
     * This method filters the query to only include options that are marked as correct (i.e., `is_correct` is `true`).
     *
     * @return \Modules\AssesmentModule\Builders\QuestionOptionBuilder
     */
    public function correct(): self
    {
        return $this->where('is_correct', true);
    }

    /**
     * Apply multiple filters to the query based on the provided filters array.
     *
     * The available filters include:
     * - `question_id`: Filters by the question ID.
     * - `is_correct`: Filters by correct options (only those marked as `is_correct = true`).
     * - `option_text`: Filters options based on the option text. The text is searched in the English translation (`option_text->en`).
     *
     * @param array $filters An array of filters to apply.
     * @return \Modules\AssesmentModule\Builders\QuestionOptionBuilder
     */
    public function filter(array $filters): self
    {
        return $this
            /**** Filter by Question ID */
            ->when(
                $filters['question_id'] ?? null,
                fn (Builder $q, $val) => $q->where('question_id', (int)$val)
            )
            /**** Filter by Correct Option */
            ->when(
                filter_var($filters['is_correct'] ?? false, FILTER_VALIDATE_BOOLEAN),
                fn (Builder $q) => $q->correct()
            )
            /**** Filter by Option Text */
            ->when(
                $filters['option_text'] ?? null,
                fn (Builder $q, $val) =>
                    $q->where('option_text->en', 'like', '%' . (string)$val . '%')
            );
    }
}
