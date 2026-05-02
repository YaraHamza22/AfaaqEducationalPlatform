<?php

namespace Modules\AssesmentModule\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\AssesmentModule\Http\Requests\QuestionOptionRequest\StoreQuestionOptionRequest;
use Modules\AssesmentModule\Http\Requests\QuestionOptionRequest\UpdateQuestionOptionRequest;
use Modules\AssesmentModule\Models\QuestionOption;
use Modules\AssesmentModule\Services\V1\QuestionOptionService;
use Modules\AssesmentModule\Transformers\QuestionOptionResource;
use Throwable;

/**
 * QuestionOptionController handles CRUD operations for managing question options.
 * Provides endpoints for listing, creating, showing, updating, deleting question options.
 *
 * @package Modules\AssesmentModule\Http\Controllers\Api\V1
 */
class QuestionOptionController extends Controller
{
    /**
     * QuestionOptionController constructor.
     *
     * @param QuestionOptionService $questionOptionService
     */
    public function __construct(private QuestionOptionService $questionOptionService)
    {
         $this->middleware('permission:list-options')->only('index');
        $this->middleware('permission:show-option')->only('show');
        $this->middleware('permission:create-option')->only('store');
        $this->middleware('permission:update-option')->only('update');
        $this->middleware('permission:delete-option')->only('destroy');
    }

    /**
     * Display a listing of question options.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only([
                'question_id',
                'is_correct',
            ]);

            $perPage = (int) $request->integer('per_page', 15);

            // Fetching question options from the service
            $res = $this->questionOptionService->index($filters, $perPage);

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 400, $res);
            }

            $data = $res['data'] ?? null;

            if ($data instanceof LengthAwarePaginator) {
                $items = collect($data->items())->map(
                    fn ($opt) => (new QuestionOptionResource($opt))->resolve()
                )->all();

                $data->setCollection(collect($items));

                return self::paginated($data, $res['message'] ?? 'Operation successful', $res['code'] ?? 200);
            }
            if ($data !== null) {
                return self::success(
                    QuestionOptionResource::collection($data),
                    $res['message'] ?? 'Operation successful',
                    $res['code'] ?? 200
                );
            }

            return self::success(null, $res['message'] ?? 'Operation successful', $res['code'] ?? 200);
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created question option.
     *
     * @param StoreQuestionOptionRequest $request
     * @return \Illuminate\Http\Response
     */
  public function store(StoreQuestionOptionRequest $request)
   {
    try {
        $option = $this->questionOptionService->store($request->validated());

        return self::success(
            new QuestionOptionResource($option),
            'Question option created successfully',
            201
        );
    } catch (Throwable $e) {
        return self::error($e->getMessage(), 500);
    }
   }

    /**
     * Display the specified question option.
     *
     * @param QuestionOption $questionOption
     * @return \Illuminate\Http\Response
     */
    public function show(QuestionOption $questionOption)
    {
        try {
            $res = $this->questionOptionService->show($questionOption->id);

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 404, $res);
            }

            $opt = $res['data'] ?? $questionOption;

            return self::success(
                $opt ? new QuestionOptionResource($opt) : null,
                $res['message'] ?? 'Operation successful',
                $res['code'] ?? 200
            );
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified question option in the database.
     *
     * @param UpdateQuestionOptionRequest $request
     * @param QuestionOption $questionOption
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateQuestionOptionRequest $request, QuestionOption $questionOption)
    {
        try {
            $res = $this->questionOptionService->update($questionOption->id, $request->validated());

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 400, $res);
            }
            $opt = $res['data'] ?? null;

            return self::success(
                $opt ? new QuestionOptionResource($opt) : null,
                $res['message'] ?? 'Operation successful',
                $res['code'] ?? 200
            );
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified question option from the database.
     *
     * @param QuestionOption $questionOption
     * @return \Illuminate\Http\Response
     */
    public function destroy(QuestionOption $questionOption)
    {
        try {
            $res = $this->questionOptionService->destroy($questionOption->id);

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 400, $res);
            }

            return self::success(null, $res['message'] ?? 'Operation successful', $res['code'] ?? 200);
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }
}
