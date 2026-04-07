<?php

namespace Modules\LearningModule\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\CachesQueries;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Modules\LearningModule\Http\Requests\Lesson\FilterLessonsRequest;
use Modules\LearningModule\Http\Requests\Lesson\MoveLessonRequest;
use Modules\LearningModule\Http\Requests\Lesson\ReorderLessonsRequest;
use Modules\LearningModule\Http\Requests\Lesson\StoreLessonRequest;
use Modules\LearningModule\Http\Requests\Lesson\UpdateLessonRequest;
use Modules\LearningModule\Http\Resources\LessonResource;
use Modules\LearningModule\Models\Lesson;
use Modules\LearningModule\Models\Unit;
use Modules\LearningModule\Services\LessonService;

/**
 * Controller for managing lessons.
 * Handles HTTP requests and delegates business logic to LessonService.
 * Follows SOLID principles: Single Responsibility, Dependency Inversion.
 */
class LessonController extends Controller
{
    use CachesQueries;
    /**
     * Lesson service instance.
     *
     * @var LessonService
     */
    protected LessonService $lessonService;

    /**
     * Create a new controller instance.
     *
     * @param LessonService $lessonService
     */
    public function __construct(LessonService $lessonService)
    {
        $this->lessonService = $lessonService;
         $this->middleware('permission:list-lessons')->only('index');
        $this->middleware('permission:show-lesson')->only('show');
        $this->middleware('permission:create-lesson')->only('store');
        $this->middleware('permission:update-lesson')->only('update');
        $this->middleware('permission:delete-lesson')->only('destroy');
    }

