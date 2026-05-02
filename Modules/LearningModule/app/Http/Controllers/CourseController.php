<?php

namespace Modules\LearningModule\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Traits\CachesQueries;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\LearningModule\Models\Course;
use Modules\LearningModule\Services\CourseService;
use Modules\LearningModule\Http\Resources\CourseResource;
use Modules\LearningModule\Services\CourseInstructorService;
use Modules\LearningModule\Http\Requests\Course\StoreCourseRequest;
use Modules\LearningModule\Http\Requests\Course\UpdateCourseRequest;
use Modules\LearningModule\Http\Requests\Course\FilterCoursesRequest;
use Modules\LearningModule\Http\Requests\Course\AssignInstructorRequest;
use Modules\LearningModule\Http\Requests\Course\RemoveInstructorRequest;
use Modules\LearningModule\Http\Requests\Course\ChangeStatusCourseRequest;
use Modules\LearningModule\Http\Requests\Course\SetPrimaryInstructorRequest;

/**
 * Controller for managing courses.
 * Handles HTTP requests and delegates business logic to CourseService.
 * Follows SOLID principles: Single Responsibility, Dependency Inversion.
 */
class CourseController extends Controller
{
    use CachesQueries;
    /**
     * Course service instance.
     *
     * @var CourseService
     */
    protected CourseService $courseService;

    /**
     * Course instructor service instance.
     *
     * @var CourseInstructorService
     */
    protected CourseInstructorService $courseInstructorService;

    /**
     * Create a new controller instance.
     *
     * @param CourseService $courseService
     * @param CourseInstructorService $courseInstructorService
     */
    public function __construct(CourseService $courseService, CourseInstructorService $courseInstructorService)
    {
        $this->courseService = $courseService;
        $this->courseInstructorService = $courseInstructorService;
        $this->middleware('permission:list-courses')->only('index');
        $this->middleware('permission:show-course')->only('show');
        $this->middleware('permission:create-course')->only('store');
        $this->middleware('permission:update-course')->only('update');
        $this->middleware('permission:delete-course')->only('destroy');
    }

