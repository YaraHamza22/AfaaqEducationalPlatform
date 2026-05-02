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
 * Modules\AssesmentModule\Events\AttemptSubmitted
 *
 * Event dispatched when a user submits an `Attempt`.
 * The event transports the submitted `Attempt` model instance and
 * can be broadcasted or handled by listeners.
 *
 * @package Modules\AssesmentModule\Events
 */
class AttemptSubmitted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The submitted attempt instance.
     *
     * @var \Modules\AssesmentModule\Models\Attempt
     */
    public Attempt $attempt;

    /**
     * Create a new event instance.
     *
     * @param \Modules\AssesmentModule\Models\Attempt $attempt The submitted attempt.
     * @return void
     */
    public function __construct(Attempt $attempt)
    {
        $this->attempt = $attempt;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     * @see \Illuminate\Broadcasting\PrivateChannel
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('attempts'),
        ];
    }
}
