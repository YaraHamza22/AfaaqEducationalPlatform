<?php

namespace Modules\AssesmentModule\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AssesmentModule\Transformers\QuizResource;
use Modules\AssesmentModule\Models\Quiz;
use Modules\AssesmentModule\Services\V1\QuizService;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\AssesmentModule\Http\Requests\QuizRequest\StoreQuizRequest;
use Modules\AssesmentModule\Http\Requests\QuizRequest\UpdateQuizRequest;
use Throwable;

/**
 * QuizController handles the CRUD operations for managing quizzes.
 * Provides endpoints to list, create, show, update, delete, and publish/unpublish quizzes.
 *
 * @package Modules\AssesmentModule\Http\Controllers\Api\V1
 */
class QuizController extends Controller
{
    /**
     * QuizController constructor.
     *
     * @param QuizService $quizService
     */

    public function __construct(private QuizService $quizService)
    {
        $this->middleware('role_or_permission:super-admin|list-quiz,api')->only('index');
        $this->middleware('role_or_permission:super-admin|show-quiz,api')->only('show');
        $this->middleware('role_or_permission:super-admin|create-quiz,api')->only('store');
        $this->middleware('role_or_permission:super-admin|update-quiz,api')->only('update');
        $this->middleware('role_or_permission:super-admin|delete-quiz,api')->only('destroy');
        $this->middleware('role_or_permission:super-admin|publish-quiz,api')->only('publish');
        $this->middleware('role_or_permission:super-admin|unpublish-quiz,api')->only('unpublish');
    }

    /**
     * Display a listing of the quizzes.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only([
                'instructor_id', 'course_id', 'unit_id', 'lesson_id', 'type', 'status', 'available_now', 'order', 'quizable_type', 'quizable_id'
                ]);
            if ($request->filled('oreder') && ! isset($filters['order'])) {
                $filters['order'] = $request->input('oreder');
            }
            $perPage = (int) $request->integer('per_page', 15);
            $res = $this->quizService->index(filters: $filters, perPage: $perPage);

            return $this->respond($res, paginated: true, resource: QuizResource::class);
        } catch (Throwable $e) {
            return self::error('Failed to fetch quizzes.', 500, $e->getMessage());
        }
    }

    /**
     * Store a newly created quiz in the database.
     *
     * @param StoreQuizRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreQuizRequest $request)
    {
        try {
            $res = $this->quizService->store($request->validated());

            return $this->respond($res, resource: QuizResource::class);
        } catch (Throwable $e) {
            return self::error('Failed to create quiz.', 500, $e->getMessage());
        }
    }

    /**
     * Display the specified quiz.
     *
     * @param Quiz $quiz
     * @return \Illuminate\Http\Response
     */

    public function show(Quiz $quiz)
    {
        try {
            $res = $this->quizService->show($quiz->id);

            return $this->respond($res, resource: QuizResource::class);
        } catch (Throwable $e) {
            return self::error('Failed to fetch quiz.', 500, $e->getMessage());
        }
    }

    /**
     * Update the specified quiz in the database.
     *
     * @param UpdateQuizRequest $request
     * @param Quiz $quiz
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateQuizRequest $request, Quiz $quiz)
    {
        try {
            $res = $this->quizService->update($quiz, $request->validated());

            return $this->respond($res, resource: QuizResource::class);
        } catch (Throwable $e) {
            return self::error('Failed to update quiz.', 500, $e->getMessage());
        }
    }

    /**
     * Remove the specified quiz from the database.
     *
     * @param Quiz $quiz
     * @return \Illuminate\Http\Response
     */
    public function destroy(Quiz $quiz)
    {
        try {
            $res = $this->quizService->destroy($quiz);

            return $this->respond($res);
        } catch (Throwable $e) {
            return self::error('Failed to delete quiz.', 500, $e->getMessage());
        }
    }

    /**
     * Publish the specified quiz.
     *
     * @param Quiz $quiz
     * @return \Illuminate\Http\Response
     */
    public function publish(Quiz $quiz)
    {
        try {
            $res = $this->quizService->publish($quiz);

            return $this->respond($res, resource: QuizResource::class);
        } catch (Throwable $e) {
            return self::error('Failed to publish quiz.', 500, $e->getMessage());
        }
    }

    /**
     * Unpublish the specified quiz.
     *
     * @param Quiz $quiz
     * @return \Illuminate\Http\Response
     */
    public function unpublish(Quiz $quiz)
    {
        try {
            $res = $this->quizService->unpublish($quiz);

            return $this->respond($res, resource: QuizResource::class);
        } catch (Throwable $e) {
            return self::error('Failed to unpublish quiz.', 500, $e->getMessage());
        }
    }
    public function archive(Quiz $quiz)
    {
        try{
            $res = $this->quizService->archive($quiz);
            return $this->respond($res, resource: QuizResource::class);
        }
        catch(Throwable $e){
            return self::error('Failed to archive quiz', 500 , $e->getMessage());
        }
    }

    /**
     * Format and return the response.
     *
     * @param array $res
     * @param bool $paginated
     * @param string|null $resource
     * @return \Illuminate\Http\Response
     */
    private function respond(array $res, bool $paginated = false, ?string $resource = null)
    {
        $ok      = (bool)($res['success'] ?? false);
        $message = (string)($res['message'] ?? ($ok ? 'Operation successful' : 'Operation failed'));
        $code    = (int)($res['code'] ?? ($ok ? 200 : 400));
        $data    = $res['data'] ?? null;

        if (!$ok) {
            return self::error($message, $code, $res['error'] ?? null);
        }

        if ($paginated) {
            if ($data instanceof LengthAwarePaginator) {
                if ($resource) {
                    $collection = $resource::collection($data->getCollection());
                    $data->setCollection(collect($collection->resolve()));
                }
                return self::paginated($data, $message, $code);
            }
        }

        if ($resource && $data) {
            if ($data instanceof \Illuminate\Support\Collection) {
                $data = $resource::collection($data);
            } else {
                $data = new $resource($data);
            }
        }

        return self::success($data, $message, $code);
    }
}
