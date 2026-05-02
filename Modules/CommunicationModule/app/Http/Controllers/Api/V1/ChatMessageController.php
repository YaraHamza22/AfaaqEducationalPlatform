<?php

namespace Modules\CommunicationModule\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\CommunicationModule\Http\Requests\Chat\StoreChatMessageRequest;
use Modules\CommunicationModule\Models\ChatMessage;
use Modules\CommunicationModule\Models\ChatThread;
use Modules\CommunicationModule\Services\V1\ChatService;

class ChatMessageController extends Controller
{
    public function __construct(private ChatService $chatService)
    {
        $this->middleware('permission:list-chat_messages')->only(['index']);
        $this->middleware('permission:create-chat_message')->only(['store', 'markRead']);
        $this->middleware('permission:delete-chat_message')->only(['destroy']);
    }

    public function index(ChatThread $chatThread)
    {
        $this->authorize('view', $chatThread);
        $messages = ChatMessage::query()
            ->where('chat_thread_id', $chatThread->id)
            ->latest()
            ->paginate(30);
        return self::paginated($messages, 'Chat messages fetched successfully.');
    }

    public function store(StoreChatMessageRequest $request, ChatThread $chatThread)
    {
        $this->authorize('view', $chatThread);
        $message = $this->chatService->sendMessage($chatThread, Auth::id(), $request->validated());
        return self::success($message, 'Chat message sent successfully.', 201);
    }

    public function markRead(ChatMessage $chatMessage)
    {
        $this->authorize('interact', $chatMessage);
        $read = $this->chatService->markRead($chatMessage, Auth::id());
        return self::success($read, 'Message marked as read.');
    }

    public function destroy(ChatMessage $chatMessage)
    {
        $this->authorize('delete', $chatMessage);
        $chatMessage->delete();
        return self::success(null, 'Chat message deleted successfully.');
    }
}
