<?php

namespace Modules\LearningModule\Http\Requests\Enrollment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\LearningModule\Enums\EnrollmentStatus;

/**
 * Form request for changing enrollment status.
 * Handles validation for enrollment status updates.
 */
class ChangeStatusEnrollmentRequest extends FormRequest
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
            'status' => [
                'required',
                'string',
                Rule::enum(EnrollmentStatus::class),
            ],
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
            'status.required' => 'The enrollment status is required.',
            'status.string' => 'The enrollment status must be a string.',
            'status.enum' => 'The enrollment status must be one of: active, completed, dropped, suspended.',
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
            'status' => 'enrollment status',
        ];
    }
}
