<?php

namespace Modules\ReportingModule\Builders;

use Modules\LearningModule\Builders\EnrollmentBuilder;

/**
 * EnrollmentReportFilter
 *
 * Helper class to apply enrollment reporting filters.
 * Updated to use students and course categories.
 */
class EnrollmentReportFilter
{
    public static function apply(EnrollmentBuilder $query, array $filters): EnrollmentBuilder
    {
        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (isset($filters['course_id'])) {
            $query->byCourse($filters['course_id']);
        }

        if (isset($filters['enrollment_id'])) {
            $query->where('enrollment_id', $filters['enrollment_id']);
        }

        if (isset($filters['date_from'])) {
            $query->enrolledAfter($filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->enrolledBefore($filters['date_to']);
        }

        if (isset($filters['course_category_id'])) {
            $query->whereHas('course', function ($q) use ($filters) {
                $q->where('course_category_id', $filters['course_category_id']);
            });
        }

        return $query;
    }
}
