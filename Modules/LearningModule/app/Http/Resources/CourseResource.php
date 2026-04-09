<?php

namespace Modules\LearningModule\Http\Resources;

use App\Traits\HelperTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Course API Resource
 *
 * Transforms Course model into a consistent JSON structure for API responses.
 * Provides a standardized format for course data across all endpoints.
 * Translatable fields (title, description, objectives, prerequisites) follow Accept-Language.
 */
class CourseResource extends JsonResource
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
            'id' => $this->course_id,
            'title' => $this->getTranslatedAttribute($this->resource, 'title', $locale),
            'slug' => $this->slug,
            'description' => $this->getTranslatedAttribute($this->resource, 'description', $locale),
            'description_translations' => $this->resource->getTranslations('description'),
            'objectives' => $this->getTranslatedAttribute($this->resource, 'objectives', $locale),
            'objectives_translations' => $this->resource->getTranslations('objectives'),
            'prerequisites' => $this->getTranslatedAttribute($this->resource, 'prerequisites', $locale),
            'prerequisites_translations' => $this->resource->getTranslations('prerequisites'),
            'actual_duration_hours' => $this->actual_duration_hours,
            'language' => $this->language,
            'status' => $this->status,
            'min_score_to_pass' => $this->min_score_to_pass,
            'is_offline_available' => $this->is_offline_available,
            'course_delivery_type' => $this->course_delivery_type,
            'difficulty_level' => $this->difficulty_level,
            'average_rating' => $this->average_rating,
            'total_ratings' => $this->total_ratings,
            'published_at' => $this->published_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'cover_url' => $this->getFirstMediaUrl('cover'),
            'intro_video_url' => $this->getFirstMediaUrl('intro_video'),

        'attachments' => $this->getMedia('attachments')->map(function ($file) {
            return [
                'id' => $file->id,
                'file_name' => $file->file_name,
                'human_readable_size' => $file->getHumanReadableSize(),
                'mime_type' => $file->mime_type,
                'url' => $file->getUrl(),
            ];
        }),

            // Relationships (only included if loaded)
            'course_category' => $this->whenLoaded('courseCategory', function () use ($request) {
                return [
                    'id' => $this->courseCategory->course_category_id,
                    'name' => $this->getTranslatedAttribute($this->courseCategory, 'name', $this->getRequestLocale($request)),
                    'slug' => $this->courseCategory->slug,
                    'description' => $this->getTranslatedAttribute($this->courseCategory, 'description', $this->getRequestLocale($request)),
                    'is_active' => $this->courseCategory->is_active,
                ];
            }),

            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'email' => $this->creator->email,
                ];
            }),

            'instructors' => $this->whenLoaded('instructors', function () {
                return $this->instructors->map(function ($instructor) {
                    return [
                        'id' => $instructor->id,
                        'name' => $instructor->name,
                        'email' => $instructor->email,
                        'is_primary' => $instructor->pivot->is_primary ?? false,
                    ];
                });
            }),

            'units' => $this->whenLoaded('units', function () use ($request) {
                $locale = $this->getRequestLocale($request);
                return $this->units->map(function ($unit) use ($locale) {
                    return [
                        'id' => $unit->unit_id,
                        'title_translations' => $unit->getTranslations('title'),
                        'title' => $this->getTranslatedAttribute($unit, 'title', $locale),
                        'slug' => $unit->slug,
                        'unit_order' => $unit->unit_order,
                    ];
                });
            }),

            'enrollments_count' => $this->when(
                $this->relationLoaded('enrollments'),
                fn() => $this->enrollments->count()
            ),
        ];
    }
}
