# CommunicationModule API Bodies

## Chat
- `POST /api/v1/chat-threads`
```json
{
  "title": "Course Help Thread",
  "course_id": 12,
  "participant_ids": [5, 9, 13]
}
```
- `POST /api/v1/chat-threads/{chatThread}/participants`
```json
{
  "user_id": 18,
  "role": "member"
}
```
- `POST /api/v1/chat-threads/{chatThread}/messages`
```json
{
  "body": "Hello instructor, I need clarification on lesson 3.",
  "metadata": {
    "client_message_id": "msg-123"
  }
}
```

## Notifications
- `POST /api/v1/notifications`
```json
{
  "user_ids": [5, 9],
  "title": "Quiz Reminder",
  "body": "Your quiz is due tomorrow.",
  "type": "deadline",
  "data": {
    "quiz_id": 44
  }
}
```

## Forum
- `POST /api/v1/forum-threads`
```json
{
  "course_id": 12,
  "title": "Week 2 discussion",
  "body": "Share your thoughts on this week's topic."
}
```
- `PUT /api/v1/forum-threads/{forumThread}`
```json
{
  "title": "Week 2 updated discussion",
  "is_pinned": true
}
```
- `POST /api/v1/forum-threads/{forumThread}/posts`
```json
{
  "body": "My answer is based on the reading section."
}
```
- `POST /api/v1/forum-posts/{forumPost}/react`
```json
{
  "reaction": "like"
}
```
- `POST /api/v1/forum-posts/{forumPost}/report`
```json
{
  "reason": "spam",
  "details": "Repeated unrelated links."
}
```
- `POST /api/v1/forum-post-reports/{forumPostReport}/review`
```json
{
  "status": "resolved"
}
```

## Integrations and Virtual Sessions
- `POST /api/v1/external-integrations`
```json
{
  "provider": "zoom",
  "external_account_id": "zoom_user_001",
  "access_token": "encrypted-token",
  "refresh_token": "encrypted-refresh-token",
  "expires_at": "2026-05-01T10:00:00Z",
  "is_active": true
}
```
- `POST /api/v1/virtual-sessions`
```json
{
  "course_id": 12,
  "integration_id": 1,
  "host_id": 5,
  "provider": "zoom",
  "title": "Live Q&A Session",
  "description": "Open questions and answers.",
  "starts_at": "2026-05-02T17:00:00Z",
  "ends_at": "2026-05-02T18:00:00Z",
  "metadata": {
    "timezone": "UTC+3"
  }
}
```
- `PUT /api/v1/virtual-sessions/{virtualSession}`
```json
{
  "title": "Updated Live Q&A Session",
  "status": "published",
  "join_url": "https://zoom.us/j/123456789"
}
```
- `POST /api/v1/virtual-sessions/{virtualSession}/attendance`
```json
{
  "user_id": 9,
  "joined_at": "2026-05-02T17:05:00Z",
  "left_at": "2026-05-02T17:55:00Z",
  "duration_minutes": 50
}
```
- `POST /api/v1/webhooks/integrations/{provider}`
```json
{
  "event": "session.updated",
  "provider_event_id": "evt_123",
  "payload": {
    "status": "completed"
  }
}
```

## Offline Content
- `POST /api/v1/offline-packages`
```json
{
  "course_id": 12,
  "version": "v1.0.0",
  "manifest": {
    "files": [
      {
        "path": "lesson-1/video.mp4",
        "checksum": "sha256:..."
      }
    ]
  },
  "file_url": "https://cdn.example.com/offline/course-12-v1.zip",
  "is_active": true
}
```
- `POST /api/v1/offline-packages/{offlinePackage}/tokens`
```json
{
  "user_id": 9,
  "device_id": "android-abc-123",
  "expires_at": "2026-05-31T23:59:59Z"
}
```
- `POST /api/v1/offline-sync-logs`
```json
{
  "user_id": 9,
  "offline_package_id": 2,
  "device_id": "android-abc-123",
  "action": "sync_completed",
  "payload": {
    "downloaded_files": 16
  }
}
```
