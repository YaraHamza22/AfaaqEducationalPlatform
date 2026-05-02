<?php

namespace Modules\AssesmentModule\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class AttemptResource
 *
 * This resource transforms the `Attempt` model data into a structured JSON response.
 * It includes metadata about the attempt, the quiz and student relationships, and associated answers and media.
 *
 * @package Modules\AssesmentModule\Transformers
 */
class AttemptResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * This method defines how the attempt data should be structured when returned as a JSON response.
     * It includes fields such as the attempt number, status, start and end times, score, and other metadata.
     * Additionally, it includes related answers and media if loaded.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request.
     * 
     * @return array<string, mixed> The transformed attempt resource.
     */
    public function toArray(Request $request): array
    {
        return [
            /** @var int $this->id The unique identifier of the attempt */
            'id' => $this->id,

            /** @var int $this->quiz_id The ID of the associated quiz */
            'quiz_id' => $this->quiz_id,

            /** @var int $this->student_id The ID of the student taking the attempt */
            'student_id' => $this->student_id,

            /** @var int $this->attempt_number The number of the attempt (e.g., 1, 2, 3) */
            'attempt_number' => $this->attempt_number,

            /** @var string $this->status The current status of the attempt (e.g., in_progress, submitted) */
            'status' => $this->status,

            /** @var string|null $this->start_at The timestamp when the attempt started */
            'start_at' => optional($this->start_at)->toISOString(),

            /** @var string|null $this->ends_at The timestamp when the attempt ended */
            'ends_at' => optional($this->ends_at)->toISOString(),

            /** @var int $this->remaining_seconds The remaining time for the attempt in seconds */
            'remaining_seconds' => $this->remaining_seconds,

            /** @var bool $this->is_time_up Indicates whether the time for the attempt is up */
            'is_time_up' => $this->is_time_up,

            /** @var int|null $this->score The score obtained for the attempt */
            'score' => $this->score,

            /** @var bool|null $this->is_passed Indicates whether the student passed the attempt */
            'is_passed' => $this->is_passed,

            /** @var string|null $this->submitted_at The timestamp when the attempt was submitted */
            'submitted_at' => optional($this->submitted_at)->toISOString(),

            /** @var string|null $this->graded_at The timestamp when the attempt was graded */
            'graded_at' => optional($this->graded_at)->toISOString(),

            /** @var int|null $this->graded_by The ID of the user who graded the attempt */
            'graded_by' => $this->graded_by,

            /** @var \Illuminate\Http\Resources\Json\AnonymousResourceCollection $this->answers The collection of answers for this attempt */
            'answers' => AnswerResource::collection($this->whenLoaded('answers')),

            /** @var \Illuminate\Http\Resources\Json\AnonymousResourceCollection $this->media The collection of media associated with this attempt */
            'media' => MediaResource::collection($this->whenLoaded('media')),
        ];
    }
}
