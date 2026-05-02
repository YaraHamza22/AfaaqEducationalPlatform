<?php

namespace Modules\CommunicationModule\Policies;

use App\Models\User;
use Modules\CommunicationModule\Models\ChatMessage;
use Modules\CommunicationModule\Models\ChatParticipant;

class ChatMessagePolicy
{
    public function interact(User $user, ChatMessage $chatMessage): bool
    {
        return ChatParticipant::query()
            ->where('chat_thread_id', $chatMessage->chat_thread_id)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function delete(User $user, ChatMessage $chatMessage): bool
    {
        return (int) $chatMessage->sender_id === (int) $user->id;
    }
}
