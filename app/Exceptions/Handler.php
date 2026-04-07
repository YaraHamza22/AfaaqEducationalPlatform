<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     * Logging is handled in service classes, so no need to log here.
     */
    public function register(): void
    {
        // No logging here - handled in service classes
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param \Throwable $e
     * @return JsonResponse|\Illuminate\Http\Response
     * @throws \Throwable
     */
    public function render($request, \Throwable $e)
    {
        // Handle all API requests with consistent JSON responses
        // Check if it's an API route or if the request expects JSON
        $isApiRequest = $request->is('api/*') 
            || $request->expectsJson() 
            || $request->wantsJson()
            || str_starts_with($request->path(), 'api/');
        
        if ($isApiRequest) {
            return $this->handleApiException($request, $e);
        }

        // For web requests, use Laravel's default handling but ensure proper error pages
        return parent::render($request, $e);
    }

    /**
     * Handle API exceptions and return readable JSON responses.
     *
     * @param Request $request
     * @param \Throwable $e
     * @return JsonResponse
     */
    protected function handleApiException(Request $request, \Throwable $e): JsonResponse
    {
        // Handle authentication exceptions
        if ($e instanceof AuthenticationException) {
            return $this->handleAuthenticationException($e);
        }

        // Handle authorization exceptions
        if ($e instanceof AuthorizationException) {
            return $this->handleAuthorizationException($e);
        }

        // Handle Spatie Permission exceptions
        if ($e instanceof \Spatie\Permission\Exceptions\UnauthorizedException) {
            return $this->handleUnauthorizedException($e);
        }

        // Handle validation exceptions
        if ($e instanceof ValidationException) {
            return $this->handleValidationException($e);
        }

        // Handle model not found exceptions
        if ($e instanceof ModelNotFoundException) {
            return $this->handleModelNotFoundException($e);
        }

        // Handle HTTP exceptions (404, 403, etc.)
        if ($e instanceof HttpException) {
            return $this->handleHttpException($e);
        }

        // Handle database query exceptions
        if ($e instanceof QueryException) {
            return $this->handleQueryException($e);
        }

        // Handle generic exceptions
        return $this->handleGenericException($e);
    }

    /**
     * Handle authentication exceptions.
     *
     * @param AuthenticationException $e
     * @return JsonResponse
     */
    protected function handleAuthenticationException(AuthenticationException $e): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthenticated. Please login to access this resource.',
            'data' => null,
            'error_code' => 'UNAUTHORIZED',
            'hint' => 'Please authenticate to access this resource.',
        ], 401, [], JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    }

    /**
     * Handle authorization exceptions.
     *
     * @param AuthorizationException $e
     * @return JsonResponse
     */
    protected function handleAuthorizationException(AuthorizationException $e): JsonResponse
    {
        $message = $e->getMessage() ?: 'You do not have permission to perform this action.';

        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => null,
            'error_code' => 'FORBIDDEN',
            'hint' => 'You do not have permission to perform this action.',
        ], 403, [], JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    }

    /**
     * Handle Spatie Permission unauthorized exceptions.
     *
     * @param \Spatie\Permission\Exceptions\UnauthorizedException $e
     * @return JsonResponse
     */
    protected function handleUnauthorizedException(\Spatie\Permission\Exceptions\UnauthorizedException $e): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized. You do not have the required permissions.',
            'data' => null,
            'error_code' => 'FORBIDDEN',
            'hint' => 'You do not have permission to perform this action.',
        ], 403, [], JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    }

    /**
     * Handle validation exceptions.
     *
     * @param ValidationException $e
     * @return JsonResponse
     */
    protected function handleValidationException(ValidationException $e): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed. Please check your input.',
            'data' => null,
            'errors' => $e->errors(),
            'error_code' => 'VALIDATION_ERROR',
        ], 422, [], JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    }

    /**
     * Handle database query exceptions.
     *
     * @param QueryException $e
     * @return JsonResponse
     */
    protected function handleQueryException(QueryException $e): JsonResponse
    {
        $errorCode = $e->getCode();
        $errorMessage = $e->getMessage();
        $message = $this->getReadableDatabaseErrorMessage($e);
        $hint = $this->getDatabaseErrorHint($e);

        // Determine appropriate HTTP status code
        $statusCode = 400; // Default to Bad Request
        if ($errorCode == 23000) {
            // Integrity constraint violations - could be 400 or 422
            if (str_contains($errorMessage, 'foreign key constraint')) {
                $statusCode = 422; // Unprocessable Entity - validation-like error
            } elseif (str_contains($errorMessage, 'cannot be null')) {
                $statusCode = 422; // Unprocessable Entity - validation error
            } elseif (str_contains($errorMessage, 'Duplicate entry')) {
                $statusCode = 409; // Conflict - resource already exists
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => null,
            'error_code' => $statusCode === 409 ? 'RESOURCE_CONFLICT' : 'DATABASE_ERROR',
            'hint' => $hint,
        ], $statusCode, [], JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    }

    /**
     * Get readable database error message.
     *
     * @param QueryException $e
     * @return string
     */
    protected function getReadableDatabaseErrorMessage(QueryException $e): string
    {
        $errorCode = $e->getCode();
        $errorMessage = $e->getMessage();

        // Handle specific SQL error codes
        // Note: $errorCode is a string, so we compare as string
        $errorCodeStr = (string) $errorCode;

        if ($errorCodeStr === '23000') { // Integrity constraint violation
            if (str_contains($errorMessage, 'cannot be null')) {
                $column = $this->extractColumnName($errorMessage);
                return "The field '{$column}' is required and cannot be empty.";
            }
            if (str_contains($errorMessage, 'Duplicate entry')) {
                $value = $this->extractDuplicateValue($errorMessage);
                return "This record already exists. The value '{$value}' is already in use.";
            }
            if (str_contains($errorMessage, 'foreign key constraint')) {
                $constraintInfo = $this->extractForeignKeyInfo($errorMessage);
                if ($constraintInfo) {
                    $fieldName = $constraintInfo['field'];
                    return "The {$fieldName} you specified does not exist. Please provide a valid {$fieldName}.";
                }
                return "Cannot perform this operation. The referenced record does not exist or is invalid.";
            }
            return "Database constraint violation. Please check your input data.";
        } elseif ($errorCodeStr === '42S22' || str_contains($errorMessage, 'Column not found')) { // Column not found
            return "Invalid field name. Please check your request data.";
        } elseif ($errorCodeStr === '42000' || str_contains($errorMessage, 'Syntax error')) { // Syntax error
            return "Invalid database query. Please contact support if this persists.";
        } else {
            // Extract meaningful part of the error
            if (str_contains($errorMessage, 'SQLSTATE')) {
                return "A database error occurred. Please check your input and try again.";
            }
            return "Database operation failed. Please try again.";
        }
    }

    /**
     * Get helpful hint for database errors.
     *
     * @param QueryException $e
     * @return string
     */
    protected function getDatabaseErrorHint(QueryException $e): string
    {
        $errorMessage = $e->getMessage();

        if (str_contains($errorMessage, 'cannot be null')) {
            $column = $this->extractColumnName($errorMessage);
            return "Please provide a value for '{$column}' field.";
        }

        if (str_contains($errorMessage, 'Duplicate entry')) {
            return "The value you're trying to use already exists. Please use a different value.";
        }

        if (str_contains($errorMessage, 'foreign key constraint')) {
            $constraintInfo = $this->extractForeignKeyInfo($errorMessage);
            if ($constraintInfo) {
                $fieldName = $constraintInfo['field'];
                return "Please ensure the {$fieldName} exists in the system before creating this record. You may need to create the {$fieldName} first.";
            }
            return "Make sure all referenced records exist before creating this record.";
        }

        return "Please verify your input data and try again.";
    }

    /**
     * Extract column name from error message.
     *
     * @param string $message
     * @return string
     */
    protected function extractColumnName(string $message): string
    {
        if (preg_match("/Column '([^']+)' cannot be null/", $message, $matches)) {
            return str_replace('_', ' ', ucwords($matches[1], '_'));
        }
        return 'field';
    }

    /**
     * Extract duplicate value from error message.
     *
     * @param string $message
     * @return string
     */
    protected function extractDuplicateValue(string $message): string
    {
        if (preg_match("/Duplicate entry '([^']+)'/", $message, $matches)) {
            return $matches[1];
        }
        return 'value';
    }

    /**
     * Extract foreign key constraint information from error message.
     *
     * @param string $message
     * @return array|null
     */
    protected function extractForeignKeyInfo(string $message): ?array
    {
        // Try to extract table and column from foreign key constraint error
        // Example: "Cannot add or update a child row: a foreign key constraint fails (`database`.`table`, CONSTRAINT `fk_name` FOREIGN KEY (`column`) REFERENCES `referenced_table` (`referenced_column`))"

        // Extract column name from FOREIGN KEY (`column`)
        if (preg_match("/FOREIGN KEY \(`([^`]+)`\)/", $message, $matches)) {
            $column = $matches[1];

            // Try to extract referenced table
            $referencedTable = null;
            if (preg_match("/REFERENCES `([^`]+)`/", $message, $refMatches)) {
                $referencedTable = $refMatches[1];
            }

            // Create user-friendly field name
            $fieldName = $this->getFieldDisplayName($column, $referencedTable);

            return [
                'field' => $fieldName,
                'column' => $column,
                'referenced_table' => $referencedTable,
            ];
        }

        // Alternative pattern: constraint name might contain field info
        if (preg_match("/CONSTRAINT `([^`]+)`/", $message, $constraintMatches)) {
            $constraintName = $constraintMatches[1];
            // Try to extract field name from constraint name (e.g., courses_course_type_id_foreign or courses_created_by_foreign)
            if (preg_match("/_([a-z_]+)_foreign$/", $constraintName, $fieldMatches)) {
                $column = $fieldMatches[1];
                $fieldName = $this->getFieldDisplayName($column);
                return [
                    'field' => $fieldName,
                    'column' => $column,
                ];
            }
        }

        return null;
    }

    /**
     * Get user-friendly display name for a field.
     *
     * @param string $column
     * @param string|null $referencedTable
     * @return string
     */
    protected function getFieldDisplayName(string $column, ?string $referencedTable = null): string
    {
        // Map common column names to user-friendly names
        $fieldMap = [
            'created_by' => 'creator',
            'updated_by' => 'updater',
            'course_type_id' => 'course type',
            'instructor_id' => 'instructor',
            'learner_id' => 'learner',
            'course_id' => 'course',
            'unit_id' => 'unit',
            'lesson_id' => 'lesson',
            'enrollment_id' => 'enrollment',
        ];

        // Check if we have a mapping
        if (isset($fieldMap[$column])) {
            return $fieldMap[$column];
        }

        // If referenced table is known, use it
        if ($referencedTable) {
            $tableName = str_replace('_', ' ', $referencedTable);
            // Remove plural 's' if present
            $tableName = rtrim($tableName, 's');
            return $tableName;
        }

        // Fallback: convert column name to readable format
        $fieldName = str_replace('_', ' ', $column);
        // Remove common suffixes
        $fieldName = preg_replace('/\s+(id|by)$/i', '', $fieldName);
        return ucwords($fieldName);
    }

    /**
     * Handle HTTP exceptions.
     *
     * @param HttpException $e
     * @return JsonResponse
     */
    protected function handleHttpException(HttpException $e): JsonResponse
    {
        $statusCode = $e->getStatusCode();
        
        // For 404 errors, provide more user-friendly messages
        if ($statusCode === 404) {
            $message = $e->getMessage();
            
            // Check if it's a model not found error (converted to NotFoundHttpException)
            if (str_contains($message, 'No query results for model')) {
                // Extract model name from message
                if (preg_match('/model \[([^\]]+)\]/', $message, $matches)) {
                    $modelClass = $matches[1];
                    $modelName = class_basename($modelClass);
                    $modelName = str_replace('_', ' ', strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $modelName)));
                    
                    return response()->json([
                        'status' => 'error',
                        'message' => "The requested {$modelName} was not found.",
                        'data' => null,
                        'error_code' => 'RESOURCE_NOT_FOUND',
                        'hint' => "Please verify the ID and try again.",
                    ], 404, [], JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
                }
            }
            
            // For any 404, provide a user-friendly message
            $message = $message && !str_contains($message, 'Symfony') && !str_contains($message, 'HttpKernel')
                ? $message
                : 'The requested resource was not found.';
        } else {
            $message = $e->getMessage() ?: $this->getDefaultHttpMessage($statusCode);
        }

        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => null,
            'error_code' => $this->getErrorCodeForStatusCode($statusCode),
        ], $statusCode, [], JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    }

    /**
     * Handle model not found exceptions.
     *
     * @param ModelNotFoundException $e
     * @return JsonResponse
     */
    public function handleModelNotFoundException(ModelNotFoundException $e): JsonResponse
    {
        $model = class_basename($e->getModel());
        $modelName = str_replace('_', ' ', strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $model)));

        return response()->json([
            'status' => 'error',
            'message' => "The requested {$modelName} was not found.",
            'data' => null,
            'error_code' => 'RESOURCE_NOT_FOUND',
            'hint' => "Please verify the ID and try again.",
        ], 404, [], JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    }

    /**
     * Handle generic exceptions.
     *
     * @param \Throwable $e
     * @return JsonResponse
     */
    protected function handleGenericException(\Throwable $e): JsonResponse
    {
        $message = $e->getMessage();
        $code = $e->getCode();

        // Check if message is user-friendly (not technical)
        $isUserFriendly = !empty($message)
            && !str_contains($message, 'SQLSTATE')
            && !str_contains($message, 'Call to')
            && !str_contains($message, 'Undefined')
            && !str_contains($message, 'Trying to get property')
            && !preg_match('/^[A-Z_]+$/', $message); // Not all caps (likely error constant)

        // Determine HTTP status code from exception code if it's a valid HTTP code
        $statusCode = ($code >= 400 && $code < 600 && $code !== 0) ? $code : 500;

        // If message is not user-friendly and status is 500, use generic message
        if (!$isUserFriendly && $statusCode === 500) {
            $message = "An unexpected error occurred. Please try again.";
        }

        // Extract error code based on status
        $errorCode = $this->getErrorCodeForStatusCode($statusCode);
        if ($statusCode === 422) {
            $errorCode = 'VALIDATION_ERROR';
        } elseif ($statusCode === 404) {
            $errorCode = 'RESOURCE_NOT_FOUND';
        }

        // Generate hint based on status and message
        $hint = $this->generateHintForException($e, $statusCode, $message);

        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => null,
            'error_code' => $errorCode,
            'hint' => $hint,
        ], $statusCode, [], JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    }

    /**
     * Generate helpful hint based on exception and status code.
     *
     * @param \Throwable $e
     * @param int $statusCode
     * @param string $message
     * @return string
     */
    protected function generateHintForException(\Throwable $e, int $statusCode, string $message): string
    {
        // LearningModule-specific hints
        if (str_contains($message, 'not found')) {
            if (str_contains($message, 'Course type')) {
                return "Please ensure the course type exists before creating or updating a course.";
            }
            if (str_contains($message, 'Course')) {
                return "Please verify the course ID and ensure the course exists.";
            }
            if (str_contains($message, 'Unit')) {
                return "Please verify the unit ID and ensure the unit exists.";
            }
            if (str_contains($message, 'Lesson')) {
                return "Please verify the lesson ID and ensure the lesson exists.";
            }
            if (str_contains($message, 'Instructor')) {
                return "Please ensure the instructor is assigned to the course before performing this action.";
            }
            return "Please verify the resource ID and try again.";
        }

        if (str_contains($message, 'already')) {
            if (str_contains($message, 'enrolled')) {
                return "The learner is already enrolled in this course. Check existing enrollments or reactivate a previous enrollment.";
            }
            if (str_contains($message, 'assigned')) {
                return "The instructor is already assigned to this course. Remove the existing assignment first if needed.";
            }
            return "This record already exists. Please use a different value or update the existing record.";
        }

        if (str_contains($message, 'cannot be')) {
            if (str_contains($message, 'deleted')) {
                if (str_contains($message, 'lesson')) {
                    return "Remove all lessons from this unit before deleting it.";
                }
                if (str_contains($message, 'enrollments')) {
                    return "Complete or cancel all active enrollments before deleting this course.";
                }
                if (str_contains($message, 'course')) {
                    return "Remove all courses associated with this course type before deleting it.";
                }
                return "This resource cannot be deleted because it has dependent records. Remove dependencies first.";
            }
            if (str_contains($message, 'published')) {
                return "Ensure the course has at least one instructor, one unit, and all required information before publishing.";
            }
            if (str_contains($message, 'deactivated')) {
                return "Unpublish or remove all published courses associated with this course type before deactivating it.";
            }
            if (str_contains($message, 'removed')) {
                return "Ensure the course has at least one instructor. Assign another instructor before removing this one.";
            }
            if (str_contains($message, 'reactivated')) {
                return "Only suspended or dropped enrollments can be reactivated. Check the enrollment status first.";
            }
            return "This operation cannot be performed due to business rules. Please check the requirements.";
        }

        if (str_contains($message, 'not available')) {
            return "The course must be published and active to allow enrollments. Check the course status.";
        }

        if (str_contains($message, 'Duplicate orders')) {
            return "Each unit/lesson must have a unique order number. Ensure all order values are different.";
        }

        if (str_contains($message, 'Invalid status')) {
            return "Please use a valid status: draft, review, published, or archived.";
        }

        // Default hints based on status code
        return match ($statusCode) {
            404 => "Please verify the resource ID and try again.",
            422 => "Please check your input data and ensure all requirements are met.",
            403 => "You do not have permission to perform this action.",
            401 => "Please authenticate to access this resource.",
            500 => "Please try again later. If the problem persists, contact support.",
            default => "Please check your request and try again.",
        };
    }

    /**
     * Get default HTTP message for status code.
     *
     * @param int $statusCode
     * @return string
     */
    protected function getDefaultHttpMessage(int $statusCode): string
    {
        return match ($statusCode) {
            404 => 'The requested resource was not found.',
            403 => 'You do not have permission to access this resource.',
            401 => 'Authentication required. Please login to access this resource.',
            500 => 'An internal server error occurred.',
            default => 'An error occurred while processing your request.',
        };
    }

    /**
     * Get error code for HTTP status code.
     *
     * @param int $statusCode
     * @return string
     */
    protected function getErrorCodeForStatusCode(int $statusCode): string
    {
        return match ($statusCode) {
            400 => 'BAD_REQUEST',
            401 => 'UNAUTHORIZED',
            403 => 'FORBIDDEN',
            404 => 'NOT_FOUND',
            409 => 'RESOURCE_CONFLICT',
            422 => 'VALIDATION_ERROR',
            500 => 'INTERNAL_SERVER_ERROR',
            default => 'HTTP_ERROR',
        };
    }
}