<?php

namespace Modules\AssesmentModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\AssesmentModule\Builders\AnswerBuilder;
use Modules\UserMangementModule\Models\User;
use Spatie\Translatable\HasTranslations;

/**
 * Class Answer
 *
 * Represents an answer submitted for a question attempt.
 *
 * @package Modules\AssesmentModule\Models
 *
 * @property int $id
 * @property int $attempt_id
 * @property int $question_id
 * @property int|null $selected_option_id
 * @property array|string|null $answer_text  Array when casted / translatable content
 * @property bool|null $boolean_answer
 * @property bool|null $is_correct
 * @property int|null $question_score
 * @property int|null $graded_by
 * @property Carbon|null $graded_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Attempt $attempt
 * @property-read Question $question
 * @property-read User|null $grader
 */
class Answer extends Model
{
    use HasFactory;
    use HasTranslations;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'attempt_id',
        'question_id',
        'selected_option_id',
        'answer_text',
        'boolean_answer',
        'is_correct',
        'question_score',
        'graded_by',
        'graded_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'boolean_answer' => 'boolean',
        'is_correct' => 'boolean',
        'graded_at' => 'datetime',
        'question_score' => 'integer',
        'answer_text' => 'array',
    ];

    /**
     * Attributes that are translatable (Spatie).
     *
     * @var string[]
     */
    public array $translatable = ['answer_text'];

    /**
     * Get the attempt that owns the answer.
     *
     * @return BelongsTo<int, Attempt>
     */
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(Attempt::class);
    }

    /**
     * Get the question that this answer belongs to.
     *
     * @return BelongsTo<int, Question>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the user who graded this answer.
     *
     * @return BelongsTo<int, User>
     */
    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Eloquent\Builder 
     * @return AnswerBuilder
     */


    public function newEloquentBuilder($query):AnswerBuilder
    {
        return new AnswerBuilder($query);
    }
}
