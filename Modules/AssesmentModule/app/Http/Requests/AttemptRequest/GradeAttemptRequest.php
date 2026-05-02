<?php

namespace Modules\AssesmentModule\Http\Requests\AttemptRequest;

use App\Http\Requests\ApiFormRequest;

/**
 * Class GradeAttemptRequest
 *
 * This class handles the validation for grading an attempt. It ensures that the provided `score`, `is_passed` status, 
 * `graded_at` date, and `graded_by` user ID are valid according to the specified rules. 
 * The `score` and `is_passed` fields are required, while `graded_at` and `graded_by` are optional, 
 * but if provided, they must meet the specific validation criteria.
 *
 * @package Modules\AssesmentModule\Http\Requests\AttemptRequest
 */
class GradeAttemptRequest extends ApiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * This method checks whether the user is authorized to grade an attempt. 
     * By default, it returns `true`, meaning the request is always authorized.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * This method defines the validation rules for the `score`, `is_passed`, `graded_at`, 
     * and `graded_by` fields. It ensures that the `score` is a non-negative integer, 
     * the `is_passed` field is a boolean, and the `graded_at` date is valid if provided.
     * If the `graded_by` field is provided, it must be a valid user ID from the `users` table.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'answers' => ['required', 'array', 'min:1'],
            'answers.*.question_id' => ['required', 'integer', 'exists:questions,id'],
            'answers.*.earned_score' => ['required', 'integer', 'min:0'],
            'answers.*.is_correct' => ['required', 'boolean'],
        ];
    }
}
