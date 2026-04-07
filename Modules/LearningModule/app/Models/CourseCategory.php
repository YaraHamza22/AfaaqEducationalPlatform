<?php

namespace Modules\LearningModule\Models;

use App\Traits\LogsActivity;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\LearningModule\Builders\CourseCategoryBuilder;
use Spatie\Activitylog\LogOptions;
use Spatie\Translatable\HasTranslations;

class CourseCategory extends Model
{
    use HasTranslations, LogsActivity, SoftDeletes, CascadeSoftDeletes;

    /** Translatable attributes (en, ar). */
    public array $translatable = ['name', 'description'];

    /**
     * Represents a course category in the e-learning platform.
     * Defines the classification and characteristics of courses, such as certification programs, workshops, or online courses.
     */

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'course_category_id';
    protected $cascadeDeletes = ['courses'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'target_audience',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'is_active' => 'boolean',
        ];
    }

    // Relationships

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return CourseCategoryBuilder
     */
    public function newEloquentBuilder($query): CourseCategoryBuilder
    {
        return new CourseCategoryBuilder($query);
    }

    /**
     * Get the courses for this course category.
     *
     * @return HasMany
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'course_category_id', 'course_category_id');
    }

    /**
     * Configure activity logging for CourseCategory model.
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                return "Course category '" . ($this->getTranslation('name', 'en') ?: $this->name) . "' was {$eventName}";
            });
    }
}
