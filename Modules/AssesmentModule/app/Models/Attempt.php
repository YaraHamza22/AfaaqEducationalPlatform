<?php

namespace Modules\AssesmentModule\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Modules\AssesmentModule\Builders\AttemptBuilder;
use Modules\UserMangementModule\Models\Student;
use Modules\UserMangementModule\Models\User;

/**
 * Class Attempt
 *
 * Represents a student's attempt at a quiz.
 *
 * @package Modules\AssesmentModule\Models
 *
 * @property int $id
 * @property int $quiz_id
 * @property int $student_id
 * @property int|null $attempt_number
 * @property string|null $status
 * @property int|null $score
 * @property bool|null $is_passed
 * @property Carbon|null $start_at
 * @property Carbon|null $ends_at
 * @property Carbon|null $submitted_at
 * @property Carbon|null $graded_at
 * @property int|null $graded_by
 * @property int|null $time_spent_seconds
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read \Modules\AssesmentModule\Models\Quiz|null $quiz
 * @property-read \Illuminate\Database\Eloquent\Collection|\Modules\AssesmentModule\Models\Answer[] $answer
 * @property-read \Modules\UserManagementModule\Models\User|null $student
 * @property-read \Modules\UserManagementModule\Models\User|null $grader
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\MediaLibrary\MediaCollections\Models\Media[] $media
 *
 * @property-read int|null $remaining_seconds      Remaining seconds until ends_at (appended)
 * @property-read bool $is_time_up                 Whether the attempt time is up (appended)
 * @property-read int|null $time_spent_seconds     Computed time spent in seconds (appended)
 */
class Attempt extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'quiz_id',
        'student_id',
        'attempt_number',
        'status',
        'score',
        'is_passed',
        'start_at',
        'ends_at',
        'submitted_at',
        'graded_at',
        'graded_by',
        'time_spent_seconds'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'score' => 'integer',
        'attempt_number' => 'integer',
        'start_at' => 'datetime',
        'ends_at' => 'datetime',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'is_passed' => 'boolean',
        'status' => \Modules\AssesmentModule\Enums\AttemptStatus::class,
    ];

    /**
     * Attributes appended to the model's array / JSON form.
     *
     * @var string[]
     */
    protected $appends = [
        'remaining_seconds',
        'is_time_up',
        'time_spent_seconds'
    ];

    /**
     * Get the quiz that owns the attempt.
     *
     * @return BelongsTo<int, \Modules\AssesmentModule\Models\Quiz>
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get the student (user) for this attempt.
     *
     * @return BelongsTo<int, User>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class,'student_id');
    }

    /**
     * Get answers for this attempt.
     *
     * Note: method name is `answer` in the original model (hasMany).
     *
     * @return HasMany<\Modules\AssesmentModule\Models\Answer>
     */
    public function answer(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Get the user who graded this attempt.
     *
     * @return BelongsTo<int, User>
     */
    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Remaining seconds until the attempt ends.
     *
     * Appended attribute: remaining_seconds
     *
     * @return Attribute<int|null, null>
     */
    protected function remainingSeconds(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->ends_at) {
                    return null;
                }
                return max(0, now()->diffInSeconds($this->ends_at, false));
            }
        );
    }

    /**
     * Whether the attempt time is up.
     *
     * Appended attribute: is_time_up
     *
     * @return Attribute<bool, null>
     */
    public function isTimeUp(): Attribute
    {
        return Attribute::make(get: fn() => $this->ends_at ? now()->gte($this->ends_at) : false);
    }

    /**
     * Computed time spent in seconds.
     *
     * Appended attribute: time_spent_seconds
     *
     * @return Attribute<int|null, int|null>
     */
    protected function timeSpentSeconds(): Attribute
    {
        return Attribute::make(get: function ($value) {
            if (!is_null($value)) {
                return (int) $value;
            }

            if (!$this->start_at) {
                return null;
            }

            $end = $this->submitted_at ?? now();
            return max(0, $this->start_at->diffInSeconds($end));
        });
    }

    /**
     * Model booted callback to handle automatic submitted_at assignment.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saving(function ($attempt) {

            if ($attempt->status === 'submitted' && empty($attempt->submitted_at)) {
                $attempt->submitted_at = now();
            }

            // Prevent submitted_at from being overwritten if originally set
            if ($attempt->isDirty('submitted_at') && $attempt->getOriginal('submitted_at')) {
                $attempt->submitted_at = $attempt->getOriginal('submitted_at');
            }
        });
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  Builder  
     * @return AttemptBuilder
     */
    public function newEloquentBuilder($query): AttemptBuilder
    {
        return new AttemptBuilder($query);
    }
}