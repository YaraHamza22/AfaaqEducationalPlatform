<?php

namespace Modules\LearningModule\Http\Requests\Unit;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for reordering units within a course.
 * Handles validation for unit reordering.
 */
class ReorderUnitsRequest extends FormRequest
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
            'unit_orders' => ['required', 'array', 'min:1'],
            'unit_orders.*' => ['required', 'integer', 'min:1'],
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
            'unit_orders.required' => 'Unit orders are required.',
            'unit_orders.array' => 'Unit orders must be an array.',
            'unit_orders.min' => 'At least one unit order must be provided.',
            'unit_orders.*.required' => 'Each unit order is required.',
            'unit_orders.*.integer' => 'Each unit order must be an integer.',
            'unit_orders.*.min' => 'Each unit order must be at least 1.',
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
            'unit_orders' => 'unit orders',
        ];
    }
}
