<?php

namespace Modules\AssesmentModule\Models;

use Modules\AssesmentModule\Builders\QuestionOptionBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;

/**
 * Class QuestionOption
 *
 * This class represents an option for a question in the quiz system. 
 * It contains the option text, a flag indicating whether the option is correct, 
 * and the relationship with the associated question. The `option_text` field 
 * is translatable, and custom logic ensures the text is trimmed before saving.
 *
 * @package Modules\AssesmentModule\Models
 */
class QuestionOption extends Model
{
    use HasFactory;
    use HasTranslations;

    /**
     * The name of the table associated with the model.
     *
     * @var string
     */
    protected $table = 'question_options';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'question_id',
        'option_text',
        'is_correct'
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    public array $translatable = ['option_text'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_correct' => 'boolean', // Ensures that is_correct is cast as a boolean
        'option_text' => 'array',
    ];

    /**
     * Get the question that this option belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Create a new instance of the custom Eloquent query builder for this model.
     *
     * @param \Illuminate\Database\Eloquent\Builder 
     * @return \Modules\AssesmentModule\Builders\QuestionOptionBuilder
     */
    public function newEloquentBuilder($query): QuestionOptionBuilder
    {
        return new QuestionOptionBuilder($query);
    }
}
