<?php

namespace Modules\AssesmentModule\Observers;

use Illuminate\Support\Facades\Log;
use Modules\AssesmentModule\Events\AttemptGraded;
use Modules\AssesmentModule\Events\AttemptSubmitted;
use Modules\AssesmentModule\Models\Attempt;

/**
 * Class AttemptObserver
 *
 * Observes changes on the `Attempt` model and dispatches domain events 
 * such as `AttemptGraded` and `AttemptSubmitted` when the attempt 
 * status changes. This observer listens to the `updated` event and 
 * triggers the appropriate domain event based on the new `status` value.
 *
 * @package Modules\AssesmentModule\Observers
 */
class AttemptObserver
{
    /**
     * Handle the model "updated" event.
     *
     * This method is triggered whenever the `Attempt` model is updated.
     * It checks if the `status` attribute has changed and dispatches 
     * the corresponding domain event:
     * - If the `status` is set to `graded`, it dispatches the 
     *   `AttemptGraded` event.
     * - If the `status` is set to `submitted`, it dispatches the 
     *   `AttemptSubmitted` event.
     *
     * @param Attempt $attempt The attempt model instance that was updated.
     * @return void
     * 
     * @see \Modules\AssesmentModule\Events\AttemptGraded
     * @see \Modules\AssesmentModule\Events\AttemptSubmitted
     */
    public function updated(Attempt $attempt): void
    {
        // Check if the 'status' attribute has changed and dispatch the corresponding event
        if ($attempt->wasChanged('status') && $attempt->status === 'graded') {
            event(new AttemptGraded($attempt));
        } 

        if ($attempt->wasChanged('status') && $attempt->status === 'submitted') {
            event(new AttemptSubmitted($attempt));
        }
    }
}
