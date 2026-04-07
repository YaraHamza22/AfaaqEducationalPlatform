<?php

namespace Modules\ReportingModule\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminDashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'summary' => $this->resource['summary'] ?? [],
            'popular_courses' => $this->resource['popular_courses'] ?? [],
            'learning_gaps' => $this->resource['learning_gaps'] ?? [],
            'course_analytics' => [
                'popularity_report' => $this->resource['course_analytics']['popularity_report'] ?? [],
            ],
            'student_analytics' => [
                'performance_report' => $this->resource['student_analytics']['performance_report'] ?? [],
                'completion_rates' => $this->resource['student_analytics']['completion_rates'] ?? [],
                'learning_time_analysis' => $this->resource['student_analytics']['learning_time_analysis'] ?? [],
            ],
        ];
    }
}
