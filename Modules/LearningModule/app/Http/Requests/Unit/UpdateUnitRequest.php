<?php

namespace Modules\LearningModule\Http\Requests\Unit;

use Illuminate\Foundation\Http\FormRequest;
use Modules\LearningModule\Models\Unit;

/**
 * Form request for updating an existing unit.
 * Translatable fields accept string or array with en/ar keys.
 */
class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['title', 'description'] as $key) {
            if ($this->has($key) && is_string($this->input($key))) {
                $this->merge([$key => ['en' => $this->input($key)]]);
            }
        }
    }

    public function rules(): array
    {
        $unitId = $this->route('unit');
        $unitId = $unitId instanceof Unit ? $unitId->unit_id : $unitId;

        return [
            'course_id' => ['sometimes', 'required', 'integer', 'exists:courses,course_id'],
            'title' => ['sometimes', 'required', 'array'],
            'title.en' => ['nullable', 'string', 'max:255'],
            'title.ar' => ['nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],
            'unit_order' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'actual_duration_minutes' => ['sometimes', 'required', 'integer', 'min:1'],
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
            'course_id.required' => 'The course is required.',
            'course_id.exists' => 'The selected course does not exist.',
            'title.required' => 'The unit title is required.',
            'title.max' => 'The unit title may not be greater than 255 characters.',
            'unit_order.min' => 'Unit order must be at least 1.',
            'actual_duration_minutes.required' => 'Actual duration is required.',
            'actual_duration_minutes.min' => 'Actual duration must be at least 1 minute.',
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
            'actual_duration_minutes' => 'actual duration',
        ];
    }
}
