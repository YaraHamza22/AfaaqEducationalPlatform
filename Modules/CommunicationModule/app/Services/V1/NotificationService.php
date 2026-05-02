<?php

namespace Modules\CommunicationModule\Services\V1;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

class NotificationService
{
    public function sendToUsers(array $payload): int
    {
        $count = 0;

        foreach ($payload['user_ids'] as $userId) {
            $user = User::query()->find($userId);
            if (!$user) {
                continue;
            }

            $user->notify(new GenericDatabaseNotification(
                $payload['title'],
                $payload['body'],
                $payload['type'] ?? 'system',
                $payload['data'] ?? []
            ));
            $count++;
        }

        return $count;
    }

    public function markAllReadForUser(int $userId): int
    {
        return DatabaseNotification::query()
            ->where('notifiable_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
