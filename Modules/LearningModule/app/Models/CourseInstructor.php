<?php

namespace Modules\LearningModule\Models;

use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;

class CourseInstructor extends Pivot
{
    use LogsActivity;

    /**
     * Represents the pivot relationship between courses and instructors.
     * Manages instructor assignments to courses, including primary instructor designation and assignment tracking.
     */


    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'course_instructor_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'course_instructor';

    /**
     * i don't need timestamps for this model because i need only assigned_at and assigned_by.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_id',
        'instructor_id',
        'is_primary',
        'assigned_at',
        'assigned_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'assigned_at' => 'datetime',
        ];
    }

    // Relationships

    /**
     * Get the course that this instructor is assigned to.
     *
     * @return BelongsTo
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }

    /**
     * Get the instructor (user).
     *
     * @return BelongsTo
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id', 'id');
    }

    /**
     * Get the user who assigned this instructor.
     *
     * @return BelongsTo
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by', 'id');
    }

    /**
     * Configure activity logging for CourseInstructor model.
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
                $instructorName = optional($this->instructor)->name ?? "User #{$this->instructor_id}";
                $courseName = optional($this->course)->title ?? "Course #{$this->course_id}";
                return "Instructor assignment for {$instructorName} to '{$courseName}' was {$eventName}";
            });
    }
}
