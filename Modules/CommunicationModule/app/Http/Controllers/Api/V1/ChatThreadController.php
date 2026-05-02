<?php

namespace Modules\CommunicationModule\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\CommunicationModule\Http\Requests\Chat\AddChatParticipantRequest;
use Modules\CommunicationModule\Http\Requests\Chat\StoreChatThreadRequest;
use Modules\CommunicationModule\Models\ChatMessage;
use Modules\CommunicationModule\Models\ChatParticipant;
use Modules\CommunicationModule\Models\ChatThread;
use Modules\CommunicationModule\Services\V1\ChatService;

class ChatThreadController extends Controller
{
    public function __construct(private ChatService $chatService)
    {
        $this->middleware('permission:list-chat_messages')->only(['index', 'show', 'participants', 'unreadCount']);
        $this->middleware('permission:create-chat_message')->only(['store', 'addParticipant']);
        $this->middleware('permission:update-chat_message')->only(['archive']);
        $this->middleware('permission:delete-chat_message')->only(['removeParticipant']);
    }

    public function index()
    {
        $userId = Auth::id();
        $threadIds = ChatParticipant::query()->where('user_id', $userId)->pluck('chat_thread_id');
        $threads = ChatThread::query()->whereIn('id', $threadIds)->latest()->paginate(15);
        return self::paginated($threads, 'Chat threads fetched successfully.');
    }

    public function store(StoreChatThreadRequest $request)
    {
        $thread = $this->chatService->createThread($request->validated(), Auth::id());
        return self::success($thread, 'Chat thread created successfully.', 201);
    }

    public function show(ChatThread $chatThread)
    {
        $this->authorize('view', $chatThread);
        return self::success($chatThread, 'Chat thread fetched successfully.');
    }

    public function archive(ChatThread $chatThread)
    {
        $this->authorize('manage', $chatThread);
        $chatThread->update(['is_archived' => true, 'archived_at' => now()]);
        return self::success($chatThread->fresh(), 'Chat thread archived successfully.');
    }

    public function participants(ChatThread $chatThread)
    {
        $this->authorize('view', $chatThread);
        $participants = ChatParticipant::query()->where('chat_thread_id', $chatThread->id)->get();
        return self::success($participants, 'Participants fetched successfully.');
    }

    public function addParticipant(AddChatParticipantRequest $request, ChatThread $chatThread)
    {
        $this->authorize('manage', $chatThread);
        $participant = $this->chatService->addParticipant($chatThread, $request->validated());
        return self::success($participant, 'Participant added successfully.', 201);
    }

    public function removeParticipant(ChatThread $chatThread, int $userId)
    {
        $this->authorize('manage', $chatThread);
        ChatParticipant::query()
            ->where('chat_thread_id', $chatThread->id)
            ->where('user_id', $userId)
            ->delete();

        return self::success(null, 'Participant removed successfully.');
    }

    public function unreadCount()
    {
        $userId = Auth::id();
        $threadIds = ChatParticipant::query()->where('user_id', $userId)->pluck('chat_thread_id');

        $unread = ChatMessage::query()
            ->whereIn('chat_thread_id', $threadIds)
            ->whereDoesntHave('reads', fn ($q) => $q->where('user_id', $userId))
            ->count();

        return self::success(['unread_count' => $unread], 'Unread count fetched successfully.');
    }
}
