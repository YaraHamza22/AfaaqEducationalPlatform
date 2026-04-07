<?php

namespace Modules\LearningModule\Http\Requests\Lesson;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for reordering lessons within a unit.
 * Handles validation for lesson reordering.
 */
class ReorderLessonsRequest extends FormRequest
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
            'lesson_orders' => ['required', 'array', 'min:1'],
            'lesson_orders.*' => ['required', 'integer', 'min:1'],
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
            'lesson_orders.required' => 'Lesson orders are required.',
            'lesson_orders.array' => 'Lesson orders must be an array.',
            'lesson_orders.min' => 'At least one lesson order must be provided.',
            'lesson_orders.*.required' => 'Each lesson order is required.',
            'lesson_orders.*.integer' => 'Each lesson order must be an integer.',
            'lesson_orders.*.min' => 'Each lesson order must be at least 1.',
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
            'lesson_orders' => 'lesson orders',
        ];
    }
}
