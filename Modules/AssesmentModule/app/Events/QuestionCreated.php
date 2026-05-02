<?php

namespace Modules\AssesmentModule\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Modules\AssesmentModule\Models\Question;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Modules\AssesmentModule\Events\QuestionCreated
 *
 * Event dispatched when a `Question` model is created. The event
 * carries the created question instance and may be broadcasted to
 * interested channels.
 *
 * @package Modules\AssesmentModule\Events
 */
class QuestionCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The created question instance.
     *
     * @var mixed
     */
    public Question $question;

    /**
     * Create a new event instance.
     *
     * @param mixed $question The created question model or payload.
     * @return void
     */
    public function __construct($question)
    {
        $this->question = $question;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('questions'),
        ];
    }
}
