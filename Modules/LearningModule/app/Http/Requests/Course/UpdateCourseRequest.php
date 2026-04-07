<?php

namespace Modules\LearningModule\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\LearningModule\Models\Course;

/**
 * Form request for updating an existing course.
 * Translatable fields accept string or array with en/ar keys.
 */
class UpdateCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Authorization logic can be added here
        // For example: return $this->user()->can('update', $this->route('course'));
        return true;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $translatable = ['title', 'description', 'objectives', 'prerequisites'];
        foreach ($translatable as $key) {
            if ($this->has($key) && is_string($this->input($key))) {
                $this->merge([$key => ['en' => $this->input($key)]]);
            }
        }
    }

    public function rules(): array
    {
        $courseId = $this->route('course');

        // Get course ID from route parameter (could be ID or model instance)
        $courseId = $courseId instanceof Course ? $courseId->course_id : $courseId;

        return [
            'title' => ['sometimes', 'required', 'array'],
            'title.en' => ['nullable', 'string', 'max:255'],
            'title.ar' => ['nullable', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                Rule::unique('courses', 'slug')->ignore($courseId, 'course_id')
            ],
            'description' => ['sometimes', 'nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],
            'objectives' => ['sometimes', 'nullable', 'array'],
            'objectives.en' => ['nullable', 'string'],
            'objectives.ar' => ['nullable', 'string'],
            'prerequisites' => ['sometimes', 'nullable', 'array'],
            'prerequisites.en' => ['nullable', 'string'],
            'prerequisites.ar' => ['nullable', 'string'],
            'course_category_id' => ['sometimes', 'required', 'integer', 'exists:course_categories,course_category_id'],
            'actual_duration_hours' => ['sometimes', 'required', 'integer', 'min:1'],
            'language' => ['sometimes', 'nullable', 'string', 'max:10', Rule::in(['ar', 'en'])],
            'status' => ['sometimes', 'nullable', 'string', Rule::in(['draft', 'review', 'published', 'archived'])],
            'min_score_to_pass' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'is_offline_available' => ['sometimes', 'nullable', 'boolean'],
            'course_delivery_type' => ['sometimes', 'nullable', 'string', Rule::in(['self_paced', 'interactive', 'hybrid'])],
            'difficulty_level' => ['sometimes', 'nullable', 'string', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'cover' => 'nullable|image|max:2048',
            'intro_video' => 'nullable|mimes:mp4,mov|max:20480',
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
            'title.required' => 'The course title is required.',
            'title.max' => 'The course title may not be greater than 255 characters.',
            'slug.unique' => 'This slug is already taken. Please choose a different one.',
            'course_category_id.required' => 'Please select a course category.',
            'course_category_id.exists' => 'The selected course category does not exist.',
            'actual_duration_hours.required' => 'Actual duration is required.',
            'actual_duration_hours.min' => 'Actual duration must be at least 1 hour.',
            'language.in' => 'Please select a valid language.',
            'status.in' => 'Please select a valid status.',
            'min_score_to_pass.min' => 'Minimum score to pass must be at least 0.',
            'min_score_to_pass.max' => 'Minimum score to pass cannot exceed 100.',
            'course_delivery_type.in' => 'Please select a valid delivery type.',
            'difficulty_level.in' => 'Please select a valid difficulty level.',
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
            'course_category_id' => 'course category',
            'actual_duration_hours' => 'actual duration',
            'min_score_to_pass' => 'minimum score to pass',
            'is_offline_available' => 'offline availability',
            'course_delivery_type' => 'delivery type',
            'difficulty_level' => 'difficulty level',
        ];
    }
}
