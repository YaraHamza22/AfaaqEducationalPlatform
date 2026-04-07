<?php

namespace Modules\LearningModule\Http\Requests\Enrollment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for updating an existing enrollment.
 * Handles validation for enrollment updates.
 *
 * Validates:
 * - Enrollment type is valid (if updating)
 * - Progress percentage is within valid range (0-100)
 * - Final grade is within valid range (0-100)
 * - Only allows updating specific fields
 */
class UpdateEnrollmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Authorization logic can be added here
        // For example: check if user is admin or is the learner themselves
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
            // Enrollment type can be updated
            'enrollment_type' => [
                'nullable',
                'string',
                Rule::in(['self', 'assigned']),
            ],

            // Progress percentage can be updated
            'progress_percentage' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
                'decimal:0,2',
            ],
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
            'enrollment_type.string' => 'The enrollment type must be a string.',
            'enrollment_type.in' => 'The enrollment type must be either "self" or "assigned".',

            'progress_percentage.numeric' => 'The progress percentage must be a number.',
            'progress_percentage.min' => 'The progress percentage cannot be less than 0.',
            'progress_percentage.max' => 'The progress percentage cannot be greater than 100.',
            'progress_percentage.decimal' => 'The progress percentage can have at most 2 decimal places.',

            'final_grade.numeric' => 'The final grade must be a number.',
            'final_grade.min' => 'The final grade cannot be less than 0.',
            'final_grade.max' => 'The final grade cannot be greater than 100.',
            'final_grade.decimal' => 'The final grade can have at most 2 decimal places.',
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
            'enrollment_type' => 'enrollment type',
            'progress_percentage' => 'progress percentage',
            'final_grade' => 'final grade',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Convert progress_percentage to float if provided
        if ($this->has('progress_percentage')) {
            $this->merge(['progress_percentage' => (float)$this->progress_percentage]);
        }

        // Convert final_grade to float if provided
        if ($this->has('final_grade')) {
            $this->merge(['final_grade' => (float)$this->final_grade]);
        }
    }

    /**
     * Get only the validated data from the request.
     * Filters out null values and empty fields.
     *
     * @return array
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        // Filter out null and empty values to only update provided fields
        return array_filter($validated, fn($value) => $value !== null);
    }
}
