<?php

namespace Modules\AssessmentModule\Http\Requests\AttemptRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttemptRequest extends FormRequest
{
     /**
     * Get the validation rules that apply to the request.
     *
     * This method defines the validation rules for the `quiz_id`, `student_id`, `attempt_number`, 
     * `status`, `score`, `is_passed`, and other relevant fields. 
     * - `quiz_id`, `student_id`: Optional, but must be valid integers and exist in their respective tables if provided.
     * - `attempt_number`: Optional, but if provided, it must be an integer greater than or equal to 1.
     * - `status`: Optional, but if provided, must be one of the valid values: `in_progress`, `submitted`, `graded`.
     * - `score`: Optional, but if provided, it must be a non-negative integer.
     * - `is_passed`: Optional, but if provided, it must be a boolean value.
     * - `start_at`, `ends_at`, `submitted_at`, `graded_at`: Optional, but if provided, they must be valid dates.
     * - `graded_by`: Optional, but if provided, it must be a valid user ID from the `users` table.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'quiz_id' => ['sometimes', 'integer', 'exists:quizzes,id'],
            'student_id' => ['sometimes', 'integer', 'exists:users,id'],
            'attempt_number' => ['sometimes', 'integer', 'min:1'],

            'status' => ['required', 'string', 'in:in_progress,submitted,graded'],
            'score' => ['sometimes', 'integer', 'min:0'],
            'is_passed' => ['sometimes', 'boolean'],

            'submitted_at' => ['sometimes', 'nullable', 'date'],
            'graded_at' => ['sometimes', 'nullable', 'date'],
            'graded_by' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
