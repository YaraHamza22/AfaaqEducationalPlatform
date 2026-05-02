<?php

namespace Modules\CommunicationModule\Services\V1;

use Illuminate\Support\Facades\DB;
use Modules\CommunicationModule\Models\ChatMessage;
use Modules\CommunicationModule\Models\ChatMessageRead;
use Modules\CommunicationModule\Models\ChatParticipant;
use Modules\CommunicationModule\Models\ChatThread;

class ChatService
{
    public function createThread(array $payload, int $creatorId): ChatThread
    {
        return DB::transaction(function () use ($payload, $creatorId) {
            $thread = ChatThread::query()->create([
                'title' => $payload['title'] ?? null,
                'course_id' => $payload['course_id'] ?? null,
                'created_by' => $creatorId,
            ]);

            $participants = collect($payload['participant_ids'])
                ->push($creatorId)
                ->unique()
                ->values();

            foreach ($participants as $userId) {
                ChatParticipant::query()->firstOrCreate([
                    'chat_thread_id' => $thread->id,
                    'user_id' => $userId,
                ], [
                    'role' => $userId === $creatorId ? 'owner' : 'member',
                ]);
            }

            return $thread;
        });
    }

    public function addParticipant(ChatThread $thread, array $payload): ChatParticipant
    {
        return ChatParticipant::query()->firstOrCreate([
            'chat_thread_id' => $thread->id,
            'user_id' => $payload['user_id'],
        ], [
            'role' => $payload['role'] ?? 'member',
        ]);
    }

    public function sendMessage(ChatThread $thread, int $senderId, array $payload): ChatMessage
    {
        return ChatMessage::query()->create([
            'chat_thread_id' => $thread->id,
            'sender_id' => $senderId,
            'body' => $payload['body'],
            'metadata' => $payload['metadata'] ?? null,
        ]);
    }

    public function markRead(ChatMessage $message, int $userId): ChatMessageRead
    {
        return ChatMessageRead::query()->firstOrCreate([
            'chat_message_id' => $message->id,
            'user_id' => $userId,
        ], [
            'read_at' => now(),
        ]);
    }
}
