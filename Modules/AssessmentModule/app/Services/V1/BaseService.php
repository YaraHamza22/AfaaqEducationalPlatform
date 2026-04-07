<?php

namespace Modules\AssessmentModule\Services\V1;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class BaseService
 *
 * This is an abstract base service class that provides utility methods
 * for responding to requests with either success or failure. It includes:
 * - `ok()` method for successful responses
 * - `fail()` method for handling errors and logging exceptions
 *
 * Other services can extend this class to inherit the common response methods
 * and handle their specific logic.
 *
 * @package Modules\AssessmentModule\Services\V1
 */
abstract class BaseService
{
    /**
     * Handle the service logic. Currently a placeholder for additional functionality.
     *
     * This is an abstract method that can be implemented in child services.
     * 
     * @return void
     */
    public function handle() {}

    /**
     * Return a successful response with a message and optional data.
     *
     * @param string $message The success message.
     * @param mixed $data Optional data to include in the response.
     * @param int $code HTTP status code for the response (default is 200).
     *
     * @return array<string, mixed> The successful response.
     */
    protected function ok(string $message, mixed $data = null, int $code = 200): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'code'    => $code,
        ];
    }

    /**
     * Return a failure response with an error message, optional exception details, and a status code.
     * Logs the exception if provided.
     *
     * @param string $message The error message.
     * @param Throwable|null $e The exception to log (optional).
     * @param int $code HTTP status code for the response (default is 500).
     *
     * @return array<string, mixed> The failure response.
     */
    protected function fail(string $message, ?Throwable $e = null, int $code = 500): array
    {
        if ($e) {
            Log::error($message, ['exception' => $e]);
        }

        return [
            'success' => false,
            'message' => $message,
            'error'   => $e?->getMessage(),
            'code'    => $code,
        ];
    }
}