<?php

namespace Modules\ReportingModule\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Student Dashboard API Resource
 * Transforms student dashboard data into a consistent JSON structure
 */
class StudentDashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'summary' => $this->resource['summary'] ?? [],
            'recent_courses' => $this->resource['recent_courses'] ?? [],
            'progress_by_course' => $this->resource['progress_by_course'] ?? [],
        ];
    }
}
