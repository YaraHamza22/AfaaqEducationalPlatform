<?php

namespace Modules\AssesmentModule\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\AssesmentModule\Builders\QuizBuilder;
use Modules\LearningModule\Models\Course;
use Modules\UserMangementModule\Models\Instructor;
use Modules\UserMangementModule\Models\Student;
use Modules\UserMangementModule\Models\User;
use Spatie\Translatable\HasTranslations;
use Modules\AssesmentModule\Enums\QuizStatus;

/**
 * Class Quiz
 *
 * This class represents a quiz in the system. It contains various properties related to a quiz, 
 * such as its associated `course_id`, `instructor_id`, `status`, `title`, `description`, 
 * as well as attributes for scoring, duration, and availability. It defines relationships with other models 
 * (e.g., `Course`, `User`, `Question`), custom attributes like `durationSecond`, 
 * and handles soft deletes for associated questions.
 *
 * @package Modules\AssesmentModule\Models
 */
class Quiz extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasTranslations;

    /**
     * The name of the table associated with the model.
     *
     * @var string
     */
    protected $table = 'quizzes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'instructor_id',
        'quizable_id',
        'quizable_type',
        'type',
        'status',
        'title',
        'description',
        'max_score',
        'passing_score',
        'auto_grade_enabled',
        'available_from',
        'due_date',
        'duration_minutes',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'max_score' => 'integer',
        'passing_score' => 'integer',
        'available_from' => 'datetime',
        'due_date' => 'datetime',
        'auto_grade_enabled' => 'boolean',
        'duration_minutes' => 'integer',
        'title' => 'array',
        'description' => 'array',
        'status' => \Modules\AssesmentModule\Enums\QuizStatus::class,
        'quizable_type' => \Modules\AssesmentModule\Enums\QuizType::class,
        'type' => \Modules\AssesmentModule\Enums\AssesmentType::class,
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    public array $translatable = ['title', 'description'];

    /**
     * Get the course that this quiz belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }

    /**
     * Get the instructor who created this quiz.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Get the related model for this quiz (e.g., assignment, practice).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function quizable()
    {
        return $this->morphTo();
    }

    /**
     * Get the questions associated with this quiz.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('order_index');
    }

    /**
     * Get the attempts associated with this quiz.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attempts()
    {
        return $this->hasMany(Attempt::class, 'quiz_id');
    }
    
    public function students(){
        return $this->belongsToMany(Student::class,'quiz_student');
    }

    /**
     * Get the media associated with this quiz.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     *
    *public function media()
    *{
      *  return $this->morphMany(Media::class, 'mediable');}
     */

    /**
     * Define a custom accessor for the `duration_seconds` attribute.
     * 
     * This method calculates the duration of the quiz in seconds.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function durationSeconds(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->duration_minutes ? $this->duration_minutes * 60 : null
        );
    }

    /**
     * Define a custom accessor for the `is_published` attribute.
     * 
     * This method checks if the quiz status is 'published'.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function isPublished(): Attribute
    {
        return Attribute::make(get: fn () => $this->status === QuizStatus::PUBLISHED);
    }

    /**
     * Create a new instance of the custom Eloquent query builder for this model.
     *
     * @param \Illuminate\Database\Eloquent\Builder 
     * @return \Modules\AssesmentModule\Builders\QuizBuilder
     */
    public function newEloquentBuilder($query): QuizBuilder
    {
        return new QuizBuilder($query);
    }

    /**
     * Perform actions when a quiz is being deleted or restored (soft deletes).
     *
     * This method deletes related questions when the quiz is soft deleted and restores 
     * soft-deleted questions when the quiz is restored.
     */
    protected static function booted()
    {
        static::deleting(function (Quiz $quiz) {
            if ($quiz->isForceDeleting()) {
                return;
            }
            // Soft delete questions related to the quiz
            $quiz->questions()->delete();
        });

        static::restoring(function (Quiz $quiz) {
            // Restore soft-deleted questions related to the quiz
            $quiz->questions()->withTrashed()->restore();
        });
    }

}
