# Phase 2 Architecture Backlog (No Firebase)

## Chat (Student-Instructor)
- Stack: Laravel Broadcasting + Laravel Reverb + Redis + queue workers.
- Data model: `chat_threads`, `chat_participants`, `chat_messages`, `chat_message_reads`.
- Delivery: private channels per thread, unread counters, last message snapshot.
- Security: authorize thread membership in broadcast channel policies.

## Notifications
- Stack: Laravel Notifications with `database` channel first, optional `mail`.
- Data model: use `notifications` table + domain events from Assessment, Learning, and Chat.
- Delivery: queued notification jobs and periodic digest job for inactive users.

## Forum
- Data model: `forum_threads`, `forum_posts`, `forum_post_reactions`, `forum_post_reports`.
- Features: course-scoped threads, instructor pin/lock actions, soft-delete moderation.
- Safety: role-based create/update/delete and simple anti-spam throttle middleware.

## Zoom and Google Classroom Integrations
- Auth: OAuth 2.0 per instructor account with encrypted token storage.
- Data model: `external_integrations`, `virtual_sessions`, `session_attendance`.
- Flow: create session in LMS -> sync to provider API -> persist provider event IDs.
- Reliability: webhook handlers + retry queue jobs + dead-letter table for failed syncs.

## Offline Content
- Packaging: signed downloadable bundle manifest per course version.
- Data model: `offline_packages`, `offline_download_tokens`, `offline_sync_logs`.
- Rules: token expiry, device registration limit, revoke capability on enrollment drop.
- Client sync: delta manifest endpoint to fetch only changed assets.

## Reporting Read Models
- Add denormalized tables updated by scheduled jobs:
  - `assessment_progress_snapshots`
  - `certificate_funnel_snapshots`
  - `engagement_activity_snapshots`
- Keep API queries read-optimized and avoid expensive runtime joins.

### Status (started)
- Snapshot tables and read endpoints are implemented in `ReportingModule`.
- Added scheduled materialization command: `reporting:snapshots:materialize`.
- Current mode is batch snapshot generation (daily at 01:00) instead of runtime joins.
