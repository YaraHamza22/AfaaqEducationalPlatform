<?php

namespace Modules\AssesmentModule\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\AssesmentModule\Models\Attempt;
/**
 * Modules\AssesmentModule\Events\AttemptGraded
 *
 * Event dispatched when an `Attempt` has been graded. The event
 * transports the graded `Attempt` model instance to listeners and
 * may be broadcasted to interested channels.
 *
 * @package Modules\AssesmentModule\Events
 */
class AttemptGraded 
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The graded attempt instance.
     *
     * @var \Modules\AssesmentModule\Models\Attempt
     */
    public Attempt $attempt;

    /**
     * Create a new event instance.
     *
     * @param \Modules\AssesmentModule\Models\Attempt $attempt The graded attempt.
     * @return void
     */
    public function __construct(Attempt $attempt)
    {
        $this->attempt = $attempt;
    }

}
