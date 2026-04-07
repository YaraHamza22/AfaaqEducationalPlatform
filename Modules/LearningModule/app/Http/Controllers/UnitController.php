<?php

namespace Modules\LearningModule\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\CachesQueries;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Modules\LearningModule\Http\Requests\Unit\FilterUnitsRequest;
use Modules\LearningModule\Http\Requests\Unit\MoveUnitRequest;
use Modules\LearningModule\Http\Requests\Unit\ReorderUnitsRequest;
use Modules\LearningModule\Http\Requests\Unit\StoreUnitRequest;
use Modules\LearningModule\Http\Requests\Unit\UpdateUnitRequest;
use Modules\LearningModule\Http\Resources\UnitResource;
use Modules\LearningModule\Models\Course;
use Modules\LearningModule\Models\Unit;
use Modules\LearningModule\Services\UnitService;

/**
 * Controller for managing units.
 * Handles HTTP requests and delegates business logic to UnitService.
 * Follows SOLID principles: Single Responsibility, Dependency Inversion.
 */
class UnitController extends Controller
{
    use CachesQueries;
    /**
     * Unit service instance.
     *
     * @var UnitService
     */
    protected UnitService $unitService;

    /**
     * Create a new controller instance.
     *
     * @param UnitService $unitService
     */
    public function __construct(UnitService $unitService)
    {
        $this->unitService = $unitService;
         $this->middleware('permission:list-units')->only('index');
        $this->middleware('permission:show-unit')->only('show');
        $this->middleware('permission:create-unit')->only('store');
        $this->middleware('permission:update-unit')->only('update');
        $this->middleware('permission:delete-unit')->only('destroy');
    }

