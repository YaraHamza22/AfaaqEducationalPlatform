<?php

namespace Modules\LearningModule\Http\Requests\Enrollment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\LearningModule\Models\Course;
use App\Models\User;

/**
 * Form request for storing a new enrollment.
 * Handles validation for enrollment creation.
 *
 * Validates:
 * - Learner exists as a user
 * - Course exists and is available for enrollment
 * - User is not already enrolled in the course
 * - Enrollment type is valid
 */
class StoreEnrollmentRequest extends FormRequest
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
            // Learner validation
            'learner_id' => [
                'required',
                'integer',
                'exists:users,id',
                Rule::unique('enrollments', 'learner_id')
                    ->where('course_id', $this->get('course_id'))
                    ->whereIn('enrollment_status', ['active', 'suspended']),
            ],

            // Course validation
            'course_id' => [
                'required',
                'integer',
                'exists:courses,course_id',
            ],

            // Enrollment type validation
            'enrollment_type' => [
                'required',
                'string',
                Rule::in(['self', 'assigned']),
            ],

            // Optional enrolled by (for assigned enrollments)
            'enrolled_by' => [
                'nullable',
                'integer',
                'exists:users,id',
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
            'learner_id.required' => 'The learner ID is required.',
            'learner_id.integer' => 'The learner ID must be an integer.',
            'learner_id.exists' => 'The selected learner does not exist.',
            'learner_id.unique' => 'This learner is already enrolled in this course with an active or suspended status.',

            'course_id.required' => 'The course ID is required.',
            'course_id.integer' => 'The course ID must be an integer.',
            'course_id.exists' => 'The selected course does not exist.',

            'enrollment_type.required' => 'The enrollment type is required.',
            'enrollment_type.string' => 'The enrollment type must be a string.',
            'enrollment_type.in' => 'The enrollment type must be either "self" or "assigned".',

            'enrolled_by.integer' => 'The enrolled by ID must be an integer.',
            'enrolled_by.exists' => 'The selected user does not exist.',
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
            'learner_id' => 'learner ID',
            'course_id' => 'course ID',
            'enrollment_type' => 'enrollment type',
            'enrolled_by' => 'enrolled by',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Convert learner_id and course_id to integers if provided
        if ($this->has('learner_id')) {
            $this->merge(['learner_id' => (int)$this->learner_id]);
        }

        if ($this->has('course_id')) {
            $this->merge(['course_id' => (int)$this->course_id]);
        }

        // Only cast enrolled_by when a value is provided (leave null as null for self-enrollment)
        if ($this->filled('enrolled_by')) {
            $this->merge(['enrolled_by' => (int) $this->enrolled_by]);
        }

        // Default enrollment type to 'self' if not provided
        if (!$this->has('enrollment_type')) {
            $this->merge(['enrollment_type' => 'self']);
        }
    }
}
