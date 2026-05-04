<?php

namespace Modules\AssesmentModule\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\AssesmentModule\Http\Requests\QuestionRequest\SuperAdmin\StoreBulkQuestionsRequest;
use Modules\AssesmentModule\Http\Requests\QuestionRequest\SuperAdmin\StoreQuestionRequest;
use Modules\AssesmentModule\Http\Requests\QuestionRequest\SuperAdmin\UpdateQuestionRequest;
use Modules\AssesmentModule\Services\V1\QuestionService;
use Modules\AssesmentModule\Transformers\QuestionResource;
use Throwable;

/**
 * SuperAdmin QuestionController
 *
 * Restricted to super-admin role only.
 * Supports:
 *  - Full CRUD for questions
 *  - Bulk creation of multiple questions in one request
 *  - Bulk delete
 *  - All responses include options + correct answers
 *
 * @package Modules\AssesmentModule\Http\Controllers\Api\V1\SuperAdmin
 */
class QuestionController extends Controller
{
    public function __construct(private QuestionService $questionService)
    {
        $this->middleware('role_or_permission:super-admin,api');
    }

    /**
     * List all questions (paginated) with options & correct answers.
     *
     * GET /v1/super-admin/questions
     * Query params: quiz_id, type, is_required, order_index, per_page
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['quiz_id', 'type', 'is_required', 'order_index']);
            $perPage = (int) $request->integer('per_page', 15);

            $data = $this->questionService->indexWithOptions($filters, $perPage);

            $collection = QuestionResource::collection($data->getCollection());
            $data->setCollection(collect($collection->resolve()));

            return self::paginated($data, 'Questions fetched successfully.', 200);
        } catch (Throwable $e) {
            return self::error('Failed to fetch questions.', 500, $e->getMessage());
        }
    }

    /**
     * Create a single question (with its options in the same request).
     *
     * POST /v1/super-admin/questions
     */
    public function store(StoreQuestionRequest $request): JsonResponse
    {
        try {
            $question = $this->questionService->storeWithOptions($request->validated());

            return self::success(
                new QuestionResource($question->load('options')),
                'Question created successfully.',
                201
            );
        } catch (Throwable $e) {
            return self::error('Failed to create question.', 500, $e->getMessage());
        }
    }

    /**
     * Create multiple questions (each with options) in one request.
     *
     * POST /v1/super-admin/questions/bulk
     * Body: { "questions": [ {...}, {...} ] }
     */
    public function storeBulk(StoreBulkQuestionsRequest $request): JsonResponse
    {
        try {
            $questions = $this->questionService->storeBulkWithOptions(
                $request->validated()['questions']
            );

            return self::success(
                QuestionResource::collection($questions),
                'Questions created successfully.',
                201
            );
        } catch (Throwable $e) {
            return self::error('Failed to create questions.', 500, $e->getMessage());
        }
    }

    /**
     * Show a single question with its options & correct answers.
     *
     * GET /v1/super-admin/questions/{question}
     */
    public function show(int $question): JsonResponse
    {
        try {
            $q = $this->questionService->showWithOptions($question);

            return self::success(
                new QuestionResource($q),
                'Question fetched successfully.',
                200
            );
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    /**
     * Update a question and optionally sync its options.
     *
     * PUT/PATCH /v1/super-admin/questions/{question}
     */
    public function update(UpdateQuestionRequest $request, int $question): JsonResponse
    {
        try {
            $q = $this->questionService->updateWithOptions($question, $request->validated());

            return self::success(
                new QuestionResource($q->load('options')),
                'Question updated successfully.',
                200
            );
        } catch (Throwable $e) {
            return self::error('Failed to update question.', 500, $e->getMessage());
        }
    }

    /**
     * Soft-delete a single question.
     *
     * DELETE /v1/super-admin/questions/{question}
     */
    public function destroy(int $question): JsonResponse
    {
        try {
            $this->questionService->destroy($question);

            return self::success(null, 'Question deleted successfully.', 200);
        } catch (Throwable $e) {
            return self::error('Failed to delete question.', 500, $e->getMessage());
        }
    }

    /**
     * Bulk soft-delete multiple questions.
     *
     * DELETE /v1/super-admin/questions/bulk
     * Body: { "ids": [1, 2, 3] }
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'ids'   => ['required', 'array', 'min:1'],
                'ids.*' => ['required', 'integer', 'exists:questions,id'],
            ]);

            $count = $this->questionService->bulkDestroy($validated['ids']);

            return self::success(
                ['deleted_count' => $count],
                "Deleted {$count} question(s) successfully.",
                200
            );
        } catch (Throwable $e) {
            return self::error('Failed to delete questions.', 500, $e->getMessage());
        }
    }
}
