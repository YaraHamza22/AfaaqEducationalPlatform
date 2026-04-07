<?php

namespace Modules\LearningModule\Http\Requests\Unit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for filtering units.
 * Handles validation for unit filtering parameters.
 */
class FilterUnitsRequest extends FormRequest
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
     * Prepare the data for validation.
     * Converts query string "true"/"false" to booleans so Laravel's boolean rule accepts them.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('has_lessons') && is_string($this->has_lessons)) {
            $value = filter_var($this->has_lessons, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($value !== null) {
                $this->merge(['has_lessons' => $value]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'course_id' => ['nullable', 'integer', 'exists:courses,course_id'],
            'unit_order' => ['nullable', 'integer', 'min:1'],
            'has_lessons' => ['nullable', 'boolean'],
            'search' => ['nullable', 'string', 'max:255'],
            'order_by' => ['nullable', 'string', Rule::in(['unit_order', 'created_at'])],
            'order_direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
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
            'course_id.integer' => 'The course ID must be an integer.',
            'course_id.exists' => 'The selected course does not exist.',
            'unit_order.integer' => 'The unit order must be an integer.',
            'unit_order.min' => 'The unit order must be at least 1.',
            'has_lessons.boolean' => 'The has lessons status must be true or false.',
            'search.string' => 'The search term must be a string.',
            'search.max' => 'The search term may not be greater than 255 characters.',
            'order_by.in' => 'The order by field must be either unit_order or created_at.',
            'order_direction.in' => 'The order direction must be either asc or desc.',
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
            'course_id' => 'course',
            'unit_order' => 'unit order',
            'has_lessons' => 'has lessons',
            'order_by' => 'order by',
            'order_direction' => 'order direction',
            'per_page' => 'per page',
        ];
    }
}
