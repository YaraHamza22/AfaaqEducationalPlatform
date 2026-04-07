<?php

namespace Modules\LearningModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Modules\LearningModule\Enums\EnrollmentStatus;
use Modules\LearningModule\Http\Requests\Enrollment\ChangeStatusEnrollmentRequest;
use Modules\LearningModule\Http\Requests\Enrollment\FilterEnrollmentsRequest;
use Modules\LearningModule\Http\Requests\Enrollment\StoreEnrollmentRequest;
use Modules\LearningModule\Http\Requests\Enrollment\UpdateEnrollmentRequest;
use Modules\LearningModule\Http\Resources\EnrollmentCollection;
use Modules\LearningModule\Http\Resources\EnrollmentResource;
use Modules\LearningModule\Models\Course;
use Modules\LearningModule\Models\Enrollment;
use Modules\LearningModule\Services\EnrollmentService;

/**
 * Controller for managing enrollments.
 * Handles HTTP requests and delegates business logic to EnrollmentService.
 * Follows SOLID principles: Single Responsibility, Dependency Inversion.
 *
 * Endpoints:
 * - GET /enrollments - List all enrollments with filtering
 * - POST /enrollments - Create new enrollment
 * - GET /enrollments/{id} - Get specific enrollment
 * - PUT /enrollments/{id} - Update enrollment
 * - DELETE /enrollments/{id} - Delete enrollment
 * - PUT /enrollments/{id}/status - Change enrollment status
 */
class EnrollmentController extends Controller
{
    /**
     * Enrollment service instance.
     *
     * @var EnrollmentService
     */
    protected EnrollmentService $enrollmentService;

    /**
     * Create a new controller instance.
     *
     * @param EnrollmentService $enrollmentService
     */
    public function __construct(EnrollmentService $enrollmentService)
    {
        $this->enrollmentService = $enrollmentService;
    }

    /**
     * Display a listing of enrollments with filtering and pagination.
     *
     * Query Parameters:
     * - learner_id: Filter by learner ID
     * - course_id: Filter by course ID
     * - status: Filter by enrollment status
     * - type: Filter by enrollment type
     * - search: Search by learner or course name
     * - sort: Sort field (default: enrollment_id)
     * - direction: Sort direction (asc|desc)
     * - per_page: Items per page (default: 15)
     * - page: Page number (default: 1)
     *
     * @param FilterEnrollmentsRequest $request
     * @return JsonResponse
     */
    public function index(FilterEnrollmentsRequest $request): JsonResponse
    {
        try {
            $query = Enrollment::query();

            $enrollments = $query
                ->filterByRequest($request)
                ->withRelations()
                ->ordered($request)
                ->paginateFromRequest($request)
                ->through(fn($enrollment) => new EnrollmentResource($enrollment));

            return self::paginated($enrollments, 'Enrollments retrieved successfully.');
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving enrollments', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // In debug, surface the real error so we can fix it
            if (config('app.debug')) {
                throw $e;
            }
            throw new Exception('Unable to retrieve enrollments at this time.', 500);
        }
    }

    /**
     * Store a newly created enrollment.
     *
     * Creates an enrollment relationship between a learner and course.
     * Validates that both learner and course exist, and course is available.
     *
     * @param StoreEnrollmentRequest $request
     * @return JsonResponse
     */
    public function store(StoreEnrollmentRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $course = Course::find($validated['course_id']);

            if (!$course) {
                throw new HttpException(404, 'Course not found.');
            }

            $enrollment = $this->enrollmentService->enroll(
                $course,
                $validated['learner_id'],
                $validated['enrollment_type'] ?? 'self',
                $validated['enrolled_by'] ?? null
            );

            if (!$enrollment) {
                throw new HttpException(422, 'Failed to create enrollment. The course may not be available for enrollment or the learner is already enrolled.');
            }

            $enrollment->load(['learner', 'course', 'enrolledBy']);

            return self::success(
                new EnrollmentResource($enrollment),
                'Enrollment created successfully.',
                201
            );
        } catch (Exception $e) {
            Log::error('Unexpected error creating enrollment', [
                'course_id' => $request->input('course_id'),
                'learner_id' => $request->input('learner_id'),
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            if (config('app.debug')) {
                throw $e;
            }
            if ($e instanceof HttpException) {
                throw $e;
            }
            throw new Exception('An error occurred while creating the enrollment.', 500);
        }
    }

    /**
     * Display the specified enrollment.
     *
     * @param Enrollment $enrollment
     * @return JsonResponse
     */
    public function show(Enrollment $enrollment): JsonResponse
    {
        try {
            $enrollment->load(['learner', 'course', 'enrolledBy']);

            return self::success(
                new EnrollmentResource($enrollment),
                'Enrollment retrieved successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving enrollment', [
                'enrollment_id' => $enrollment->enrollment_id ?? null,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Unable to retrieve enrollment details.', 500);
        }
    }

    /**
     * Update the specified enrollment.
     *
     * Updates enrollment fields such as enrollment_type, progress_percentage, and final_grade.
     *
     * @param UpdateEnrollmentRequest $request
     * @param Enrollment $enrollment
     * @return JsonResponse
     */
    public function update(UpdateEnrollmentRequest $request, Enrollment $enrollment): JsonResponse
    {
        try {
            $validated = $request->validated();
            $updatedEnrollment = $this->enrollmentService->update($enrollment, $validated);

            if (!$updatedEnrollment) {
                throw new Exception('Failed to update enrollment.', 422);
            }

            $updatedEnrollment->load(['learner', 'course', 'enrolledBy']);

            return self::success(
                new EnrollmentResource($updatedEnrollment),
                'Enrollment updated successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error updating enrollment', [
                'enrollment_id' => $enrollment->enrollment_id ?? null,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('An error occurred while updating the enrollment.', 500);
        }
    }

    /**
     * Change the enrollment status.
     *
     * Updates enrollment status and related timestamps.
     * Handles completion date and progress percentage automatically.
     *
     * @param ChangeStatusEnrollmentRequest $request
     * @param Enrollment $enrollment
     * @return JsonResponse
     */
    public function updateStatus(ChangeStatusEnrollmentRequest $request, Enrollment $enrollment): JsonResponse
    {
        try {
            $status = EnrollmentStatus::from($request->validated()['status']);
            $updatedEnrollment = $this->enrollmentService->updateStatus($enrollment, $status);

            if (!$updatedEnrollment) {
                throw new Exception('Failed to update enrollment status.', 422);
            }

            $updatedEnrollment->load(['learner', 'course', 'enrolledBy']);

            return self::success(
                new EnrollmentResource($updatedEnrollment),
                'Enrollment status updated successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error updating enrollment status', [
                'enrollment_id' => $enrollment->enrollment_id ?? null,
                'status' => $request->validated()['status'] ?? null,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('An error occurred while updating the enrollment status.', 500);
        }
    }


    /**
     * Get enrollment progress details.
     *
     * Returns detailed progress information including:
     * - Current progress percentage
     * - Completed units/lessons count
     * - Total units/lessons count
     * - Estimated completion time
     *
     * @param Enrollment $enrollment
     * @return JsonResponse
     */
    public function getProgress(Enrollment $enrollment): JsonResponse
    {
        try {
            $progress = $this->enrollmentService->getProgressDetails($enrollment);

            if (!$progress) {
                throw new Exception('Failed to retrieve progress details. Course information may be missing.', 404);
            }

            return self::success($progress, 'Progress details retrieved successfully.');
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving progress details', [
                'enrollment_id' => $enrollment->enrollment_id ?? null,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Unable to retrieve enrollment progress.', 500);
        }
    }
}
