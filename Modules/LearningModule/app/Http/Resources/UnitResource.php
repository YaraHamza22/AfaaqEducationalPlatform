<?php

namespace Modules\LearningModule\Http\Resources;

use App\Traits\HelperTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Unit API Resource
 *
 * Transforms Unit model into a consistent JSON structure for API responses.
 * Translatable fields (title, description) follow Accept-Language.
 */
class UnitResource extends JsonResource
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
        $locale = $this->getRequestLocale($request);

        return [
            'id' => $this->unit_id,
            'course_id' => $this->course_id,
            'title' => $this->getTranslatedAttribute($this->resource, 'title', $locale),
            'description' => $this->getTranslatedAttribute($this->resource, 'description', $locale),
            'unit_order' => $this->unit_order,
            'actual_duration_minutes' => $this->actual_duration_minutes,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),

            // Relationships (only included if loaded)
            'course' => $this->whenLoaded('course', function () use ($request) {
                return [
                    'id' => $this->course->course_id,
                    'title' => $this->getTranslatedAttribute($this->course, 'title', $this->getRequestLocale($request)),
                    'slug' => $this->course->slug,
                ];
            }),

            'lessons' => $this->whenLoaded('lessons', function () use ($request) {
                $locale = $this->getRequestLocale($request);
                return $this->lessons->map(function ($lesson) use ($locale) {
                    return [
                        'id' => $lesson->lesson_id,
                        'title' => $this->getTranslatedAttribute($lesson, 'title', $locale),
                        'lesson_order' => $lesson->lesson_order,
                    ];
                });
            }),

            'lessons_count' => $this->when(
                $this->relationLoaded('lessons'),
                fn() => $this->lessons->count()
            ),
        ];
    }
}
