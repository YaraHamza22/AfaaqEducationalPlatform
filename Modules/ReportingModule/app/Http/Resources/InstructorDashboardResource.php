<?php

namespace Modules\ReportingModule\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorDashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'summary' => $this->resource['summary'] ?? [],
            'course_statistics' => $this->resource['course_statistics'] ?? [],
            'top_performing_courses' => $this->resource['top_performing_courses'] ?? [],
        ];
    }
}
