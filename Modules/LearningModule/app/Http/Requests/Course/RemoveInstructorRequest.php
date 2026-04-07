<?php

namespace Modules\LearningModule\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for removing an instructor from a course.
 * Handles validation for instructor removal.
 */
class RemoveInstructorRequest extends FormRequest
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
            'instructor_id' => ['required', 'integer', 'exists:users,id'],
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
            'instructor_id.required' => 'The instructor is required.',
            'instructor_id.exists' => 'The selected instructor does not exist.',
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
            'instructor_id' => 'instructor',
        ];
    }
}
