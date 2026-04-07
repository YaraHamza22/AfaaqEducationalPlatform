<?php

namespace Modules\LearningModule\Http\Requests\Lesson;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for moving a lesson to a specific position.
 * Handles validation for lesson position change.
 */
class MoveLessonRequest extends FormRequest
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
            'lesson_order' => ['required', 'integer', 'min:1'],
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
            'lesson_order.required' => 'Lesson order is required.',
            'lesson_order.integer' => 'Lesson order must be an integer.',
            'lesson_order.min' => 'Lesson order must be at least 1.',
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
            'lesson_order' => 'lesson order',
        ];
    }
}
