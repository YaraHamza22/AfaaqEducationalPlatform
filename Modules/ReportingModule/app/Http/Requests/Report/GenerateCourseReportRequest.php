<?php

namespace Modules\ReportingModule\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for generating course reports.
 * Updated to use course categories instead of course types.
 */
class GenerateCourseReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => ['nullable', 'integer', 'exists:courses,course_id'],
            'course_category_id' => ['nullable', 'integer', 'exists:course_categories,course_category_id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.integer' => 'The course ID must be an integer.',
            'course_id.exists' => 'The selected course does not exist.',
            'course_category_id.integer' => 'The course category ID must be an integer.',
            'course_category_id.exists' => 'The selected course category does not exist.',
            'date_from.date' => 'The date from must be a valid date.',
            'date_to.date' => 'The date to must be a valid date.',
            'date_to.after_or_equal' => 'The date to must be after or equal to date from.',
        ];
    }
}
