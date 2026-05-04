<?php

namespace Modules\AssesmentModule\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Modules\AssesmentModule\Http\Requests\QuestionRequest\StoreQuestionRequest;
use Modules\AssesmentModule\Http\Requests\QuestionRequest\UpdateQuestionRequest;
use Modules\AssesmentModule\Services\V1\QuestionService;
use Modules\AssesmentModule\Transformers\QuestionResource;
use Throwable;

/**
 * QuestionController handles CRUD operations for managing questions in the assessment module.
 * Provides endpoints for listing, creating, updating, and deleting questions.
 *
 * @package Modules\AssesmentModule\Http\Controllers\Api\V1
 */
class QuestionController extends \App\Http\Controllers\Controller
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

            // Wrap in QuestionResource so that eager-loaded 'options' are serialised
            $questions->setCollection(
                $questions->getCollection()->map(
                    fn ($q) => (new QuestionResource($q))->resolve()
                )
            );

            return static::paginated($questions, 'Operation successful', 200);
        } catch (Throwable $e) {
            return static::error($e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created question with its options.
     *
     * @param StoreQuestionRequest $request The validated request data.
     * @throws Throwable If an unexpected error occurs during the request.
     */
    public function store(StoreQuestionRequest $request)
    {
        try {
            $data = $request->validated();

            $question = $this->questionService->storeWithOptions($data);

            return static::success(new QuestionResource($question), 'Question created successfully', 201);
        } catch (Throwable $e) {
            return static::error($e->getMessage(), 500);
        }
    }

    /**
     * Store multiple questions with their options in bulk.
     *
     * @param Request $request The request containing an array of questions.
     * @return \Illuminate\Http\JsonResponse JSON response with created questions or error.
     */
    public function storeBulk(Request $request)
    {
        try {
            $data = $request->validate([
                'questions' => 'required|array|min:1',
                // Each item should follow StoreQuestionRequest rules essentially
                'questions.*.quiz_id' => 'required|exists:quizzes,id',
                'questions.*.type' => 'required|string',
                'questions.*.question_text' => 'required|array',
                'questions.*.point' => 'required|integer',
                'questions.*.order_index' => 'required|integer',
                'questions.*.is_required' => 'required|boolean',
                'questions.*.options' => 'sometimes|array',
            ]);

            $questions = $this->questionService->storeBulkWithOptions($data['questions']);

            return static::success(QuestionResource::collection($questions), 'Bulk questions created successfully', 201);
        } catch (Throwable $e) {
            return static::error($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified question with options.
     *
     * @param int|string $id The ID of the question to retrieve.
     * @return \Illuminate\Http\Response A JSON response containing the question.
     *
     * @throws Throwable If an unexpected error occurs during the request.
     */
    public function show($id)
    {
        try {
            $question = $this->questionService->showWithOptions((int) $id);

            return static::success(new QuestionResource($question), 'Operation successful', 200);
        } catch (Throwable $e) {
            return static::error($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified question and its options.
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

            $question = $this->questionService->updateWithOptions((int) $id, $data);

            return static::success(new QuestionResource($question), 'Question updated successfully', 200);
        } catch (Throwable $e) {
            return static::error($e->getMessage(), 500);
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

            return static::success(null, 'Question deleted successfully', 200);
        } catch (Throwable $e) {
            return static::error($e->getMessage(), 500);
        }
    }

    /**
     * Bulk delete questions.
     *
     * @param Request $request The request containing an array of question IDs.
     * @return \Illuminate\Http\JsonResponse JSON response indicating success or failure.
     */
    public function bulkDestroy(Request $request)
    {
        try {
            $data = $request->validate([
                'ids' => 'required|array|min:1',
                'ids.*' => 'exists:questions,id',
            ]);

            $this->questionService->bulkDestroy($data['ids']);

            return static::success(null, 'Questions deleted successfully', 200);
        } catch (Throwable $e) {
            return static::error($e->getMessage(), 500);
        }
    }
}
