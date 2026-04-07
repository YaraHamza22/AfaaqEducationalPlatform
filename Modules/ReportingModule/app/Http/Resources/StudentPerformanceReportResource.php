<?php

namespace Modules\ReportingModule\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentPerformanceReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'total_enrollments' => $this->resource['total_enrollments'] ?? 0,
            'completed_enrollments' => $this->resource['completed_enrollments'] ?? 0,
            'average_progress' => $this->resource['average_progress'] ?? 0,
            'average_completion_time_days' => $this->resource['average_completion_time_days'] ?? 0,
            'performance_by_course' => $this->resource['performance_by_course'] ?? [],
        ];
    }
}
