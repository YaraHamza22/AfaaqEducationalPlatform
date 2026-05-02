# CommunicationModule Endpoint Report

Base prefix: `/api/v1`  
Auth: `auth:api` on all endpoints except webhook receiver (`/webhooks/integrations/{provider}`).

| Method | Route | Controller | Status |
| --- | --- | --- | --- |
| GET | `/chat-threads` | `ChatThreadController@index` | done |
| POST | `/chat-threads` | `ChatThreadController@store` | done |
| GET | `/chat-threads/{chatThread}` | `ChatThreadController@show` | done |
| POST | `/chat-threads/{chatThread}/archive` | `ChatThreadController@archive` | done |
| GET | `/chat-threads/{chatThread}/participants` | `ChatThreadController@participants` | done |
| POST | `/chat-threads/{chatThread}/participants` | `ChatThreadController@addParticipant` | done |
| DELETE | `/chat-threads/{chatThread}/participants/{userId}` | `ChatThreadController@removeParticipant` | done |
| GET | `/chat-unread-count` | `ChatThreadController@unreadCount` | done |
| GET | `/chat-threads/{chatThread}/messages` | `ChatMessageController@index` | done |
| POST | `/chat-threads/{chatThread}/messages` | `ChatMessageController@store` | done |
| POST | `/chat-messages/{chatMessage}/read` | `ChatMessageController@markRead` | done |
| DELETE | `/chat-messages/{chatMessage}` | `ChatMessageController@destroy` | done |
| GET | `/forum-threads` | `ForumThreadController@index` | done |
| POST | `/forum-threads` | `ForumThreadController@store` | done |
| GET | `/forum-threads/{forumThread}` | `ForumThreadController@show` | done |
| PUT/PATCH | `/forum-threads/{forumThread}` | `ForumThreadController@update` | done |
| DELETE | `/forum-threads/{forumThread}` | `ForumThreadController@destroy` | done |
| POST | `/forum-threads/{forumThread}/pin` | `ForumThreadController@pin` | done |
| POST | `/forum-threads/{forumThread}/lock` | `ForumThreadController@lock` | done |
| GET | `/forum-threads/{forumThread}/posts` | `ForumPostController@index` | done |
| POST | `/forum-threads/{forumThread}/posts` | `ForumPostController@store` | done |
| PUT | `/forum-posts/{forumPost}` | `ForumPostController@update` | done |
| DELETE | `/forum-posts/{forumPost}` | `ForumPostController@destroy` | done |
| POST | `/forum-posts/{forumPost}/react` | `ForumPostController@react` | done |
| POST | `/forum-posts/{forumPost}/report` | `ForumPostController@report` | done |
| POST | `/forum-post-reports/{forumPostReport}/review` | `ForumPostController@reviewReport` | done |
| GET | `/notifications` | `NotificationController@index` | done |
| POST | `/notifications` | `NotificationController@store` | done |
| POST | `/notifications/{notificationId}/read` | `NotificationController@markRead` | done |
| POST | `/notifications/read-all` | `NotificationController@markAllRead` | done |
| POST | `/notifications/digest/trigger` | `NotificationController@triggerDigest` | done |
| GET | `/external-integrations` | `IntegrationController@index` | done |
| POST | `/external-integrations` | `IntegrationController@store` | done |
| PUT/PATCH | `/external-integrations/{externalIntegration}` | `IntegrationController@update` | done |
| DELETE | `/external-integrations/{externalIntegration}` | `IntegrationController@destroy` | done |
| GET | `/external-integrations/{provider}/oauth-url` | `IntegrationController@oauthUrl` | done |
| POST | `/external-integrations/{provider}/exchange-code` | `IntegrationController@exchangeCode` | done |
| GET | `/virtual-sessions` | `VirtualSessionController@index` | done |
| POST | `/virtual-sessions` | `VirtualSessionController@store` | done |
| GET | `/virtual-sessions/{virtualSession}` | `VirtualSessionController@show` | done |
| PUT/PATCH | `/virtual-sessions/{virtualSession}` | `VirtualSessionController@update` | done |
| DELETE | `/virtual-sessions/{virtualSession}` | `VirtualSessionController@destroy` | done |
| POST | `/virtual-sessions/{virtualSession}/publish` | `VirtualSessionController@publish` | done |
| POST | `/virtual-sessions/{virtualSession}/cancel` | `VirtualSessionController@cancel` | done |
| POST | `/virtual-sessions/{virtualSession}/attendance` | `VirtualSessionController@storeAttendance` | done |
| POST | `/webhooks/integrations/{provider}` | `VirtualSessionController@webhook` | done |
| GET | `/offline-packages` | `OfflinePackageController@index` | done |
| POST | `/offline-packages` | `OfflinePackageController@store` | done |
| GET | `/offline-packages/{offlinePackage}` | `OfflinePackageController@show` | done |
| POST | `/offline-packages/{offlinePackage}/tokens` | `OfflinePackageController@issueToken` | done |
| POST | `/offline-packages/tokens/{offlineDownloadToken}/revoke` | `OfflinePackageController@revokeToken` | done |
| GET | `/offline-packages/download/{token}` | `OfflinePackageController@downloadByToken` | done |
| POST | `/offline-sync-logs` | `OfflinePackageController@storeSyncLog` | done |

## Finished in this implementation pass

- Added ownership/membership policy checks for chat, forum, integrations, virtual sessions, and offline token actions.
- Added business rules for locked forum threads and self-report prevention.
- Added dedicated `ReviewForumPostReportRequest` for report moderation action.
- Improved validations for course references and time constraints.
- Added HMAC-SHA256 webhook signature verification using per-provider configured secrets.
- Added OAuth start/code exchange endpoints for Zoom and Google Classroom integrations.
- Added provider API sync for virtual session publish/cancel and queued webhook processing.
- Added offline download token validation endpoint with device binding support.
- Added feature test coverage for auth gate and ownership enforcement.
- Expanded OpenAPI file with request schemas, route parameters, and endpoint-level request/response descriptions.

## Pending / Not finished

- Full positive-path feature tests for every endpoint.
- Request authorization methods with role-aware checks.
- Dedicated transformers/resources for response consistency.
- Deep provider-specific webhook event mapping (Google Classroom events still minimal).
