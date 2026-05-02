<?php

namespace Modules\CommunicationModule\Policies;

use App\Models\User;
use Modules\CommunicationModule\Models\ChatParticipant;
use Modules\CommunicationModule\Models\ChatThread;

class ChatThreadPolicy
{
    public function view(User $user, ChatThread $chatThread): bool
    {
        return ChatParticipant::query()
            ->where('chat_thread_id', $chatThread->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function manage(User $user, ChatThread $chatThread): bool
    {
        return (int) $chatThread->created_by === (int) $user->id;
    }
}
