<?php

namespace Modules\AssesmentModule\Observers;

use Modules\AssesmentModule\Events\QuestionCreated;
use Modules\AssesmentModule\Models\Question;

/**
 * Class QuestionObserver
 *
 * Observes events related to the `Question` model and dispatches domain events 
 * such as `QuestionCreated` when appropriate. This observer listens to the `created` event
 * and triggers the `QuestionCreated` event after a new question has been successfully created.
 *
 * @package Modules\AssesmentModule\Observers
 */
class QuestionObserver
{
    /**
     * Handle the `created` event for the `Question` model.
     *
     * This method is triggered when a new `Question` model is created. It dispatches 
     * the `QuestionCreated` event with the created model, allowing other parts of the 
     * application to react to the creation of a new question.
     *
     * @param Question $question The question model instance that was created.
     * @return void
     * 
     * @see \Modules\AssesmentModule\Events\QuestionCreated
     */
    public function created(Question $question): void
    {
        event(new QuestionCreated($question)); // Dispatches the QuestionCreated event
    }
}
