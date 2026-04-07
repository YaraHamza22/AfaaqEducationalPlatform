<?php

namespace Modules\ReportingModule\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoursePopularityReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'total_courses' => $this->resource['total_courses'] ?? 0,
            'total_enrollments' => $this->resource['total_enrollments'] ?? 0,
            'popular_courses' => $this->resource['popular_courses'] ?? [],
            'popularity_by_course_category' => $this->resource['popularity_by_course_category'] ?? [],
        ];
    }
}
