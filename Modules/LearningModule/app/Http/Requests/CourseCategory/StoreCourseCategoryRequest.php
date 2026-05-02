<?php

namespace Modules\LearningModule\Http\Requests\CourseCategory;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for storing a new course category.
 * Translatable fields (name, description) accept string or array with en/ar keys.
 */
class StoreCourseCategoryRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
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
        return [
            'name' => ['required', 'array'],
            'name.en' => ['required_without:name.ar', 'nullable', 'string', 'max:100'],
            'name.ar' => ['nullable', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100', 'unique:course_categories,slug'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'target_audience' => ['nullable', 'string'],
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
