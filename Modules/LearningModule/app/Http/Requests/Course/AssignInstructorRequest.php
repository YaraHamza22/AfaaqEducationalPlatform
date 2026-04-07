<?php

namespace Modules\LearningModule\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for assigning an instructor to a course.
 * Handles validation for instructor assignment.
 */
class AssignInstructorRequest extends FormRequest
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
        $courseId = $this->route('course');
        $courseId = is_object($courseId) ? $courseId->course_id : $courseId;

        return [
            'instructor_id' => [
                'required',
                'integer',
                'exists:users,id',
                Rule::unique('course_instructor', 'instructor_id')
                    ->where('course_id', $courseId)
            ],
            'is_primary' => ['nullable', 'boolean'],
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
            'instructor_id.unique' => 'This instructor is already assigned to this course.',
            'is_primary.boolean' => 'The primary flag must be true or false.',
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
            'is_primary' => 'primary instructor',
        ];
    }
}
