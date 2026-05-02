<?php

namespace Modules\CommunicationModule\Policies;

use App\Models\User;
use Modules\CommunicationModule\Models\OfflineDownloadToken;

class OfflineDownloadTokenPolicy
{
    public function revoke(User $user, OfflineDownloadToken $offlineDownloadToken): bool
    {
        return (int) $offlineDownloadToken->user_id === (int) $user->id;
    }
}
