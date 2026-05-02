<?php

namespace Modules\AssesmentModule\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;
use Modules\AssesmentModule\Builders\QuestionBuilder;

/**
 * Class Question
 *
 * This class represents a Question in the database. It contains various properties and methods 
 * related to a quiz question, including relationships with other models, custom accessors, 
 * and translation support. It also defines how the question's data is cast and which fields 
 * are translatable.
 *
 * @package Modules\AssesmentModule\Models
 */
class Question extends Model
{
    use HasFactory;
    use HasTranslations;
    use SoftDeletes;

    /**
     * The name of the table associated with the model.
     *
     * @var string
     */
    protected $table = 'questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'quiz_id',
        'type',
        'question_text',
        'point',
        'order_index',
        'is_required',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'point' => 'integer',
        'order_index' => 'integer',
        'is_required' => 'boolean',
        'question_text' => 'array',
        'type' => \Modules\AssesmentModule\Enums\QuestionType::class,
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    public array $translatable = ['question_text'];

    /**
     * Get the quiz that this question belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    /**
     * Get the options for this question.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function options()
    {
        return $this->hasMany(QuestionOption::class, 'question_id');
    }

    /**
     * Get the answers for this question.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Define a custom accessor to check if the question is of type "MCQ".
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function isMultiChoiceQuestion(): Attribute
    {
        return Attribute::make(get: fn () => $this->type === 'multiple_choice');
    }

    /**
     * Define a custom setter for the `type` attribute to ensure it is stored in lowercase.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function type(): Attribute
    {
        return Attribute::make(set: fn ($v) => is_string($v) ? strtolower(trim($v)) : $v);
    }

    /**
     * Create a new instance of the custom Eloquent query builder for this model.
     *
     * @param \Illuminate\Database\Eloquent\Builder 
     * @return \Modules\AssesmentModule\Builders\QuestionBuilder
     */
    public function newEloquentBuilder($query): QuestionBuilder
    {
        return new QuestionBuilder($query);
    }

    /**
     * Perform actions when a question is being deleted or restored (soft deletes).
     */
    protected static function booted()
    {
        static::deleting(function (Question $q) {
            if ($q->isForceDeleting()) {
                return;
            }
            // Soft delete options related to the question
            $q->options()->delete();
        });

        static::restoring(function (Question $q) {
            // Restore soft-deleted options related to the question
            $q->options()->withTrashed()->restore();
        });
    }
}
