<?php

namespace Modules\CommunicationModule\Policies;

use App\Models\User;
use Modules\CommunicationModule\Models\ExternalIntegration;

class ExternalIntegrationPolicy
{
    public function update(User $user, ExternalIntegration $externalIntegration): bool
    {
        return (int) $externalIntegration->user_id === (int) $user->id;
    }

    public function delete(User $user, ExternalIntegration $externalIntegration): bool
    {
        return (int) $externalIntegration->user_id === (int) $user->id;
    }
}
