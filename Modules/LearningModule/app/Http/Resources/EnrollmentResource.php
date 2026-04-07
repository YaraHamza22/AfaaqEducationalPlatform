<?php

namespace Modules\LearningModule\Http\Resources;

use App\Traits\HelperTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Enrollment API Resource
 *
 * Transforms Enrollment model into a consistent JSON structure for API responses.
 * Provides a standardized format for enrollment data across all endpoints.
 *
 * Includes:
 * - Core enrollment information (id, status, type, progress)
 * - Timestamps (enrolled_at, completed_at)
 * - Relationships (learner, course, enrolled_by) - loaded conditionally
 * - Computed fields (duration_in_days, is_overdue)
 */
class EnrollmentResource extends JsonResource
{
    use HelperTrait;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Core enrollment information
            'id' => $this->enrollment_id,
            'learner_id' => $this->learner_id,
            'course_id' => $this->course_id,
            'enrollment_type' => $this->enrollment_type,
            'enrollment_status' => $this->enrollment_status?->value,

            // Progress and timing
            'progress_percentage' => $this->progress_percentage !== null ? (float) $this->progress_percentage : null,
            'final_grade' => $this->final_grade !== null ? (float)$this->final_grade : null,
            'enrolled_at' => $this->enrolled_at?->toDateTimeString(),
            'completed_at' => $this->completed_at?->toDateTimeString(),
            'enrolled_by' => $this->enrolled_by,

            // Timestamps
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),

            // Computed fields
            'enrollment_duration_days' => $this->getEnrollmentDurationDays(),
            'is_completed' => $this->enrollment_status?->value === 'completed',

            // Relationships (only included if loaded; null-safe in case relation is missing)
            'learner' => $this->whenLoaded('learner', function () {
                return $this->learner ? [
                    'id' => $this->learner->id,
                    'name' => $this->learner->name,
                    'email' => $this->learner->email,
                ] : null;
            }),

            'course' => $this->whenLoaded('course', function () use ($request) {
                return $this->course ? [
                    'id' => $this->course->course_id,
                    'title' => $this->getTranslatedAttribute($this->course, 'title', $this->getRequestLocale($request)),
                    'slug' => $this->course->slug,
                    'description' => $this->getTranslatedAttribute($this->course, 'description', $this->getRequestLocale($request)),
                    'status' => $this->course->status,
                    'actual_duration_hours' => $this->course->actual_duration_hours,
                    'min_score_to_pass' => $this->course->min_score_to_pass,
                ] : null;
            }),

            'enrolled_by_user' => $this->whenLoaded('enrolledBy', function () {
                return $this->enrolledBy ? [
                    'id' => $this->enrolledBy->id,
                    'name' => $this->enrolledBy->name,
                    'email' => $this->enrolledBy->email,
                ] : null;
            }),
        ];
    }

    /**
     * Get additional metadata that should always be included.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => '1.0',
            ],
        ];
    }

    /**
     * Calculate enrollment duration in days
     *
     * @return int|null
     */
    private function getEnrollmentDurationDays(): ?int
    {
        if (!$this->enrolled_at) {
            return null;
        }

        $endDate = $this->completed_at ?? now();
        return $this->enrolled_at->diffInDays($endDate);
    }
}
