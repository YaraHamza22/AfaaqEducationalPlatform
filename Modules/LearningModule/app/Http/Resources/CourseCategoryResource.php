<?php

namespace Modules\LearningModule\Http\Resources;

use App\Traits\HelperTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CourseCategory API Resource
 *
 * Transforms CourseCategory model into a consistent JSON structure for API responses.
 * Translatable fields (name, description) follow Accept-Language.
 */
class CourseCategoryResource extends JsonResource
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
            'id' => $this->course_category_id,
            'name' => $this->getTranslatedAttribute($this->resource, 'name', $locale),
            'slug' => $this->slug,
            'description' => $this->getTranslatedAttribute($this->resource, 'description', $locale),
            'is_active' => $this->is_active,
            'target_audience' => $this->target_audience,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),

            // Relationships (only included if loaded)
            'courses' => $this->whenLoaded('courses', function () use ($request) {
                $locale = $this->getRequestLocale($request);
                return $this->courses->map(function ($course) use ($locale) {
                    return [
                        'id' => $course->course_id,
                        'title' => $this->getTranslatedAttribute($course, 'title', $locale),
                        'slug' => $course->slug,
                        'status' => $course->status,
                    ];
                });
            }),

            'courses_count' => $this->when(
                $this->relationLoaded('courses'),
                fn() => $this->courses->count()
            ),
        ];
    }
}
