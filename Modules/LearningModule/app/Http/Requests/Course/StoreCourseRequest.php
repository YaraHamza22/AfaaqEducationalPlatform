<?php

namespace Modules\LearningModule\Http\Requests\Course;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for storing a new course.
 * Handles validation for course creation.
 * Translatable fields (title, description, objectives, prerequisites) accept string or array with en/ar keys.
 */
class StoreCourseRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
        return [
            'title' => ['required', 'array'],
            'title.en' => ['required_without:title.ar', 'nullable', 'string', 'max:255'],
            'title.ar' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:courses,slug'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],
            'objectives' => ['nullable', 'array'],
            'objectives.en' => ['nullable', 'string'],
            'objectives.ar' => ['nullable', 'string'],
            'prerequisites' => ['nullable', 'array'],
            'prerequisites.en' => ['nullable', 'string'],
            'prerequisites.ar' => ['nullable', 'string'],
            'course_category_id' => ['required', 'integer', 'exists:course_categories,course_category_id'],
            'actual_duration_hours' => ['required', 'integer', 'min:1'],
            'language' => ['nullable', 'string', 'max:10', Rule::in(['ar', 'en'])],
            'status' => ['nullable', 'string', Rule::in(['draft', 'review', 'published', 'archived'])],
            'min_score_to_pass' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_offline_available' => ['nullable', 'boolean'],
            'course_delivery_type' => ['nullable', 'string', Rule::in(['self_paced', 'interactive', 'hybrid'])],
            'difficulty_level' => ['nullable', 'string', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'cover' => 'nullable|image|max:10240',
            'intro_video' => 'nullable|mimes:mp4,mov|max:2097152',
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
            'allocated_budget' => 'allocated budget',
            'required_budget' => 'required budget',
            'min_score_to_pass' => 'minimum score to pass',
            'is_offline_available' => 'offline availability',
            'course_delivery_type' => 'delivery type',
            'difficulty_level' => 'difficulty level',
        ];
    }
}
