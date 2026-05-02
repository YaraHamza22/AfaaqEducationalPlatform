<?php

namespace Modules\AssesmentModule\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class QuizResource
 *
 * Transform the Quiz model data into an array structure that can be returned as a JSON response.
 *
 * This resource includes the quiz metadata, its relationships to other models, and related resources like questions and media.
 *
 * @package Modules\AssesmentModule\Transformers
 */
class QuizResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request.
     * 
     * @return array<string, mixed> The transformed quiz resource.
     */
    public function toArray(Request $request): array
    {
        return [
            /** @var int $this->id The unique identifier of the quiz */
            'id' => $this->id,


            /** @var int $this->instructor_id The ID of the instructor who created the quiz */
            'instructor_id' => $this->instructor_id,

            /** @var int|null $this->quizable_id The ID for polymorphic relationships (if applicable) */
            'quizable_id' => $this->quizable_id,

            /** @var string|null $this->quizable_type The type for polymorphic relationships (if applicable) */
            'quizable_type' => $this->quizable_type,

            /** @var string $this->type Type of the quiz (quiz, assignment, practice) */
            'type' => $this->type,

            /** @var array $this->title Translated title of the quiz */
            'title' => $this->getTranslations('title'),

            /** @var array|null $this->description Translated description of the quiz */
            'description' => $this->getTranslations('description'),

            /** @var int $this->max_score The maximum score for the quiz */
            'max_score' => $this->max_score,

            /** @var int|null $this->passing_score The passing score for the quiz */
            'passing_score' => $this->passing_score,

            /** @var string $this->status The current status of the quiz (published, draft) */
            'status' => $this->status,

            /** @var bool $this->is_published Indicates whether the quiz is published */
            'is_published' => $this->is_published,

            /** @var bool $this->auto_grade_enabled Indicates whether auto-grading is enabled for this quiz */
            'auto_grade_enabled' => $this->auto_grade_enabled,

            /** @var string|null $this->available_from The date and time the quiz becomes available */
            'available_from' => optional($this->available_from)->toISOString(),

            /** @var string|null $this->due_date The date and time when the quiz is due */
            'due_date' => optional($this->due_date)->toISOString(),

            /** @var int|null $this->duration_minutes The duration of the quiz in minutes */
            'duration_minutes' => $this->duration_minutes,

            /** @var int|null $this->duration_seconds The duration of the quiz in seconds (if available) */
            'duration_seconds' => $this->duration_seconds,

            /** @var \Illuminate\Http\Resources\Json\AnonymousResourceCollection $this->questions Collection of associated questions */
            'questions' => QuestionResource::collection($this->whenLoaded('questions')),

            /** @var \Illuminate\Http\Resources\Json\AnonymousResourceCollection $this->media Collection of associated media */
            'media' => MediaResources::collection($this->whenLoaded('media')),

            /** @var string|null $this->created_at The timestamp of when the quiz was created */
            'created_at' => optional($this->created_at)->toISOString(),

            /** @var string|null $this->updated_at The timestamp of when the quiz was last updated */
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
