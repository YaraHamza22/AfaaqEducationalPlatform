<?php

namespace Modules\LearningModule\Models;

use App\Models\User;
use App\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\LearningModule\Models\Enrollment;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Modules\LearningModule\Builders\CourseBuilder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\LearningModule\Models\CourseInstructor;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Course extends Model implements HasMedia
{
    /**
     * Represents a course in the e-learning platform.
     */
    use SoftDeletes, CascadeSoftDeletes, LogsActivity, InteractsWithMedia;

    /**
     * Relationships that should cascade on delete.
     *
     * @var array
     */
    protected $cascadeDeletes = ['units'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'course_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'objectives',
        'prerequisites',
        'actual_duration_hours',
        'course_category_id',
        'language',
        'status',
        'min_score_to_pass',
        'is_offline_available',
        'course_delivery_type',
        'difficulty_level',
        'average_rating',
        'total_ratings',
        'created_by',
        'published_at',
    ];

    /**
     * Cast attributes.
     */
    protected function casts(): array
    {
        return [
            'title' => 'array',
            'description' => 'array',
            'objectives' => 'array',
            'prerequisites' => 'array',
            'is_offline_available' => 'boolean',
            'min_score_to_pass' => 'decimal:2',
            'average_rating' => 'decimal:2',
            'published_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Use slug for route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Custom Eloquent builder.
     */
    public function newEloquentBuilder($query): CourseBuilder
    {
        return new CourseBuilder($query);
    }

    /* =====================
     | Relationships
     ===================== */

    public function courseCategory(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'course_category_id', 'course_category_id');
    }

    /**
     * Get the user who created the course.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_instructor', 'course_id', 'instructor_id')
            ->using(CourseInstructor::class)
            ->withPivot([
                'course_instructor_id',
                'is_primary',
                'assigned_at',
                'assigned_by',
            ]);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class, 'course_id', 'course_id')
            ->orderBy('unit_order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'course_id', 'course_id');
    }

    public function learners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments', 'course_id', 'learner_id')
            ->using(Enrollment::class)
            ->withPivot([
                'enrollment_id',
                'enrollment_type',
                'enrollment_status',
                'enrolled_at',
                'enrolled_by',
                'completed_at',
                'progress_percentage',
                'final_grade',
            ]);
    }

    public function students(): BelongsToMany
    {
        return $this->learners();
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
            ->setDescriptionForEvent(
                fn(string $event) =>
                "Course '{$this->getTranslation('title', 'en')}' was {$event}"
            );
    }

    /* =====================
     | Media Library
     ===================== */

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('intro_video')
            ->singleFile()
            ->acceptsMimeTypes(['video/mp4', 'video/quicktime']);
    }
}
