<?php

namespace Modules\CommunicationModule\Policies;

use App\Models\User;
use Modules\CommunicationModule\Models\VirtualSession;

class VirtualSessionPolicy
{
    public function update(User $user, VirtualSession $virtualSession): bool
    {
        return (int) $virtualSession->host_id === (int) $user->id;
    }

    public function delete(User $user, VirtualSession $virtualSession): bool
    {
        return (int) $virtualSession->host_id === (int) $user->id;
    }
}
