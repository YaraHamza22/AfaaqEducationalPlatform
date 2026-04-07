<?php

namespace Modules\ReportingModule\Builders;

use Modules\LearningModule\Builders\CourseBuilder;

/**
 * CourseReportFilter
 *
 * Helper class to apply course reporting filters.
 * Updated after removing programs and renaming course type to course category.
 */
class CourseReportFilter
{
    public static function apply(CourseBuilder $query, array $filters): CourseBuilder
    {
        if (isset($filters['course_category_id'])) {
            $query->where('course_category_id', $filters['course_category_id']);
        }

        if (isset($filters['date_from'])) {
            $query->where('published_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('published_at', '<=', $filters['date_to']);
        }

        return $query;
    }
}
