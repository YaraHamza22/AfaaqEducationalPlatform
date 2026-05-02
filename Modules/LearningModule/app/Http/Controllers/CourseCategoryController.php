<?php

namespace Modules\LearningModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Modules\LearningModule\Http\Requests\CourseCategory\FilterCourseCategoriesRequest;
use Modules\LearningModule\Http\Requests\CourseCategory\StoreCourseCategoryRequest;
use Modules\LearningModule\Http\Requests\CourseCategory\UpdateCourseCategoryRequest;
use Modules\LearningModule\Http\Resources\CourseCategoryResource;
use Modules\LearningModule\Models\CourseCategory;
use Modules\LearningModule\Services\CourseCategoryService;

/**
 * Controller for managing course categories.
 * Handles HTTP requests and delegates business logic to CourseCategoryService.
 * Follows SOLID principles: Single Responsibility, Dependency Inversion.
 */
class CourseCategoryController extends Controller
{
    /**
     * Course category service instance.
     *
     * @var CourseCategoryService
     */
    protected CourseCategoryService $courseCategoryService;

    /**
     * Create a new controller instance.
     *
     * @param CourseCategoryService $courseCategoryService
     */
    public function __construct(CourseCategoryService $courseCategoryService)
    {
        $this->courseCategoryService = $courseCategoryService;
         $this->middleware('permission:list-categories')->only('index');
        $this->middleware('permission:show-category')->only('show');
        $this->middleware('permission:create-category')->only('store');
        $this->middleware('permission:update-category')->only('update');
        $this->middleware('permission:delete-category')->only('destroy');
    }

    /**
     * Display a listing of course categories.
     *
     * @param FilterCourseCategoriesRequest $request
     * @return JsonResponse
     */
    public function index(FilterCourseCategoriesRequest $request): JsonResponse
    {
        try {
            $query = CourseCategory::query();

            $courseCategories = $query
                ->filterByRequest($request)
                ->ordered()
                ->paginateFromRequest($request)
                ->through(fn($courseCategory) => new CourseCategoryResource($courseCategory));

            return self::paginated($courseCategories, 'Course categories retrieved successfully.');
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving course categories', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Unable to retrieve course categories at this time.', 500);
        }
    }

    /**
     * Store a newly created course category.
     *
     * @param StoreCourseCategoryRequest $request
     * @return JsonResponse
     */
    public function store(StoreCourseCategoryRequest $request): JsonResponse
    {
        try {
            $courseCategory = $this->courseCategoryService->create($request->validated());

            if (!$courseCategory) {
                throw new Exception('Failed to create course category. Please check your input and try again.', 422);
            }

            return self::success(
                new CourseCategoryResource($courseCategory),
                'Course category created successfully.',
                201
            );
        } catch (Exception $e) {
            Log::error('Unexpected error creating course category', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('An error occurred while creating the course category.', 500);
        }
    }

    /**
     * Display the specified course category.
     *
     * @param CourseCategory $courseCategory
     * @return JsonResponse
     */
    public function show(CourseCategory $courseCategory): JsonResponse
    {
        try {
            $courseCategory->load('courses');

            return self::success(
                new CourseCategoryResource($courseCategory),
                'Course category retrieved successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving course category', [
                'course_category_id' => $courseCategory->course_category_id ?? null,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Unable to retrieve course category details.', 500);
        }
    }

    /**
     * Update the specified course category.
     *
     * @param UpdateCourseCategoryRequest $request
     * @param CourseCategory $courseCategory
     * @return JsonResponse
     */
    public function update(UpdateCourseCategoryRequest $request, CourseCategory $courseCategory): JsonResponse
    {
        try {
            $updatedCourseCategory = $this->courseCategoryService->update($courseCategory, $request->validated());

            if (!$updatedCourseCategory) {
                throw new Exception('Failed to update course category. Please check your input and try again.', 422);
            }

            return self::success(
                new CourseCategoryResource($updatedCourseCategory),
                'Course category updated successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error updating course category', [
                'course_category_id' => $courseCategory->course_category_id ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('An error occurred while updating the course category.', 500);
        }
    }

    /**
     * Remove the specified course category.
     *
     * @param CourseCategory $courseCategory
     * @return JsonResponse
     */
    public function destroy(CourseCategory $courseCategory): JsonResponse
    {
        try {
            $deleted = $this->courseCategoryService->delete($courseCategory);

            if (!$deleted) {
                throw new Exception('Cannot delete course category. It may have courses associated with it.', 422);
            }

            return self::success(null, 'Course category deleted successfully.');
        } catch (Exception $e) {
            Log::error('Unexpected error deleting course category', [
                'course_category_id' => $courseCategory->course_category_id ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('An error occurred while deleting the course category.', 500);
        }
    }

    /**
     * Activate the specified course category.
     *
     * @param CourseCategory $courseCategory
     * @return JsonResponse
     */
    public function activate(CourseCategory $courseCategory): JsonResponse
    {
        try {
            $activatedCourseCategory = $this->courseCategoryService->activate($courseCategory);

            if (!$activatedCourseCategory) {
                throw new Exception('Failed to activate course category.', 422);
            }

            return self::success(
                new CourseCategoryResource($activatedCourseCategory),
                'Course category activated successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error activating course category', [
                'course_category_id' => $courseCategory->course_category_id ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('An error occurred while activating the course category.', 500);
        }
    }

    /**
     * Deactivate the specified course category.
     *
     * @param CourseCategory $courseCategory
     * @return JsonResponse
     */
    public function deactivate(CourseCategory $courseCategory): JsonResponse
    {
        try {
            $deactivatedCourseCategory = $this->courseCategoryService->deactivate($courseCategory);

            if (!$deactivatedCourseCategory) {
                throw new Exception('Cannot deactivate course category. It may have active published courses.', 422);
            }

            return self::success(
                new CourseCategoryResource($deactivatedCourseCategory),
                'Course category deactivated successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error deactivating course category', [
                'course_category_id' => $courseCategory->course_category_id ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
         //   throw new Exception('An error occurred while deactivating the course category.', 500);
         throw $e;
        }
    }
}
