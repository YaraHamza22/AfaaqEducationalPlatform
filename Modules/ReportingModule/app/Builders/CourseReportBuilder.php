<?php

namespace Modules\ReportingModule\Builders;

use Modules\LearningModule\Builders\CourseBuilder;

/**
 * CourseReportBuilder
 *
 * Reporting-oriented builder for course queries.
 * Updated to work with course categories after removing programs.
 */
class CourseReportBuilder extends CourseBuilder
{
    public function applyReportFilters(array $filters): self
    {
        if (isset($filters['course_category_id'])) {
            $this->where('course_category_id', $filters['course_category_id']);
        }

        if (isset($filters['date_from'])) {
            $this->publishedAfter($filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $this->publishedBefore($filters['date_to']);
        }

        return $this;
    }

    public function publishedAfter(\DateTime|string $date): self
    {
        return $this->where('published_at', '>=', $date);
    }

    public function publishedBefore(\DateTime|string $date): self
    {
        return $this->where('published_at', '<=', $date);
    }

    public function publishedBetween(\DateTime|string $from, \DateTime|string $to): self
    {
        return $this->whereBetween('published_at', [$from, $to]);
    }
}
