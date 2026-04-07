<?php

namespace Modules\LearningModule\Http\Requests\Enrollment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for filtering enrollments.
 * Handles validation for enrollment filtering parameters.
 */
class FilterEnrollmentsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'learner_id' => ['nullable', 'integer'],
            'course_id' => ['nullable', 'integer', 'exists:courses,course_id'],
            'status' => ['nullable', 'string', Rule::in(['active', 'completed', 'dropped', 'suspended'])],
            'type' => ['nullable', 'string', Rule::in(['self', 'assigned'])],
            'search' => ['nullable', 'string', 'max:255'],
            'min_progress' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'max_progress' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'min_final_grade' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'max_final_grade' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'enrolled_after' => ['nullable', 'date'],
            'enrolled_before' => ['nullable', 'date', 'after_or_equal:enrolled_after'],
            'sort' => ['nullable', 'string', 'max:255'],
            'direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'learner_id.integer' => 'The learner ID must be an integer.',
            'course_id.integer' => 'The course ID must be an integer.',
            'course_id.exists' => 'The selected course does not exist.',
            'status.in' => 'Please select a valid enrollment status.',
            'type.in' => 'Please select a valid enrollment type.',
            'search.string' => 'The search term must be a string.',
            'search.max' => 'The search term may not be greater than 255 characters.',
            'min_progress.numeric' => 'The minimum progress must be a number.',
            'min_progress.min' => 'The minimum progress must be at least 0.',
            'min_progress.max' => 'The minimum progress cannot exceed 100.',
            'max_progress.numeric' => 'The maximum progress must be a number.',
            'max_progress.min' => 'The maximum progress must be at least 0.',
            'max_progress.max' => 'The maximum progress cannot exceed 100.',
            'enrolled_after.date' => 'The enrolled after date must be a valid date.',
            'enrolled_before.date' => 'The enrolled before date must be a valid date.',
            'enrolled_before.after_or_equal' => 'The enrolled before date must be after or equal to enrolled after date.',
            'sort.string' => 'The sort field must be a string.',
            'direction.in' => 'The direction must be either asc or desc.',
            'per_page.integer' => 'The per page value must be an integer.',
            'per_page.min' => 'The per page value must be at least 1.',
            'per_page.max' => 'The per page value cannot exceed 100.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'learner_id' => 'learner',
            'course_id' => 'course',
            'min_progress' => 'minimum progress',
            'max_progress' => 'maximum progress',
            'min_final_grade' => 'minimum final grade',
            'max_final_grade' => 'maximum final grade',
            'enrolled_after' => 'enrolled after',
            'enrolled_before' => 'enrolled before',
            'per_page' => 'per page',
        ];
    }
}
