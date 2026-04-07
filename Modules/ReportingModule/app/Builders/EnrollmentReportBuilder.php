<?php

namespace Modules\ReportingModule\Builders;

use Modules\LearningModule\Builders\EnrollmentBuilder;

/**
 * EnrollmentReportBuilder
 *
 * Reporting builder for enrollment queries.
 * Updated to use students and course categories.
 */
class EnrollmentReportBuilder extends EnrollmentBuilder
{
    public function applyReportFilters(array $filters): self
    {
        if (isset($filters['student_id'])) {
            $this->where('student_id', $filters['student_id']);
        }

        if (isset($filters['course_id'])) {
            $this->byCourse($filters['course_id']);
        }

        if (isset($filters['enrollment_id'])) {
            $this->where('enrollment_id', $filters['enrollment_id']);
        }

        if (isset($filters['date_from'])) {
            $this->enrolledAfter($filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $this->enrolledBefore($filters['date_to']);
        }

        if (isset($filters['course_category_id'])) {
            $this->byCourseCategory($filters['course_category_id']);
        }

        return $this;
    }

    public function byCourseCategory(int $courseCategoryId): self
    {
        return $this->whereHas('course', function ($query) use ($courseCategoryId) {
            $query->where('course_category_id', $courseCategoryId);
        });
    }
}
