<?php

namespace Modules\CommunicationModule\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Modules\CommunicationModule\Http\Requests\Notification\StoreNotificationRequest;
use Modules\CommunicationModule\Services\V1\NotificationService;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $notificationService)
    {
    }

    public function index()
    {
        $query = DatabaseNotification::query()
            ->where('notifiable_id', Auth::id())
            ->orderByDesc('created_at');

        if (request()->boolean('unread_only')) {
            $query->whereNull('read_at');
        }

        return self::paginated($query->paginate(20), 'Notifications fetched successfully.');
    }

    public function store(StoreNotificationRequest $request)
    {
        $count = $this->notificationService->sendToUsers($request->validated());
        return self::success(['sent' => $count], 'Notifications dispatched successfully.');
    }

    public function markRead(string $notificationId)
    {
        $notification = DatabaseNotification::query()
            ->where('id', $notificationId)
            ->where('notifiable_id', Auth::id())
            ->firstOrFail();
        $notification->markAsRead();
        return self::success($notification->fresh(), 'Notification marked as read.');
    }

    public function markAllRead()
    {
        $updated = $this->notificationService->markAllReadForUser(Auth::id());
        return self::success(['updated' => $updated], 'All notifications marked as read.');
    }

    public function triggerDigest()
    {
        return self::success(['triggered' => true], 'Digest trigger accepted.');
    }
}
