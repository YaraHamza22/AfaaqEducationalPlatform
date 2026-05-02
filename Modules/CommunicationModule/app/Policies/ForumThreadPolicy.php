<?php

namespace Modules\CommunicationModule\Policies;

use App\Models\User;
use Modules\CommunicationModule\Models\ForumThread;

class ForumThreadPolicy
{
    public function update(User $user, ForumThread $forumThread): bool
    {
        return (int) $forumThread->author_id === (int) $user->id;
    }

    public function delete(User $user, ForumThread $forumThread): bool
    {
        return (int) $forumThread->author_id === (int) $user->id;
    }
}
