<?php

namespace Modules\LearningModule\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\LearningModule\Builders\LessonBuilder;
use Spatie\Activitylog\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Lesson extends Model implements HasMedia
{
    /**
     * Represents a lesson within a unit in the e-learning platform.
     */
    use HasTranslations, SoftDeletes, LogsActivity, InteractsWithMedia;

    /**
     * Translatable attributes.
     *
     * @var array<int, string>
     */
    public array $translatable = ['title', 'description'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'lesson_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'unit_id',
        'lesson_order',
        'title',
        'description',
        'lesson_type',
        'is_required',
        'is_completed',
        'actual_duration_minutes',
    ];

    /**
     * Cast attributes.
     */
    protected function casts(): array
    {
        return [
            'title' => 'array',
            'description' => 'array',
            'is_required' => 'boolean',
            'is_completed' => 'boolean',
            'lesson_order' => 'integer',
            'actual_duration_minutes' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Custom Eloquent builder.
     */
    public function newEloquentBuilder($query): LessonBuilder
    {
        return new LessonBuilder($query);
    }

    /* =====================
     | Relationships
     ===================== */

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'unit_id');
    }

    public function completedByEnrollments(): BelongsToMany
    {
        return $this->belongsToMany(
            Enrollment::class,
            'enrollment_lesson',
            'lesson_id',
            'enrollment_id'
        )
        ->withPivot(['completed_at'])
        ->withTimestamps();
    }

    /* =====================
     | Activity Log
     ===================== */

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $event) =>
                "Lesson '{$this->getTranslation('title', 'en')}' was {$event}"
            );
    }

    /* =====================
     | Media Library
     ===================== */

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments');

        $this->addMediaCollection('video')
            ->singleFile()
            ->acceptsMimeTypes([
                'video/mp4',
                'video/x-m4v',
                'video/quicktime',
            ]);
    }
}
