<?php

namespace Modules\AssesmentModule\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\AssesmentModule\Http\Requests\AttemptRequest\GradeAttemptRequest;
use Modules\AssesmentModule\Http\Requests\AttemptRequest\StartAttemptRequest;
use Modules\AssesmentModule\Http\Requests\AttemptRequest\SubmitAttemptRequest;
use Modules\AssesmentModule\Http\Requests\AttemptRequest\StoreAttemptRequest;
use Modules\AssesmentModule\Http\Requests\AttemptRequest\UpdateAttemptRequest;
use Modules\AssesmentModule\Models\Attempt;
use Modules\AssesmentModule\Services\V1\AttemptService;
use Throwable;

/**
 * AttemptController handles the CRUD operations for managing attempts.
 * Provides endpoints for listing, creating, updating, starting, submitting, and grading attempts.
 *
 * @package Modules\AssesmentModule\Http\Controllers\Api\V1
 */
class AttemptController extends Controller
{
    private AttemptService $attemptService;

    /**
     * AttemptController constructor.
     *
     * @param AttemptService $attemptService The service for managing attempt business logic.
     */
    public function __construct(AttemptService $attemptService)
    {
        $this->attemptService = $attemptService;
        $this->middleware('role_or_permission:super-admin|student|list-attempts,api')->only('index');
        $this->middleware('role_or_permission:super-admin|student|show-attempt,api')->only('show');
        $this->middleware('role_or_permission:super-admin|student|create-attempt,api')->only('store');
        $this->middleware('role_or_permission:super-admin|student|update-attempt,api')->only('update');
        $this->middleware('role_or_permission:super-admin|student|delete-attempt,api')->only('destroy');
        $this->middleware('role_or_permission:super-admin|student|submit-attempt,api')->only('start', 'submit');
        $this->middleware('role_or_permission:super-admin|student|grade-attempt,api')->only('grade');
    }

    /**
     * Display a listing of attempts based on filters.
     *
     * @param Request $request The request containing filtering and pagination parameters.
     * @return \Illuminate\Http\JsonResponse JSON response with paginated data or error.
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only([
                'quiz_id', 'student_id', 'status', 'is_passed', 'graded_by', 'attempt_number',
                'start_at', 'ends_at', 'min_score', 'max_score', 'submitted_at', 'graded_at',
                'min_time_spent', 'max_time_spent', 'order'
            ]);

            $perPage = (int) $request->integer('per_page', 15);
            $data = $this->attemptService->index($filters, $perPage);

            if ($data instanceof LengthAwarePaginator) {
                return self::paginated($data, 'Operation successful', 200);
            }

            return self::success($data, 'Operation successful', 200);

        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified attempt.
     *
     * @param Attempt $attempt The attempt model instance.
     * @return \Illuminate\Http\JsonResponse JSON response with the attempt data or error.
     */
    public function show(Attempt $attempt)
    {
        try {
            $data = $this->attemptService->show($attempt);
            return self::success($data, 'Operation successful', 200);
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

/**
* Store a newly created attempt.
 *
 * This method validates the incoming request data and passes it to the service for storing
 * a new attempt in the database. It then returns a success response if the attempt is created
 * successfully or an error response if something goes wrong.
 *
 * @param StoreAttemptRequest $request The request object containing validated data for the attempt.
 * 
 * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the operation.
 *         If successful, returns the created attempt data with status 201.
 *         If there's an error, returns an error message with status 500.
 * 
 * @throws \Throwable If there is an error while storing the attempt.
 */
    public function store(StoreAttemptRequest $request)
    {
    try {
        // Get validated data from the request
        $data = $request->validated();

        // Use the service to store the attempt
        $data = $this->attemptService->store($data);
        
        return self::success($data, 'Attempt created successfully', 201);
    } catch (Throwable $e) {
        // Handle errors and return error response
        return self::error($e->getMessage(), 500);
    }
    }
 

    /**
     * Update the specified attempt.
     *
     * @param UpdateAttemptRequest $request The request with validated data to update the attempt.
     * @param int $attempt The ID of the attempt to update.
     * @return \Illuminate\Http\JsonResponse JSON response with the updated attempt data or error.
     */
    public function update(UpdateAttemptRequest $request, Attempt $attempt)
    {
        try {
            $data = $this->attemptService->update($attempt, $request->validated());
            return self::success($data, 'Attempt updated successfully', 200);
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

   /**
 * Start a new attempt for the student.
 *
 * This method receives the validated data from the `StartAttemptRequest`, 
 * then it passes the data to the `AttemptService` for creating the attempt.
 * If successful, it returns a success JSON response. If there's an error, 
 * it returns an error response.
 *
 * @param StartAttemptRequest $request The validated data from the request.
 * 
 * @return \Illuminate\Http\JsonResponse JSON response indicating the result.
 *         - Success: Returns the created attempt data with status 201.
 *         - Failure: Returns the error message with status 500.
 * 
 * @throws \Throwable If an error occurs while starting the attempt.
 */
   public function start(StartAttemptRequest $request, Attempt $attempt)
   {
    try {
        // Pass validated data to the service for processing
        $data = $this->attemptService->start($attempt);
        return self::success($data, 'Attempt started successfully', 201);
    } catch (Throwable $e) {
        return self::error($e->getMessage(), 500);
    }
    }


/**
 * Submit the attempt.
 *
 * This method validates the incoming request and passes the validated data
 * to the `AttemptService` to submit the attempt. If successful, it returns
 * a success response. If there's an error, it returns an error response.
 *
 * @param SubmitAttemptRequest $request The validated data from the request.
 * @param int $attempt The ID of the attempt to submit.
 * @return \Illuminate\Http\JsonResponse JSON response indicating success or failure.
 */
public function submit(SubmitAttemptRequest $request, Attempt $attempt)
{
    try {
        // Pass validated data to the service for submission
        $data = $this->attemptService->submit($attempt, $request->validated());
        
        // Return success response with the submitted attempt data
        return self::success($data, 'Attempt submitted successfully', 200);
    } catch (Throwable $e) {
        // Return error response if something goes wrong
        return self::error($e->getMessage(), 500);
    }
}


    /**
     * Grade the attempt.
     *
     * @param GradeAttemptRequest $request The request containing grading data for the attempt.
     * @param int $attempt The ID of the attempt to grade.
     * @return \Illuminate\Http\JsonResponse JSON response with the graded attempt data or error.
     */
    public function grade(GradeAttemptRequest $request, Attempt $attempt)
    {
        try {
            $data = $this->attemptService->grade($attempt, $request->validated());
            return self::success($data, 'Attempt graded successfully', 200);
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }
 /**
 * Destroy the specified attempt.
 *
 * This method receives the attempt ID from the request, passes it to the service,
 * and deletes the attempt from the database if it exists. If successful, it returns
 * a success response. If the attempt is not found or if there is an error, it returns
 * an error response.
 *
 * @param int $attempt The ID of the attempt to delete.
 * @return \Illuminate\Http\JsonResponse JSON response indicating success or failure.
 */
    public function destroy(Attempt $attempt)
   {
    try {
        // Pass attempt ID to the service to delete the attempt
        $data = $this->attemptService->destroy($attempt);
        
        // Return success response if deletion is successful
        return self::success($data, 'Attempt deleted successfully', 200);
    } catch (Throwable $e) {
        // Return error response if there is an issue
        return self::error($e->getMessage(), 500);
    }
    }

}
