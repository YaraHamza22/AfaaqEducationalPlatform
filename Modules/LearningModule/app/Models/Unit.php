<?php

namespace Modules\LearningModule\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Modules\LearningModule\Builders\UnitBuilder;
use Spatie\Activitylog\LogOptions;
use Spatie\Translatable\HasTranslations;

class Unit extends Model
{
    /**
     * Represents a unit within a course in the e-learning platform.
     * Organizes course content into logical sections, containing multiple lessons and supporting soft deletion for content management.
     */
    use HasTranslations, SoftDeletes, CascadeSoftDeletes, LogsActivity;

    /** Translatable attributes (en, ar). */
    public array $translatable = ['title', 'description'];

    /**
     * The relationships that should cascade on delete.
     *
     * @var array
     */
    protected $cascadeDeletes = ['lessons'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'unit_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_id',
        'unit_order',
        'title',
        'description',
        'actual_duration_minutes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
            'unit_order' => 'integer',
            'actual_duration_minutes' => 'integer',
        ];
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return UnitBuilder
     */
    public function newEloquentBuilder($query): UnitBuilder
    {
        return new UnitBuilder($query);
    }

    // Relationships

    /**
     * Get the course that owns the unit.
     *
     * @return BelongsTo
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }

    /**
     * Get the lessons for the unit.
     *
     * @return HasMany
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'unit_id', 'unit_id')->orderBy('lesson_order');
    }

    /**
     * Configure activity logging for Unit model.
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            // translate the title to the current language
            ->setDescriptionForEvent(function (string $eventName) {
                return "Unit '" . ($this->getTranslation('title', 'en') ?: $this->title) . "' was {$eventName}";
            });
    }
}
