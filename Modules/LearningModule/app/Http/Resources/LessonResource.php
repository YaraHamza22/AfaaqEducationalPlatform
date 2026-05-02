<?php

namespace Modules\LearningModule\Http\Resources;

use App\Traits\HelperTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\UploadedFile;
/**
 * Lesson API Resource
 *
 * Transforms Lesson model into a consistent JSON structure for API responses.
 * Translatable fields (title, description) follow Accept-Language.
 */
class LessonResource extends JsonResource
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
            'id' => $this->lesson_id,
            'unit_id' => $this->unit_id,
            'title' => $this->getTranslatedAttribute($this->resource, 'title', $locale),
            'title_translations' => $this->resource->getTranslations('title'),
            'description' => $this->getTranslatedAttribute($this->resource, 'description', $locale),
            'description_translations' => $this->resource->getTranslations('description'),
            'lesson_order' => $this->lesson_order,
            'lesson_type' => $this->lesson_type,
            'is_required' => $this->is_required,
            'actual_duration_minutes' => $this->actual_duration_minutes,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'video_url' => $this->getFirstMediaUrl('video'),
            'attachments' => $this->getMedia('attachments')->map(function ($file) {
                return [
                    'id' => $file->id,
                    'file_name' => $file->file_name,
                    'mime_type' => $file->mime_type,
                    'url' => $file->getUrl(),
                ];
            }),


            // Relationships (only included if loaded)
            'unit' => $this->whenLoaded('unit', function () use ($request) {
                return [
                    'id' => $this->unit->unit_id,
                    'title' => $this->getTranslatedAttribute($this->unit, 'title', $this->getRequestLocale($request)),
                ];
            }),
        ];
    }
}