    /**
     * Display a listing of lessons.
     *
     * @param FilterLessonsRequest $request
     * @return JsonResponse
     */
    public function index(FilterLessonsRequest $request): JsonResponse
    {
        try {
            $cacheKey = 'lessons.index.' . md5($request->getQueryString());

            $lessons = $this->remember($cacheKey, 1800, function () use ($request) {
                $query = Lesson::query();
                return $query
                    ->filterByRequest($request)
                    ->withRelations()
                    ->ordered()
                    ->paginateFromRequest($request)
                    ->through(fn($lesson) => new LessonResource($lesson));
            }, ['lessons']);

            return self::paginated($lessons, 'Lessons retrieved successfully.');
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving lessons', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Unable to retrieve lessons at this time.', 500);
        }
    }

    /**
     * Store a newly created lesson.
     *
     * @param StoreLessonRequest $request
     * @return JsonResponse
     */
    public function store(StoreLessonRequest $request): JsonResponse
    {
        try {
            $unit = Unit::find($request->input('unit_id'));

            if (!$unit) {
                throw new Exception('Unit not found.', 404);
            }

            $data = $request->validated();
            unset($data['unit_id']); // Remove unit_id as service sets it from unit object
            $lesson = $this->lessonService->create($unit, $data);

            if (!$lesson) {
                throw new Exception('Failed to create lesson. Please check your input and try again.', 422);
            }

            $lesson->load(['unit']);

            return self::success(
                new LessonResource($lesson),
                'Lesson created successfully.',
                201
            );
        } catch (Exception $e) {
            Log::error('Unexpected error creating lesson', [
                'unit_id' => $request->input('unit_id'),
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('An error occurred while creating the lesson.', 500);
        }
    }

    /**
     * Display the specified lesson.
     *
     * @param Lesson $lesson
     * @return JsonResponse
     */
    public function show(Lesson $lesson): JsonResponse
    {
        try {
            $cacheKey = "lesson.{$lesson->lesson_id}";

            $lessonData = $this->remember($cacheKey, 1800, function () use ($lesson) {
                $lesson->load(['unit']);
                return new LessonResource($lesson);
            }, ['lessons', "lesson.{$lesson->lesson_id}"]);

            return self::success(
                $lessonData,
                'Lesson retrieved successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving lesson', [
                'lesson_id' => $lesson->lesson_id ?? null,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Unable to retrieve lesson details.', 500);
        }
    }

    /**
     * Update the specified lesson.
     *
     * @param UpdateLessonRequest $request
     * @param Lesson $lesson
     * @return JsonResponse
     */
    public function update(UpdateLessonRequest $request, Lesson $lesson): JsonResponse
    {
        try {
            $updatedLesson = $this->lessonService->update($lesson, $request->validated());

            if (!$updatedLesson) {
                throw new Exception('Failed to update lesson. Please check your input and try again.', 422);
            }

            $updatedLesson->load(['unit']);

            return self::success(
                new LessonResource($updatedLesson),
                'Lesson updated successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error updating lesson', [
                'lesson_id' => $lesson->lesson_id ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('An error occurred while updating the lesson.', 500);
        }
    }

    /**
     * Remove the specified lesson.
     *
     * @param Lesson $lesson
     * @return JsonResponse
     */
    public function destroy(Lesson $lesson): JsonResponse
    {
        try {
            $deleted = $this->lessonService->delete($lesson);

            if (!$deleted) {
                throw new Exception('Failed to delete lesson.', 422);
            }

            return self::success(null, 'Lesson deleted successfully.');
        } catch (Exception $e) {
            Log::error('Unexpected error deleting lesson', [
                'lesson_id' => $lesson->lesson_id ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('An error occurred while deleting the lesson.', 500);
        }
    }

    /**
     * Get lessons by unit.
     *
     * @param FilterLessonsRequest $request
     * @param Unit $unit
     * @return JsonResponse
     */
    public function byUnit(FilterLessonsRequest $request, Unit $unit): JsonResponse
    {
        try {
            $cacheKey = "lessons.unit.{$unit->unit_id}." . md5($request->getQueryString());

            $lessons = $this->remember($cacheKey, 1800, function () use ($request, $unit) {
                return Lesson::query()
                    ->byUnit($unit->unit_id)
                    ->filterByRequest($request)
                    ->withRelations()
                    ->ordered()
                    ->paginateFromRequest($request)
                    ->through(fn($lesson) => new LessonResource($lesson));
            }, ['lessons', "unit.{$unit->unit_id}"]);

            return self::paginated($lessons, 'Unit lessons retrieved successfully.');
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving unit lessons', [
                'unit_id' => $unit->unit_id ?? null,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Unable to retrieve lessons for this unit.', 500);
        }
    }

    /**
     * Reorder lessons within a unit.
     *
     * @param ReorderLessonsRequest $request
     * @param Unit $unit
     * @return JsonResponse
     */
    public function reorder(ReorderLessonsRequest $request, Unit $unit): JsonResponse
    {
        try {
            $reordered = $this->lessonService->reorder($unit, $request->input('lesson_orders'));

            if (!$reordered) {
                throw new Exception('Failed to reorder lessons. Please ensure all orders are unique.', 422);
            }

            return self::success(null, 'Lessons reordered successfully.');
        } catch (Exception $e) {
            Log::error('Unexpected error reordering lessons', [
                'unit_id' => $unit->unit_id ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('An error occurred while reordering lessons.', 500);
        }
    }

    /**
     * Move lesson to a specific position.
     *
     * @param MoveLessonRequest $request
     * @param Lesson $lesson
     * @return JsonResponse
     */
    public function moveToPosition(MoveLessonRequest $request, Lesson $lesson): JsonResponse
    {
        try {
            $updatedLesson = $this->lessonService->moveToPosition($lesson, $request->input('lesson_order'));
            $updatedLesson->load(['unit']);

            return self::success(
                new LessonResource($updatedLesson),
                'Lesson moved to position successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error moving lesson', [
                'lesson_id' => $lesson->lesson_id ?? null,
                'new_order' => $request->input('lesson_order'),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('An error occurred while moving the lesson.', 500);
        }
    }

    /**
     * Get lesson duration.
     *
     * @param Lesson $lesson
     * @return JsonResponse
     */
    public function getDuration(Lesson $lesson): JsonResponse
    {
        try {
            $duration = $this->lessonService->getDuration($lesson);

            return self::success(
                [
                    'lesson_id' => $lesson->lesson_id,
                    'duration_minutes' => $duration,
                    'actual_duration_minutes' => $lesson->actual_duration_minutes,
                ],
                'Lesson duration retrieved successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving lesson duration', [
                'lesson_id' => $lesson->lesson_id ?? null,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Unable to retrieve lesson duration.', 500);
        }
    }

    /**
     * Get lesson count for a unit.
     *
     * @param Unit $unit
     * @return JsonResponse
     */
    public function getLessonCount(Unit $unit): JsonResponse
    {
        try {
            $count = $this->lessonService->getLessonCount($unit);

            return self::success(
                [
                    'unit_id' => $unit->unit_id,
                    'lessons_count' => $count,
                ],
                'Lesson count retrieved successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving lesson count', [
                'unit_id' => $unit->unit_id ?? null,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Unable to retrieve lesson count.', 500);
        }
    }
}