    /**
     * Display a listing of units.
     *
     * @param FilterUnitsRequest $request
     * @return JsonResponse
     */
    public function index(FilterUnitsRequest $request): JsonResponse
    {
        try {
            $cacheKey = 'units.index.' . md5($request->getQueryString());

            $units = $this->remember($cacheKey, 1800, function () use ($request) {
                $query = Unit::query();
                return $query
                    ->filterByRequest($request)
                    ->withRelations()
                    ->ordered()
                    ->paginateFromRequest($request)
                    ->through(fn($unit) => new UnitResource($unit));
            }, ['units']);

            return self::paginated($units, 'Units retrieved successfully.');
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving units', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Unable to retrieve units at this time.', 500);
        }
    }

    /**
     * Store a newly created unit.
     *
     * @param StoreUnitRequest $request
     * @return JsonResponse
     */
    public function store(StoreUnitRequest $request): JsonResponse
    {
        try {
            $course = Course::find($request->input('course_id'));

            if (!$course) {
                throw new Exception('Course not found.', 404);
            }

            $data = $request->validated();
            unset($data['course_id']); // Remove course_id as service sets it from course object
            $unit = $this->unitService->create($course, $data);

            if (!$unit) {
                throw new Exception('Failed to create unit. Please check your input and try again.', 422);
            }

            $unit->load(['course']);

            return self::success(
                new UnitResource($unit),
                'Unit created successfully.',
                201
            );
        } catch (Exception $e) {
            Log::error('Unexpected error creating unit', [
                'course_id' => $request->input('course_id'),
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('An error occurred while creating the unit.', 500);
        }
    }

    /**
     * Display the specified unit.
     *
     * @param Unit $unit
     * @return JsonResponse
     */
    public function show(Unit $unit): JsonResponse
    {
        try {
            $cacheKey = "unit.{$unit->unit_id}";

            $unitData = $this->remember($cacheKey, 1800, function () use ($unit) {
                $unit->load(['course', 'lessons']);
                return new UnitResource($unit);
            }, ['units', "unit.{$unit->unit_id}"]);

            return self::success(
                $unitData,
                'Unit retrieved successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving unit', [
                'unit_id' => $unit->unit_id ?? null,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Unable to retrieve unit details.', 500);
        }
    }

    /**
     * Update the specified unit.
     *
     * @param UpdateUnitRequest $request
     * @param Unit $unit
     * @return JsonResponse
     */
    public function update(UpdateUnitRequest $request, Unit $unit): JsonResponse
    {
        try {
            $updatedUnit = $this->unitService->update($unit, $request->validated());

            if (!$updatedUnit) {
                throw new Exception('Failed to update unit. Please check your input and try again.', 422);
            }

            $updatedUnit->load(['course']);

            return self::success(
                new UnitResource($updatedUnit),
                'Unit updated successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error updating unit', [
                'unit_id' => $unit->unit_id ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('An error occurred while updating the unit.', 500);
        }
    }

    /**
     * Remove the specified unit.
     *
     * @param Unit $unit
     * @return JsonResponse
     */
    public function destroy(Unit $unit): JsonResponse
    {
        try {
            $deleted = $this->unitService->delete($unit);

            if (!$deleted) {
                throw new Exception('Cannot delete unit. It may have lessons associated with it.', 422);
            }

            return self::success(null, 'Unit deleted successfully.');
        } catch (Exception $e) {
            Log::error('Unexpected error deleting unit', [
                'unit_id' => $unit->unit_id ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('An error occurred while deleting the unit.', 500);
        }
    }

    /**
     * Get units by course.
     *
     * @param FilterUnitsRequest $request
     * @param Course $course
     * @return JsonResponse
     */
    public function byCourse(FilterUnitsRequest $request, Course $course): JsonResponse
    {
        try {
            $cacheKey = "units.course.{$course->course_id}." . md5($request->getQueryString());

            $units = $this->remember($cacheKey, 1800, function () use ($request, $course) {
                return Unit::query()
                    ->byCourse($course->course_id)
                    ->filterByRequest($request)
                    ->withRelations()
                    ->ordered()
                    ->paginateFromRequest($request)
                    ->through(fn($unit) => new UnitResource($unit));
            }, ['units', "course.{$course->course_id}"]);

            return self::paginated($units, 'Course units retrieved successfully.');
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving course units', [
                'course_id' => $course->course_id ?? null,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Unable to retrieve units for this course.', 500);
        }
    }

    /**
     * Reorder units within a course.
     *
     * @param ReorderUnitsRequest $request
     * @param Course $course
     * @return JsonResponse
     */
    public function reorder(ReorderUnitsRequest $request, Course $course): JsonResponse
    {
        try {
            $reordered = $this->unitService->reorder($course, $request->input('unit_orders'));

            if (!$reordered) {
                throw new Exception('Failed to reorder units. Please ensure all orders are unique.', 422);
            }

            return self::success(null, 'Units reordered successfully.');
        } catch (Exception $e) {
            Log::error('Unexpected error reordering units', [
                'course_id' => $course->course_id ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('An error occurred while reordering units.', 500);
        }
    }

    /**
     * Move unit to a specific position.
     *
     * @param MoveUnitRequest $request
     * @param Unit $unit
     * @return JsonResponse
     */
    public function moveToPosition(MoveUnitRequest $request, Unit $unit): JsonResponse
    {
        try {
            $updatedUnit = $this->unitService->moveToPosition($unit, $request->input('unit_order'));

            if (!$updatedUnit) {
                throw new Exception('Failed to move unit to position. Please check the order value and try again.', 422);
            }

            $updatedUnit->load(['course']);

            return self::success(
                new UnitResource($updatedUnit),
                'Unit moved to position successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error moving unit', [
                'unit_id' => $unit->unit_id ?? null,
                'new_order' => $request->input('unit_order'),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('An error occurred while moving the unit.', 500);
        }
    }

    /**
     * Get unit duration.
     *
     * @param Unit $unit
     * @return JsonResponse
     */
    public function getDuration(Unit $unit): JsonResponse
    {
        try {
            $duration = $this->unitService->getDuration($unit);

            return self::success(
                [
                    'unit_id' => $unit->unit_id,
                    'duration_minutes' => $duration,
                    'actual_duration_minutes' => $unit->actual_duration_minutes,
                ],
                'Unit duration retrieved successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving unit duration', [
                'unit_id' => $unit->unit_id ?? null,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Unable to retrieve unit duration.', 500);
        }
    }

    /**
     * Check if unit can be deleted.
     *
     * @param Unit $unit
     * @return JsonResponse
     */
    public function canBeDeleted(Unit $unit): JsonResponse
    {
        try {
            $canBeDeleted = $this->unitService->canBeDeleted($unit);
            $lessonsCount = $unit->lessons()->count();

            return self::success(
                [
                    'unit_id' => $unit->unit_id,
                    'can_be_deleted' => $canBeDeleted,
                    'lessons_count' => $lessonsCount,
                    'reason' => $canBeDeleted ? null : "Unit has {$lessonsCount} lesson(s).",
                ],
                'Unit deletion check completed.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error checking unit deletion', [
                'unit_id' => $unit->unit_id ?? null,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Unable to check if unit can be deleted.', 500);
        }
    }

    /**
     * Get unit count for a course.
     *
     * @param Course $course
     * @return JsonResponse
     */
    public function getUnitCount(Course $course): JsonResponse
    {
        try {
            $count = $this->unitService->getUnitCount($course);

            return self::success(
                [
                    'course_id' => $course->course_id,
                    'units_count' => $count,
                ],
                'Unit count retrieved successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving unit count', [
                'course_id' => $course->course_id ?? null,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Unable to retrieve unit count.', 500);
        }
    }
}
