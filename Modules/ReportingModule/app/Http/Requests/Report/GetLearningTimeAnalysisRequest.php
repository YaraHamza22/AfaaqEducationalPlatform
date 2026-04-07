<?php

namespace Modules\ReportingModule\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for getting learning time analysis
 */
class GetLearningTimeAnalysisRequest extends FormRequest
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
            'learner_id' => ['nullable', 'integer', 'exists:users,user_id'],
            'course_id' => ['nullable', 'integer', 'exists:courses,course_id'],
            'enrollment_id' => ['nullable', 'integer', 'exists:enrollments,enrollment_id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
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
            'learner_id.integer' => 'The learner ID must be an integer.',
            'learner_id.exists' => 'The selected learner does not exist.',
            'course_id.integer' => 'The course ID must be an integer.',
            'course_id.exists' => 'The selected course does not exist.',
            'enrollment_id.integer' => 'The enrollment ID must be an integer.',
            'enrollment_id.exists' => 'The selected enrollment does not exist.',
            'date_from.date' => 'The date from must be a valid date.',
            'date_to.date' => 'The date to must be a valid date.',
            'date_to.after_or_equal' => 'The date to must be after or equal to date from.',
        ];
    }
}
