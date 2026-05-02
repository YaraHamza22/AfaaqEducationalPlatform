<?php

namespace Modules\CommunicationModule\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\CommunicationModule\Http\Requests\Forum\StoreForumThreadRequest;
use Modules\CommunicationModule\Http\Requests\Forum\UpdateForumThreadRequest;
use Modules\CommunicationModule\Models\ForumThread;
use Modules\CommunicationModule\Services\V1\ForumService;

class ForumThreadController extends Controller
{
    public function __construct(private ForumService $forumService)
    {
        $this->middleware('permission:list-threads_forum')->only(['index', 'show']);
        $this->middleware('permission:create-thread_forum')->only(['store']);
        $this->middleware('permission:update-thread_forum')->only(['update', 'pin', 'lock']);
        $this->middleware('permission:delete-thread_forum')->only(['destroy']);
    }

    public function index()
    {
        $threads = ForumThread::query()->latest()->paginate(15);
        return self::paginated($threads, 'Forum threads fetched successfully.');
    }

    public function store(StoreForumThreadRequest $request)
    {
        $thread = $this->forumService->createThread($request->validated(), Auth::id());
        return self::success($thread, 'Forum thread created successfully.', 201);
    }

    public function show(ForumThread $forumThread)
    {
        return self::success($forumThread, 'Forum thread fetched successfully.');
    }

    public function update(UpdateForumThreadRequest $request, ForumThread $forumThread)
    {
        $this->authorize('update', $forumThread);
        $forumThread->update($request->validated());
        return self::success($forumThread->fresh(), 'Forum thread updated successfully.');
    }

    public function destroy(ForumThread $forumThread)
    {
        $this->authorize('delete', $forumThread);
        $forumThread->delete();
        return self::success(null, 'Forum thread deleted successfully.');
    }

    public function pin(ForumThread $forumThread)
    {
        $this->authorize('update', $forumThread);
        $forumThread->update(['is_pinned' => ! $forumThread->is_pinned]);
        return self::success($forumThread->fresh(), 'Forum thread pin status updated.');
    }

    public function lock(ForumThread $forumThread)
    {
        $this->authorize('update', $forumThread);
        $forumThread->update(['is_locked' => ! $forumThread->is_locked]);
        return self::success($forumThread->fresh(), 'Forum thread lock status updated.');
    }
}
