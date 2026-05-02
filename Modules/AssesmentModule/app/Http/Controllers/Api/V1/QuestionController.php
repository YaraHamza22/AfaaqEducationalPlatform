<?php

namespace Modules\AssesmentModule\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AssesmentModule\Http\Requests\QuestionRequest\StoreQuestionRequest;
use Modules\AssesmentModule\Http\Requests\QuestionRequest\UpdateQuestionRequest;
use Modules\AssesmentModule\Services\V1\QuestionService;
use Throwable;

/**
 * QuestionController handles CRUD operations for managing questions in the assessment module.
 * Provides endpoints for listing, creating, updating, and deleting questions.
 *
 * @package Modules\AssesmentModule\Http\Controllers\Api\V1
 */
class QuestionController extends Controller
{
    private $questionService;

    /**
     * QuestionController constructor.
     *
     * @param QuestionService $questionService
     */
    public function __construct(QuestionService $questionService)
    {
        $this->questionService = $questionService;
        $this->middleware('permission:list-questions')->only('index');
        $this->middleware('permission:show-question')->only('show');
        $this->middleware('permission:create-question')->only('store');
        $this->middleware('permission:update-question')->only('update');
        $this->middleware('permission:delete-question')->only('destroy');
    }

    /**
     * List all questions with pagination.
     *
     * @param Request $request The request containing filtering and pagination parameters.
     * @return \Illuminate\Http\JsonResponse JSON response with paginated data or error.
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only([
                'quiz_id', 'type', 'is_required', 'order_index',
            ]);

            $perPage = (int) $request->integer('per_page', 15);
            $questions = $this->questionService->index($filters, $perPage);

            return self::paginated($questions, 'Operation successful', 200);
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created question.
     *
     * @param StoreQuestionRequest $request The validated request data.
     * @throws Throwable If an unexpected error occurs during the request.
     */
    public function store(StoreQuestionRequest $request)
    {
        try {
            $data = $request->validated();

            $question = $this->questionService->store($data);

            return self::success($question, 'Question created successfully', 201);
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified question.
     *
     * @param int|string $id The ID of the question to retrieve.
     * @return \Illuminate\Http\Response A JSON response containing the question.
     *
     * @throws Throwable If an unexpected error occurs during the request.
     */
    public function show($id)
    {
        try {
            $question = $this->questionService->show((int) $id);

            return self::success($question, 'Operation successful', 200);
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified question.
     *
     * @param UpdateQuestionRequest $request The validated request data.
     * @param int|string $id The ID of the question to update.
     * @return \Illuminate\Http\Response A JSON response indicating the success or failure of the operation.
     *
     * @throws Throwable If an unexpected error occurs during the request.
     */
    public function update(UpdateQuestionRequest $request, $id)
    {
        try {
            $data = $request->validated();

            $question = $this->questionService->update((int) $id, $data);

            return self::success($question, 'Question updated successfully', 200);
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified question from storage.
     *
     * @param int|string $id The ID of the question to delete.
     * @return \Illuminate\Http\Response A JSON response indicating the success or failure of the operation.
     *
     * @throws Throwable If an unexpected error occurs during the request.
     */
    public function destroy($id)
    {
        try {
            $this->questionService->destroy((int) $id);

            return self::success(null, 'Question deleted successfully', 200);
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }
}
