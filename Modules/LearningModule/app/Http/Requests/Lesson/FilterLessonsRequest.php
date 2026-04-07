<?php

namespace Modules\LearningModule\Http\Requests\Lesson;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for filtering lessons.
 * Handles validation for lesson filtering parameters.
 */
class FilterLessonsRequest extends FormRequest
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
            'unit_id' => ['nullable', 'integer', 'exists:units,unit_id'],
            'lesson_order' => ['nullable', 'integer', 'min:1'],
            'lesson_type' => ['nullable', 'string', 'max:255'],
            'is_required' => ['nullable', 'boolean'],
            'search' => ['nullable', 'string', 'max:255'],
            'order_by' => ['nullable', 'string', Rule::in(['lesson_order', 'created_at'])],
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
            'unit_id.integer' => 'The unit ID must be an integer.',
            'unit_id.exists' => 'The selected unit does not exist.',
            'lesson_order.integer' => 'The lesson order must be an integer.',
            'lesson_order.min' => 'The lesson order must be at least 1.',
            'lesson_type.string' => 'The lesson type must be a string.',
            'lesson_type.max' => 'The lesson type may not be greater than 255 characters.',
            'is_required.boolean' => 'The required status must be true or false.',
            'search.string' => 'The search term must be a string.',
            'search.max' => 'The search term may not be greater than 255 characters.',
            'order_by.in' => 'The order by field must be either lesson_order or created_at.',
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
            'unit_id' => 'unit',
            'lesson_order' => 'lesson order',
            'lesson_type' => 'lesson type',
            'is_required' => 'required status',
            'order_by' => 'order by',
            'order_direction' => 'order direction',
            'per_page' => 'per page',
        ];
    }
}
