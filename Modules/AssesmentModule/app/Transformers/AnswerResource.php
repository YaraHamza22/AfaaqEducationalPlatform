<?php

namespace Modules\AssesmentModule\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class AnswerResource
 *
 * This resource transforms the `Answer` model data into a structured JSON response.
 * It includes the answer details, such as the selected option, answer text, correctness, score, and grading metadata.
 *
 * @package Modules\AssesmentModule\Transformers
 */
class AnswerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * This method defines how the answer data should be structured when returned as a JSON response.
     * It includes fields such as selected option, answer text (with translations), correctness, and grading details.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request.
     * 
     * @return array<string, mixed> The transformed answer resource.
     */
    public function toArray(Request $request): array
    {
        return [
            /** @var int $this->id The unique identifier of the answer */
            'id' => $this->id,

            /** @var int $this->attempt_id The ID of the associated attempt */
            'attempt_id' => $this->attempt_id,

            /** @var int $this->question_id The ID of the associated question */
            'question_id' => $this->question_id,

            /** @var int|null $this->selected_option_id The ID of the selected option (if applicable) */
            'selected_option_id' => $this->selected_option_id,

            /** @var array $this->answer_text The translated answer text (supports multiple languages) */
            'answer_text' => $this->getTranslations('answer_text'),

            /** @var bool|null $this->boolean_answer The boolean answer (for true/false questions) */
            'boolean_answer' => $this->boolean_answer,

            /** @var bool|null $this->is_correct Indicates whether the answer is correct */
            'is_correct' => $this->is_correct,

            /** @var int|null $this->question_score The score awarded for this question */
            'question_score' => $this->question_score,

            /** @var int|null $this->graded_by The ID of the user who graded the answer */
            'graded_by' => $this->graded_by,

            /** @var string|null $this->graded_at The timestamp when the answer was graded */
            'graded_at' => optional($this->graded_at)->toISOString(),
        ];
    }
}
