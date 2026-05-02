<?php

namespace Modules\ReportingModule\Http\Requests\ActivityLog;

use App\Http\Requests\ApiFormRequest;

class ActivityLogIndexRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'log_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'event' => ['sometimes', 'nullable', 'string', 'max:255'],
            'subject_type' => ['sometimes', 'nullable', 'string', 'max:500'],
            'subject_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'causer_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'from' => ['sometimes', 'nullable', 'date'],
            'to' => ['sometimes', 'nullable', 'date', 'after_or_equal:from'],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }
}
