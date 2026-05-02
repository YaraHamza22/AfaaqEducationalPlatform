<?php

namespace Modules\CommunicationModule\Policies;

use App\Models\User;
use Modules\CommunicationModule\Models\ForumPost;

class ForumPostPolicy
{
    public function update(User $user, ForumPost $forumPost): bool
    {
        return (int) $forumPost->author_id === (int) $user->id;
    }

    public function delete(User $user, ForumPost $forumPost): bool
    {
        return (int) $forumPost->author_id === (int) $user->id;
    }
}