    /**
     * Display a listing of courses.
     *
     * @param FilterCoursesRequest $request
     * @return JsonResponse
     */
    public function index(FilterCoursesRequest $request): JsonResponse
    {
        try {
            $getCourses = function () use ($request) {
                $query = Course::query();
                return $query
                    ->filterByRequest($request)
                    ->withRelations()
                    ->ordered()
                    ->paginateFromRequest($request)
                    ->through(fn($course) => new CourseResource($course));
            };

            // Only use cache when filters are applied; "list all" (no params) always hits DB
            $queryString = $request->getQueryString();
            if ($queryString !== null && $queryString !== '') {
                $cacheKey = 'courses.index.' . md5($queryString);
                $courses = $this->remember($cacheKey, 900, $getCourses, ['courses']);
            } else {
                $courses = $getCourses();
            }

            return self::paginated($courses, 'Courses retrieved successfully.');
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving courses', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Unable to retrieve courses at this time. Please try again later.', 500);
        }
    }

    /**
     * Store a newly created course.
     *
     * @param StoreCourseRequest $request
     * @return JsonResponse
     */
    public function store(StoreCourseRequest $request): JsonResponse
    {
        try {
            $course = $this->courseService->create($request->validated());

            if (!$course) {
                throw new Exception('Failed to create course. Please check your input and try again.', 422);
            }

            $course->load(['courseCategory', 'creator']);

            return self::success(
                new CourseResource($course),
                'Course created successfully.',
                201
            );
        } catch (Exception $e) {
            Log::error('Unexpected error creating course', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Show actual error in development, generic message in production
            $errorMessage = App::environment('local', 'testing')
                ? $e->getMessage()
                : 'An error occurred while creating the course. Please try again.';

            throw new Exception($errorMessage, 500);
        }
    }

    /**
     * Display the specified course.
     *
     * @param Course $course
     * @return JsonResponse
     */
    public function show(Course $course): JsonResponse
    {
        try {
            $cacheKey = "course.{$course->course_id}";

            $courseData = $this->remember($cacheKey, 1800, function () use ($course) {
                $course->load(['courseCategory', 'instructors', 'creator', 'units']);
                return new CourseResource($course);
            }, ['courses', "course.{$course->course_id}"]);

            return self::success(
                $courseData,
                'Course retrieved successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving course', [
                'course_id' => $course->course_id ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Unable to retrieve course details.', 500);
        }
    }

    /**
     * Update the specified course.
     *
     * @param UpdateCourseRequest $request
     * @param Course $course
     * @return JsonResponse
     */
    public function update(UpdateCourseRequest $request, Course $course): JsonResponse
    {
        try {
            $updatedCourse = $this->courseService->update($course, $request->validated());

            if (!$updatedCourse) {
                throw new Exception('Failed to update course. Please check your input and try again.', 422);
            }

            $updatedCourse->load(['courseCategory', 'creator']);

            return self::success(
                new CourseResource($updatedCourse),
                'Course updated successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error updating course', [
                'course_id' => $course->course_id ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Show actual error in development, generic message in production
            $errorMessage = App::environment('local', 'testing')
                ? $e->getMessage()
                : 'An error occurred while updating the course. Please try again.';

            throw new Exception($errorMessage, 500);
        }
    }

    /**
     * Remove the specified course.
     *
     * @param Course $course
     * @return JsonResponse
     */
    public function destroy(Course $course): JsonResponse
    {
        try {
            $deleted = $this->courseService->delete($course);

            if (!$deleted) {
                throw new Exception('Cannot delete course. It may have active enrollments or other dependencies.', 422);
            }

            return self::success(null, 'Course deleted successfully.');
        } catch (Exception $e) {
            Log::error('Unexpected error deleting course', [
                'course_id' => $course->course_id ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('An error occurred while deleting the course.', 500);
        }
    }

    /**
     * Publish the specified course.
     *
     * @param Course $course
     * @return JsonResponse
     */
    public function publish(Course $course): JsonResponse
    {
        try {
            $publishedCourse = $this->courseService->publish($course);

            if (!$publishedCourse) {
                throw new HttpException(422, 'Course cannot be published. Please ensure it has at least one instructor, one unit, and all required information.');
            }

            return self::success(
                new CourseResource($publishedCourse),
                'Course published successfully.'
            );
        } catch (Exception $e) {
            // Rethrow HTTP exceptions so the client gets the correct status and message
            if ($e instanceof HttpException) {
                throw $e;
            }
            Log::error('Unexpected error publishing course', [
                'course_id' => $course->course_id ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('An error occurred while publishing the course.', 500);
        }
    }

    /**
     * Unpublish the specified course.
     *
     * @param Course $course
     * @return JsonResponse
     */
    public function unpublish(Course $course): JsonResponse
    {
        try {
            $unpublishedCourse = $this->courseService->unpublish($course);

            return self::success(
                new CourseResource($unpublishedCourse),
                'Course unpublished successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error unpublishing course', [
                'course_id' => $course->course_id ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('An error occurred while unpublishing the course.', 500);
        }
    }

    /**
     * Change the status of the specified course.
     *
     * @param ChangeStatusCourseRequest $request
     * @param Course $course
     * @return JsonResponse
     */
    public function changeStatus(ChangeStatusCourseRequest $request, Course $course): JsonResponse
    {
        try {
            $updatedCourse = $this->courseService->changeStatus($course, $request->validated()['status']);

            if (!$updatedCourse) {
                throw new Exception('Invalid status provided or course cannot be changed to this status.', 422);
            }

            return self::success(
                new CourseResource($updatedCourse),
                'Course status changed successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error changing course status', [
                'course_id' => $course->course_id ?? null,
                'status' => $request->validated()['status'] ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('An error occurred while changing the course status.', 500);
        }
    }

    /**
     * Get course duration.
     *
     * @param Course $course
     * @return JsonResponse
     */
    public function getDuration(Course $course): JsonResponse
    {
        try {
            $duration = $this->courseService->getDuration($course);

            return self::success(
                [
                    'course_id' => $course->course_id,
                    'duration_hours' => $duration,
                    'actual_duration_hours' => $course->actual_duration_hours,
                ],
                'Course duration retrieved successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving course duration', [
                'course_id' => $course->course_id ?? null,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Unable to retrieve course duration.', 500);
        }
    }

    /**
     * Check if course can be published.
     *
     * @param Course $course
     * @return JsonResponse
     */
    public function checkPublishability(Course $course): JsonResponse
    {
        try {
            $isPublishable = $this->courseService->isPublishable($course);
            $reasons = $isPublishable ? [] : $this->courseService->getUnpublishabilityReasons($course);

            return self::success(
                [
                    'is_publishable' => $isPublishable,
                    'reasons' => $reasons,
                ],
                'Publishability check completed.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error checking publishability', [
                'course_id' => $course->course_id ?? null,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Unable to check course publishability.', 500);
        }
    }

    /**
     * Get courses available for enrollment.
     *
     * @param FilterCoursesRequest $request
     * @return JsonResponse
     */
    public function enrollable(FilterCoursesRequest $request): JsonResponse
    {
        try {
            // Generate cache key based on query parameters
            $cacheKey = 'courses.enrollable.' . md5($request->getQueryString());

            $courses = $this->remember($cacheKey, 900, function () use ($request) {
                return Course::query()
                    ->enrollable()
                    ->filterByRequest($request)
                    ->withRelations()
                    ->ordered()
                    ->paginateFromRequest($request)
                    ->through(fn($course) => new CourseResource($course));
            }, ['courses', 'courses.enrollable']);

            return self::paginated($courses, 'Enrollable courses retrieved successfully.');
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving enrollable courses', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Unable to retrieve enrollable courses.', 500);
        }
    }

    /**
     * Get courses by instructor.
     *
     * @param FilterCoursesRequest $request
     * @param int $instructorId
     * @return JsonResponse
     */
    public function byInstructor(FilterCoursesRequest $request, int $instructorId): JsonResponse
    {
        try {
            $courses = Course::query()
                ->byInstructor($instructorId)
                ->filterByRequest($request)
                ->withRelations()
                ->ordered()
                ->paginateFromRequest($request)
                ->through(fn($course) => new CourseResource($course));

            return self::paginated($courses, 'Instructor courses retrieved successfully.');
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving instructor courses', [
                'instructor_id' => $instructorId ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Unable to retrieve courses for this instructor.', 500);
        }
    }

    /**
     * Assign an instructor to a course.
     *
     * @param AssignInstructorRequest $request
     * @param Course $course
     * @return JsonResponse
     */
    public function assignInstructor(AssignInstructorRequest $request, Course $course): JsonResponse
    {
        try {
            $assignment = $this->courseInstructorService->assign(
                $course,
                $request->input('instructor_id'),
                $request->input('is_primary', false)
            );

            if (!$assignment) {
                throw new Exception('Instructor is already assigned to this course or assignment failed.', 422);
            }

            $course->load('instructors');

            return self::success(
                new CourseResource($course),
                'Instructor assigned to course successfully.',
                201
            );
        } catch (Exception $e) {
            Log::error('Unexpected error assigning instructor', [
                'course_id' => $course->course_id ?? null,
                'instructor_id' => $request->input('instructor_id'),
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('An error occurred while assigning the instructor.', 500);
        }
    }

    /**
     * Remove an instructor from a course.
     *
     * @param RemoveInstructorRequest $request
     * @param Course $course
     * @return JsonResponse
     */
    public function removeInstructor(RemoveInstructorRequest $request, Course $course): JsonResponse
    {
        try {
            $removed = $this->courseInstructorService->remove($course, $request->input('instructor_id'));

            if (!$removed) {
                throw new Exception('Cannot remove instructor. The course must have at least one instructor.', 422);
            }

            $course->load('instructors');

            return self::success(
                new CourseResource($course),
                'Instructor removed from course successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error removing instructor', [
                'course_id' => $course->course_id ?? null,
                'instructor_id' => $request->input('instructor_id'),
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
          //  throw new Exception('An error occurred while removing the instructor.', 500);
          throw $e;
        }
    }

    /**
     * Set an instructor as primary for the course.
     *
     * @param SetPrimaryInstructorRequest $request
     * @param Course $course
     * @return JsonResponse
     */
    public function setPrimaryInstructor(SetPrimaryInstructorRequest $request, Course $course): JsonResponse
    {
        try {
            $assignment = $this->courseInstructorService->setPrimary($course, $request->input('instructor_id'));

            if (!$assignment) {
                throw new Exception('Instructor is not assigned to this course.', 404);
            }

            $course->load('instructors');

            return self::success(
                new CourseResource($course),
                'Instructor set as primary successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error setting primary instructor', [
                'course_id' => $course->course_id ?? null,
                'instructor_id' => $request->input('instructor_id'),
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('An error occurred while setting the primary instructor.', 500);
        }
    }

    /**
     * Unset primary flag for an instructor.
     *
     * @param RemoveInstructorRequest $request
     * @param Course $course
     * @return JsonResponse
     */
    public function unsetPrimaryInstructor(RemoveInstructorRequest $request, Course $course): JsonResponse
    {
        try {
            $this->courseInstructorService->unsetPrimary($course, $request->input('instructor_id'));

            $course->load('instructors');

            return self::success(
                new CourseResource($course),
                'Primary instructor flag removed successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error unsetting primary instructor', [
                'course_id' => $course->course_id ?? null,
                'instructor_id' => $request->input('instructor_id'),
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('An error occurred while removing the primary instructor flag.', 500);
        }
    }

    /**
     * Get all instructors for a specific course.
     *
     * @param Request $request
     * @param Course $course
     * @return JsonResponse
     */
    public function getInstructors(Request $request, Course $course): JsonResponse
    {
        try {
            $instructors = $this->courseInstructorService->getInstructors($course);

            $instructorsData = $instructors->map(function ($courseInstructor) {
                return [
                    'id' => $courseInstructor->instructor_id,
                    'name' => $courseInstructor->instructor->name ?? null,
                    'email' => $courseInstructor->instructor->email ?? null,
                    'is_primary' => $courseInstructor->is_primary,
                    'assigned_at' => $courseInstructor->assigned_at?->toDateTimeString(),
                    'assigned_by' => $courseInstructor->assignedBy ? [
                        'id' => $courseInstructor->assignedBy->id,
                        'name' => $courseInstructor->assignedBy->name,
                    ] : null,
                ];
            });

            return self::success(
                [
                    'course_id' => $course->course_id,
                    'course_title' => $course->title,
                    'instructors' => $instructorsData,
                    'instructors_count' => $instructorsData->count(),
                ],
                'Course instructors retrieved successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving course instructors', [
                'course_id' => $course->course_id ?? null,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Unable to retrieve course instructors.', 500);
        }
    }
}
