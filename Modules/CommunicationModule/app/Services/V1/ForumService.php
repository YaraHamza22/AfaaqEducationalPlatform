<?php

namespace Modules\CommunicationModule\Services\V1;

use Modules\CommunicationModule\Models\ForumPost;
use Modules\CommunicationModule\Models\ForumPostReaction;
use Modules\CommunicationModule\Models\ForumPostReport;
use Modules\CommunicationModule\Models\ForumThread;

class ForumService
{
    public function createThread(array $payload, int $authorId): ForumThread
    {
        return ForumThread::query()->create([
            'course_id' => $payload['course_id'],
            'author_id' => $authorId,
            'title' => $payload['title'],
            'body' => $payload['body'] ?? null,
        ]);
    }

    public function createPost(ForumThread $thread, array $payload, int $authorId): ForumPost
    {
        return ForumPost::query()->create([
            'forum_thread_id' => $thread->id,
            'author_id' => $authorId,
            'body' => $payload['body'],
        ]);
    }

    public function react(ForumPost $post, array $payload, int $userId): ForumPostReaction
    {
        return ForumPostReaction::query()->firstOrCreate([
            'forum_post_id' => $post->id,
            'user_id' => $userId,
            'reaction' => $payload['reaction'],
        ]);
    }

    public function report(ForumPost $post, array $payload, int $reporterId): ForumPostReport
    {
        return ForumPostReport::query()->create([
            'forum_post_id' => $post->id,
            'reporter_id' => $reporterId,
            'reason' => $payload['reason'],
            'details' => $payload['details'] ?? null,
            'status' => 'pending',
        ]);
    }
}
