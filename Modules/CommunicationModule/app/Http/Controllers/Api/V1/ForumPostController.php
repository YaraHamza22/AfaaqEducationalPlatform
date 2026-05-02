<?php

namespace Modules\CommunicationModule\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\CommunicationModule\Http\Requests\Forum\ReactForumPostRequest;
use Modules\CommunicationModule\Http\Requests\Forum\ReportForumPostRequest;
use Modules\CommunicationModule\Http\Requests\Forum\ReviewForumPostReportRequest;
use Modules\CommunicationModule\Http\Requests\Forum\StoreForumPostRequest;
use Modules\CommunicationModule\Models\ForumPost;
use Modules\CommunicationModule\Models\ForumPostReport;
use Modules\CommunicationModule\Models\ForumThread;
use Modules\CommunicationModule\Services\V1\ForumService;

class ForumPostController extends Controller
{
    public function __construct(private ForumService $forumService)
    {
        $this->middleware('permission:list-posts_forum')->only(['index']);
        $this->middleware('permission:create-post_forum')->only(['store', 'react', 'report']);
        $this->middleware('permission:update-post_forum')->only(['update', 'reviewReport']);
        $this->middleware('permission:delete-post_forum')->only(['destroy']);
    }

    public function index(ForumThread $forumThread)
    {
        $posts = ForumPost::query()->where('forum_thread_id', $forumThread->id)->latest()->paginate(20);
        return self::paginated($posts, 'Forum posts fetched successfully.');
    }

    public function store(StoreForumPostRequest $request, ForumThread $forumThread)
    {
        if ($forumThread->is_locked) {
            return self::error('Forum thread is locked.', 422);
        }

        $post = $this->forumService->createPost($forumThread, $request->validated(), Auth::id());
        return self::success($post, 'Forum post created successfully.', 201);
    }

    public function update(StoreForumPostRequest $request, ForumPost $forumPost)
    {
        $this->authorize('update', $forumPost);
        $forumPost->update($request->validated());
        return self::success($forumPost->fresh(), 'Forum post updated successfully.');
    }

    public function destroy(ForumPost $forumPost)
    {
        $this->authorize('delete', $forumPost);
        $forumPost->delete();
        return self::success(null, 'Forum post deleted successfully.');
    }

    public function react(ReactForumPostRequest $request, ForumPost $forumPost)
    {
        $reaction = $this->forumService->react($forumPost, $request->validated(), Auth::id());
        return self::success($reaction, 'Forum reaction added successfully.', 201);
    }

    public function report(ReportForumPostRequest $request, ForumPost $forumPost)
    {
        if ((int) $forumPost->author_id === (int) Auth::id()) {
            return self::error('You cannot report your own post.', 422);
        }

        $report = $this->forumService->report($forumPost, $request->validated(), Auth::id());
        return self::success($report, 'Forum post reported successfully.', 201);
    }

    public function reviewReport(ReviewForumPostReportRequest $request, ForumPostReport $forumPostReport)
    {
        $forumPostReport->update([
            'status' => $request->validated()['status'],
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);
        return self::success($forumPostReport->fresh(), 'Forum report reviewed successfully.');
    }
}
