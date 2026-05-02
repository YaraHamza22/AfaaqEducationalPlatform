<?php

namespace Modules\AssesmentModule\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class QuestionOptionResource
 *
 * This resource transforms the `QuestionOption` model data into a structured JSON response.
 * It includes the option details such as the option text (supporting translations) and whether the option is correct.
 *
 * @package Modules\AssesmentModule\Transformers
 */
class QuestionOptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * This method defines how the question option data should be structured when returned as a JSON response.
     * It includes fields such as the option text (supporting translations) and whether the option is correct.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request.
     * 
     * @return array<string, mixed> The transformed question option resource.
     */
    public function toArray(Request $request): array
    {
        return [
            /** @var int $this->id The unique identifier of the question option */
            'id' => $this->id,

            /** @var int $this->question_id The ID of the associated question */
            'question_id' => $this->question_id,

            /** @var array $this->option_text Translated text for the question option */
            'option_text' => $this->getTranslations('option_text'),

            /** @var bool $this->is_correct Indicates whether the option is correct */
            'is_correct' => $this->is_correct,
        ];
    }
}
