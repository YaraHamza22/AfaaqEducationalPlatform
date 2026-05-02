<?php

namespace Modules\ReportingModule\Http\Requests\Report;

use App\Http\Requests\ApiFormRequest;

/**
 * Form request for generating student performance reports.
 */
class GenerateStudentReportRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['nullable', 'integer', 'exists:users,id'],
            'course_id' => ['nullable', 'integer', 'exists:courses,course_id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.integer' => 'The student ID must be an integer.',
            'student_id.exists' => 'The selected student does not exist.',
            'course_id.integer' => 'The course ID must be an integer.',
            'course_id.exists' => 'The selected course does not exist.',
            'date_from.date' => 'The date from must be a valid date.',
            'date_to.date' => 'The date to must be a valid date.',
            'date_to.after_or_equal' => 'The date to must be after or equal to date from.',
        ];
    }
}
