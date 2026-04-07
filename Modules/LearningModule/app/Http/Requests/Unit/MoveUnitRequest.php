<?php

namespace Modules\LearningModule\Http\Requests\Unit;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for moving a unit to a specific position.
 * Handles validation for unit position change.
 */
class MoveUnitRequest extends FormRequest
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
            'unit_order' => ['required', 'integer', 'min:1'],
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
            'unit_order.required' => 'Unit order is required.',
            'unit_order.integer' => 'Unit order must be an integer.',
            'unit_order.min' => 'Unit order must be at least 1.',
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
            'unit_order' => 'unit order',
        ];
    }
}
