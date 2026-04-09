<?php

namespace Modules\LearningModule\Builders;

use Illuminate\Database\Eloquent\Builder;
use Modules\LearningModule\Enums\EnrollmentStatus;

/**
 * EnrollmentBuilder
 *
 * Purpose: Custom query builder for Enrollment model that encapsulates all query logic,
 * filters, and scopes. This follows the Builder pattern to keep query logic
 * separate from the model, improving maintainability and testability.
 *
 */
class EnrollmentBuilder extends Builder
{
    // ============================================
    // SCOPE METHODS - Status Filtering
    // ============================================

    /**
     * Filter enrollments by status
     *
     * @param EnrollmentStatus|string $status
     * @return self
     */
    public function byStatus(EnrollmentStatus|string $status): self
    {
        $statusValue = $status instanceof EnrollmentStatus ? $status->value : $status;
        return $this->where('enrollment_status', $statusValue);
    }

    /**
     * Filter enrollments by multiple statuses
     *
     * @param array<EnrollmentStatus|string> $statuses
     * @return self
     */
    public function byStatuses(array $statuses): self
    {
        $statusValues = array_map(
            fn($status) => $status instanceof EnrollmentStatus ? $status->value : $status,
            $statuses
        );

        return $this->whereIn('enrollment_status', $statusValues);
    }

    /**
     * Filter only active enrollments
     *
     * @return self
     */
    public function active(): self
    {
        return $this->where('enrollment_status', EnrollmentStatus::ACTIVE->value);
    }

    /**
     * Filter only completed enrollments
     *
     * @return self
     */
    public function completed(): self
    {
        return $this->where('enrollment_status', EnrollmentStatus::COMPLETED->value);
    }

    /**
     * Filter only dropped enrollments
     *
     * @return self
     */
    public function dropped(): self
    {
        return $this->where('enrollment_status', EnrollmentStatus::DROPPED->value);
    }

    /**
     * Filter only suspended enrollments
     *
     * @return self
     */
    public function suspended(): self
    {
        return $this->where('enrollment_status', EnrollmentStatus::SUSPENDED->value);
    }

    /**
     * Filter enrollments not completed
     *
     * @return self
     */
    public function notCompleted(): self
    {
        return $this->where('enrollment_status', '!=', EnrollmentStatus::COMPLETED->value);
    }

    // ============================================
    // SCOPE METHODS - Learner & Course Filtering
    // ============================================

    /**
     * Filter enrollments by learner
     *
     * @param int $learnerId
     * @return self
     */
    public function byLearner(int $learnerId): self
    {
        return $this->where('learner_id', $learnerId);
    }

    /**
     * Filter enrollments by course
     *
     * @param int $courseId
     * @return self
     */
    public function byCourse(int $courseId): self
    {
        return $this->where('course_id', $courseId);
    }

    /**
     * Filter enrollments by enrollment type
     *
     * @param string $type 'self' or 'assigned'
     * @return self
     */
    public function byType(string $type): self
    {
        return $this->where('enrollment_type', $type);
    }

    /**
     * Filter only self-enrolled enrollments
     *
     * @return self
     */
    public function selfEnrolled(): self
    {
        return $this->where('enrollment_type', 'self');
    }

    /**
     * Filter only assigned enrollments
     *
     * @return self
     */
    public function assigned(): self
    {
        return $this->where('enrollment_type', 'assigned');
    }

    /**
     * Filter enrollments by user who enrolled them
     *
     * @param int $enrolledById
     * @return self
     */
    public function enrolledBy(int $enrolledById): self
    {
        return $this->where('enrolled_by', $enrolledById);
    }

    // ============================================
    // SCOPE METHODS - Progress Filtering
    // ============================================

    /**
     * Filter enrollments by minimum progress percentage
     *
     * @param float $percentage
     * @return self
     */
    public function minProgress(float $percentage): self
    {
        return $this->where('progress_percentage', '>=', $percentage);
    }

