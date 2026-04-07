<?php
namespace App\Http\Controllers;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use \Illuminate\Routing\Controller as BaseController;


/**
 * Base controller class for all application controllers.
 * Provides a common foundation for controller functionality in the Laravel application.
 */
abstract class Controller extends BaseController
{
    use AuthorizesRequests;

    /**
     * Return a success JSON response.
     *
     * @param  mixed|null  $data  The response data (optional).
     * @param  string  $message  The success message (default: "Operation successful").
     * @param  int  $status  The HTTP status code (default: 200).
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success($data = null, $message = 'Operation successful', $status = 200)
    {
        return response()->json(
            [
                'status' => 'success', // Indicates a successful operation
                'message' => trans($message), // Translates the message
                'data' => $data, // Contains the response data (if any)
            ],
            $status,
            options: JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION
        );
    }

    /**
     * Return an error JSON response.
     *
     * @param  string|array  $message  The error message or an array containing additional error details.
     * @param  int  $status  The HTTP status code (default: 400).
     * @param  mixed|null  $data  Additional data to include in the response (optional).
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error($message = 'Operation failed', $status = 400, $data = null)
    {
        // If the message is an array, don't translate it directly (handle structured error messages)
        if (is_array($message)) {
            // $messageArray = $message; // Preserve the existing message array
            // $messageArray['message'] = trans($message['message']); // Translate only the message key
            $translatedMessage = trans($message['message']);
        } else {
            // $messageArray['message'] = trans($message);
            $translatedMessage = trans($message);
        }

        return response()->json(
            [
                'status' => 'error', // Indicates an error response
                'message' => $translatedMessage, // Contains the error message(s)
                'data' => $data, // Optional additional data
            ],
            $status,
            options: JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION
        );
    }

    /**
     * Return a paginated JSON response.
     *
     * @param  LengthAwarePaginator  $paginator  The paginator instance containing paginated results.
     * @param  string  $message  The success message (default: "Operation successful").
     * @param  int  $status  The HTTP status code (default: 200).
     * @return \Illuminate\Http\JsonResponse
     */
    public static function paginated(LengthAwarePaginator $paginator, $message = 'Operation successful', $status = 200)
    {
        return response()->json([
            'status' => 'success', // Indicates a successful operation
            'message' => trans($message), // Translates the success message
            'data' => $paginator->items(), // Retrieves the current page's data
            'pagination' => [
                'total' => $paginator->total(), // Total number of records
                'count' => count($paginator), // Number of records on the current page
                'per_page' => $paginator->perPage(), // Records per page
                'current_page' => $paginator->currentPage(), // The current page number
                'total_pages' => $paginator->lastPage(), // The last page number
            ],
        ], $status, options: JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    }
}