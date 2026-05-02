<?php

namespace Modules\AssesmentModule\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class QuestionResource
 *
 * This resource transforms the `Question` model data into a structured JSON response.
 * It includes the question details and related resources such as options and media.
 *
 * @package Modules\AssesmentModule\Transformers
 */
class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * This method defines how the question data should be structured when returned as a JSON response.
     * It includes fields such as question text (supporting translations), points, type, and options.
     * Additionally, it includes related media and question options (if loaded).
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request.
     * 
     * @return array<string, mixed> The transformed question resource.
     */
    public function toArray(Request $request): array
    {
        return [
            /** @var int $this->id The unique identifier of the question */
            'id' => $this->id,

            /** @var int $this->quiz_id The ID of the parent quiz */
            'quiz_id' => $this->quiz_id,

            /** @var string $this->type The type of the question (e.g., MCQ, True/False, Text) */
            'type' => $this->type,

            /** @var array $this->question_text Translated question text */
            'question_text' => $this->getTranslations('question_text'),

            /** @var int $this->point The points allocated for the question */
            'point' => $this->point,

            /** @var int $this->order_index The order of the question within the quiz */
            'order_index' => $this->order_index,

            /** @var bool $this->is_required Whether the question is required or optional */
            'is_required' => $this->is_required,

            /** @var \Illuminate\Http\Resources\Json\AnonymousResourceCollection $this->options The collection of options for this question */
            'options' => QuestionOptionResource::collection($this->whenLoaded('options')),

            /** @var \Illuminate\Http\Resources\Json\AnonymousResourceCollection $this->media The collection of media associated with the question */
            'media' => MediaResources::collection($this->whenLoaded('media')),
        ];
    }
}
