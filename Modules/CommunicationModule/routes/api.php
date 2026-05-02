<?php

use Illuminate\Support\Facades\Route;
use Modules\CommunicationModule\Http\Controllers\Api\V1\ChatMessageController;
use Modules\CommunicationModule\Http\Controllers\Api\V1\ChatThreadController;
use Modules\CommunicationModule\Http\Controllers\Api\V1\ForumPostController;
use Modules\CommunicationModule\Http\Controllers\Api\V1\ForumThreadController;
use Modules\CommunicationModule\Http\Controllers\Api\V1\IntegrationController;
use Modules\CommunicationModule\Http\Controllers\Api\V1\NotificationController;
use Modules\CommunicationModule\Http\Controllers\Api\V1\OfflinePackageController;
use Modules\CommunicationModule\Http\Controllers\Api\V1\VirtualSessionController;

Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    Route::apiResource('chat-threads', ChatThreadController::class)->only(['index', 'store', 'show']);
    Route::post('chat-threads/{chatThread}/archive', [ChatThreadController::class, 'archive']);
    Route::get('chat-threads/{chatThread}/participants', [ChatThreadController::class, 'participants']);
    Route::post('chat-threads/{chatThread}/participants', [ChatThreadController::class, 'addParticipant']);
    Route::delete('chat-threads/{chatThread}/participants/{userId}', [ChatThreadController::class, 'removeParticipant']);
    Route::get('chat-unread-count', [ChatThreadController::class, 'unreadCount']);

    Route::get('chat-threads/{chatThread}/messages', [ChatMessageController::class, 'index']);
    Route::post('chat-threads/{chatThread}/messages', [ChatMessageController::class, 'store']);
    Route::post('chat-messages/{chatMessage}/read', [ChatMessageController::class, 'markRead']);
    Route::delete('chat-messages/{chatMessage}', [ChatMessageController::class, 'destroy']);

    Route::apiResource('forum-threads', ForumThreadController::class);
    Route::post('forum-threads/{forumThread}/pin', [ForumThreadController::class, 'pin']);
    Route::post('forum-threads/{forumThread}/lock', [ForumThreadController::class, 'lock']);
    Route::get('forum-threads/{forumThread}/posts', [ForumPostController::class, 'index']);
    Route::post('forum-threads/{forumThread}/posts', [ForumPostController::class, 'store']);
    Route::put('forum-posts/{forumPost}', [ForumPostController::class, 'update']);
    Route::delete('forum-posts/{forumPost}', [ForumPostController::class, 'destroy']);
    Route::post('forum-posts/{forumPost}/react', [ForumPostController::class, 'react']);
    Route::post('forum-posts/{forumPost}/report', [ForumPostController::class, 'report']);
    Route::post('forum-post-reports/{forumPostReport}/review', [ForumPostController::class, 'reviewReport']);

    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications', [NotificationController::class, 'store']);
    Route::post('notifications/{notificationId}/read', [NotificationController::class, 'markRead']);
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::post('notifications/digest/trigger', [NotificationController::class, 'triggerDigest']);

    Route::apiResource('external-integrations', IntegrationController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('external-integrations/{provider}/oauth-url', [IntegrationController::class, 'oauthUrl']);
    Route::post('external-integrations/{provider}/exchange-code', [IntegrationController::class, 'exchangeCode']);

    Route::apiResource('virtual-sessions', VirtualSessionController::class);
    Route::post('virtual-sessions/{virtualSession}/publish', [VirtualSessionController::class, 'publish']);
    Route::post('virtual-sessions/{virtualSession}/cancel', [VirtualSessionController::class, 'cancel']);
    Route::post('virtual-sessions/{virtualSession}/attendance', [VirtualSessionController::class, 'storeAttendance']);
    Route::apiResource('offline-packages', OfflinePackageController::class)->only(['index', 'store', 'show']);
    Route::post('offline-packages/{offlinePackage}/tokens', [OfflinePackageController::class, 'issueToken']);
    Route::post('offline-packages/tokens/{offlineDownloadToken}/revoke', [OfflinePackageController::class, 'revokeToken']);
    Route::get('offline-packages/download/{token}', [OfflinePackageController::class, 'downloadByToken']);
    Route::post('offline-sync-logs', [OfflinePackageController::class, 'storeSyncLog']);
});

Route::prefix('v1')->group(function () {
    Route::post('webhooks/integrations/{provider}', [VirtualSessionController::class, 'webhook']);
});