    /**
     * Filter enrollments by maximum progress percentage
     *
     * @param float $percentage
     * @return self
     */
    public function maxProgress(float $percentage): self
    {
        return $this->where('progress_percentage', '<=', $percentage);
    }

    /**
     * Filter enrollments within progress range
     *
     * @param float $min
     * @param float $max
     * @return self
     */
    public function progressBetween(float $min, float $max): self
    {
        return $this->whereBetween('progress_percentage', [$min, $max]);
    }

    /**
     * Filter not started enrollments (0% progress)
     *
     * @return self
     */
    public function notStarted(): self
    {
        return $this->where('progress_percentage', 0);
    }

    /**
     * Filter in-progress enrollments (1-99% progress)
     *
     * @return self
     */
    public function inProgress(): self
    {
        return $this->whereBetween('progress_percentage', [0.01, 99.99]);
    }

    // ============================================
    // SCOPE METHODS - Final Grade Filtering
    // ============================================

    /**
     * Filter enrollments by minimum final grade
     *
     * @param float $grade
     * @return self
     */
    public function minFinalGrade(float $grade): self
    {
        return $this->where('final_grade', '>=', $grade);
    }

    /**
     * Filter enrollments by maximum final grade
     *
     * @param float $grade
     * @return self
     */
    public function maxFinalGrade(float $grade): self
    {
        return $this->where('final_grade', '<=', $grade);
    }

    /**
     * Filter enrollments within final grade range
     *
     * @param float $min
     * @param float $max
     * @return self
     */
    public function finalGradeBetween(float $min, float $max): self
    {
        return $this->whereBetween('final_grade', [$min, $max]);
    }

    /**
     * Filter enrollments with final grade set (not null)
     *
     * @return self
     */
    public function withFinalGrade(): self
    {
        return $this->whereNotNull('final_grade');
    }

    /**
     * Filter enrollments without final grade (null)
     *
     * @return self
     */
    public function withoutFinalGrade(): self
    {
        return $this->whereNull('final_grade');
    }

    // ============================================
    // SCOPE METHODS - Date Filtering
    // ============================================

    /**
     * Filter enrollments enrolled after date
     *
     * @param \DateTime|string $date
     * @return self
     */
    public function enrolledAfter(\DateTime|string $date): self
    {
        return $this->where('enrolled_at', '>=', $date);
    }

    /**
     * Filter enrollments enrolled before date
     *
     * @param \DateTime|string $date
     * @return self
     */
    public function enrolledBefore(\DateTime|string $date): self
    {
        return $this->where('enrolled_at', '<=', $date);
    }

    /**
     * Filter enrollments completed after date
     *
     * @param \DateTime|string $date
     * @return self
     */
    public function completedAfter(\DateTime|string $date): self
    {
        return $this->where('completed_at', '>=', $date);
    }

    /**
     * Filter enrollments completed before date
     *
     * @param \DateTime|string $date
     * @return self
     */
    public function completedBefore(\DateTime|string $date): self
    {
        return $this->where('completed_at', '<=', $date);
    }

    /**
     * Filter recently enrolled (last N days)
     *
     * @param int $days
     * @return self
     */
    public function recentlyEnrolled(int $days = 7): self
    {
        return $this->where('enrolled_at', '>=', now()->subDays($days));
    }

    // ============================================
    // RELATIONSHIP LOADING - Eager Loading
    // ============================================

    /**
     * Load all related relationships
     *
     * @return self
     */
    public function withRelations(): self
    {
        return $this->with(['learner', 'course', 'enrolledBy']);
    }

    /**
     * Load learner relationship
     *
     * @return self
     */
    public function withLearner(): self
    {
        return $this->with('learner');
    }

    /**
     * Load course relationship
     *
     * @return self
     */
    public function withCourse(): self
    {
        return $this->with('course');
    }

    /**
     * Load enrolled by relationship
     *
     * @return self
     */
    public function withEnrolledBy(): self
    {
        return $this->with('enrolledBy');
    }

