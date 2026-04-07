<?php

namespace Modules\LearningModule\Http\Requests\CourseCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\LearningModule\Models\CourseCategory;

/**
 * Form request for updating an existing course category.
 * Translatable fields accept string or array with en/ar keys.
 */
class UpdateCourseCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['name', 'description'] as $key) {
            if ($this->has($key) && is_string($this->input($key))) {
                $this->merge([$key => ['en' => $this->input($key)]]);
            }
        }
    }

    public function rules(): array
    {
        $courseCategoryId = $this->route('courseCategory') ?? $this->route('course_category');
        $courseCategoryId = $courseCategoryId instanceof CourseCategory ? $courseCategoryId->course_category_id : $courseCategoryId;

        return [
            'name' => ['sometimes', 'required', 'array'],
            'name.en' => ['nullable', 'string', 'max:100'],
            'name.ar' => ['nullable', 'string', 'max:100'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:100', Rule::unique('course_categories', 'slug')->ignore($courseCategoryId, 'course_category_id')],
            'description' => ['sometimes', 'nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'nullable', 'boolean'],
            'target_audience' => ['sometimes', 'nullable', 'string'],
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
            'name.required' => 'The course category name is required.',
            'name.max' => 'The course category name may not be greater than 100 characters.',
            'name.unique' => 'This course category name is already taken.',
            'slug.unique' => 'This slug is already taken.',
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
            'is_active' => 'active status',
            'target_audience' => 'target audience',
        ];
    }
}