    // ============================================
    // FILTERING FROM REQUEST
    // ============================================

    /**
     * Apply filters from request parameters.
     *
     * Supported query parameters:
     * - learner_id: int - Filter by learner
     * - course_id: int - Filter by course
     * - status: string - Filter by enrollment status
     * - type: string - Filter by enrollment type (self|assigned)
     * - search: string - Search in learner/course names (requires join)
     * - min_progress: float - Minimum progress percentage
     * - max_progress: float - Maximum progress percentage
     * - min_final_grade: float - Minimum final grade
     * - max_final_grade: float - Maximum final grade
     * - enrolled_after: date - Filter enrollments after this date
     * - enrolled_before: date - Filter enrollments before this date
     *
     * @param \Illuminate\Http\Request $request
     * @return self
     */
    public function filterByRequest($request): self
    {
        // Filter by learner_id
        if ($request->filled('learner_id')) {
            $this->byLearner((int)$request->get('learner_id'));
        }

        // Filter by course_id
        if ($request->filled('course_id')) {
            $this->byCourse((int)$request->get('course_id'));
        }

        // Filter by enrollment status
        if ($request->filled('status')) {
            $this->byStatus($request->get('status'));
        }

        // Filter by enrollment type
        if ($request->filled('type')) {
            $this->byType($request->get('type'));
        }

        // Filter by progress range
        if ($request->filled('min_progress')) {
            $this->minProgress((float)$request->get('min_progress'));
        }

        if ($request->filled('max_progress')) {
            $this->maxProgress((float)$request->get('max_progress'));
        }

        // Filter by final grade range
        if ($request->filled('min_final_grade')) {
            $this->minFinalGrade((float)$request->get('min_final_grade'));
        }

        if ($request->filled('max_final_grade')) {
            $this->maxFinalGrade((float)$request->get('max_final_grade'));
        }

        // Filter by enrollment date range
        if ($request->filled('enrolled_after')) {
            $this->enrolledAfter($request->get('enrolled_after'));
        }

        if ($request->filled('enrolled_before')) {
            $this->enrolledBefore($request->get('enrolled_before'));
        }

        // Search in related data (learner name, course title)
        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $this->where(function ($query) use ($searchTerm) {
                $query->whereHas('learner', function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('email', 'like', "%{$searchTerm}%");
                })
                    ->orWhereHas('course', function ($q) use ($searchTerm) {
                        $q->where('title', 'like', "%{$searchTerm}%");
                    });
            });
        }

        return $this;
    }

    // ============================================
    // SORTING & ORDERING
    // ============================================

    /**
     * Apply sorting from request.
     *
     * Supported parameters:
     * - sort: Field name to sort by (default: enrollment_id)
     * - direction: asc|desc (default: desc)
     *
     * @param $request
     * @return self
     */
    public function ordered($request = null): self
    {
        $sortField = $request?->get('sort') ?? 'enrollment_id';
        $direction = $request?->get('direction') ?? 'desc';

        // Whitelist allowed sort fields for security
        $allowedFields = [
            'enrollment_id',
            'learner_id',
            'course_id',
            'enrollment_type',
            'enrollment_status',
            'progress_percentage',
            'final_grade',
            'enrolled_at',
            'completed_at',
            'created_at',
            'updated_at',
        ];

        if (in_array($sortField, $allowedFields)) {
            $this->orderBy($sortField, $direction);
        } else {
            $this->orderBy('enrollment_id', 'desc');
        }

        return $this;
    }

    // ============================================
    // PAGINATION
    // ============================================

    /**
     * Paginate from request.
     *
     * Supported parameters:
     * - per_page: Items per page (default: 15)
     * - page: Page number (default: 1)
     *
     * @param $request
     * @return \Illuminate\Pagination\Paginator
     */
    public function paginateFromRequest($request)
    {
        $perPage = (int)($request?->get('per_page') ?? 15);
        $perPage = min($perPage, 100); // Max 100 items per page

        return $this->paginate($perPage);
    }
}
