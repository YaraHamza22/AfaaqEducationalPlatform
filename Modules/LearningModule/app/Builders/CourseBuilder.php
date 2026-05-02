<?php

namespace Modules\LearningModule\Builders;

use Illuminate\Database\Eloquent\Builder;
use Modules\LearningModule\Enums\CourseStatus;

/**
 * CourseBuilder
 *
 * Purpose: Custom query builder for Course model that encapsulates all query logic,
 * filters, and scopes. This follows the Builder pattern to keep query logic
 * separate from the model, improving maintainability and testability.
 *
 * Benefits:
 * - Centralized query logic
 * - Reusable filter methods
 * - Type-safe query building
 * - Easy to test
 * - Clean separation of concerns
 *
 * Usage:
 * Course::query()->byStatus(CourseStatus::PUBLISHED)->get();
 * Course::query()->filterByRequest($request)->paginateFromRequest($request);
 * Course::query()->published()->withRelations()->get();
 */
class CourseBuilder extends Builder
{
    // ============================================
    // SCOPE METHODS
    // ============================================

    /**
     * Filter courses by status
     *
     * @param string $status
     * @return self
     */
    public function byStatus(string $status): self
    {
        return $this->where('status', $status);
    }

    /**
     * Filter courses by multiple statuses
     *
     * @param array<CourseStatus|string> $statuses
     * @return self
     */
    public function byStatuses(array $statuses): self
    {
        return $this->whereIn('status', $statuses);
    }

    /**
     * Filter courses by course category
     *
     * @param int $courseCategoryId
     * @return self
     */
    public function byCourseCategory(int $courseCategoryId): self
    {
        return $this->where('course_category_id', $courseCategoryId);
    }


    /**
     * Filter courses by language
     *
     * @param string $language
     * @return self
     */
    public function byLanguage(string $language): self
    {
        return $this->where('language', $language);
    }

    /**
     * Filter courses by difficulty level
     *
     * @param string $difficultyLevel
     * @return self
     */
    public function byDifficultyLevel(string $difficultyLevel): self
    {
        return $this->where('difficulty_level', $difficultyLevel);
    }

    /**
     * Filter courses by minimum rating
     *
     * @param float $minRating
     * @return self
     */
    public function byMinRating(float $minRating): self
    {
        return $this->where('average_rating', '>=', $minRating);
    }

    /**
     * Filter courses by creator
     *
     * @param int $creatorId
     * @return self
     */
    public function createdBy(int $creatorId): self
    {
        return $this->where('created_by', $creatorId);
    }

    /**
     * Filter courses that are published
     *
     * @return self
     */
    public function published(): self
    {
        return $this->where('status', CourseStatus::PUBLISHED->value)
            ->whereNotNull('published_at');
    }

    /**
     * Filter courses that are draft
     *
     * @return self
     */
    public function draft(): self
    {
        return $this->where('status', CourseStatus::DRAFT->value);
    }

    /**
     * Filter courses that are in review
     *
     * @return self
     */
    public function inReview(): self
    {
        return $this->where('status', CourseStatus::REVIEW->value);
    }

    /**
     * Filter courses that are archived
     *
     * @return self
     */
    public function archived(): self
    {
        return $this->where('status', CourseStatus::ARCHIVED->value);
    }

    /**
     * Filter courses that are available for enrollment
     * (published with active course category)
     *
     * @return self
     */
    public function enrollable(): self
    {
        return $this->published()
            ->whereHas('courseCategory', function ($query) {
                $query->where('is_active', true);
            });
    }

    /**
     * Filter courses by instructor
     *
     * @param int $instructorId
     * @return self
     */
    public function byInstructor(int $instructorId): self
    {
        return $this->whereHas('instructors', function ($query) use ($instructorId) {
            $query->where('instructor_id', $instructorId);
        });
    }

    /**
     * Filter courses where instructor is primary
     *
     * @param int $instructorId
     * @return self
     */
    public function byPrimaryInstructor(int $instructorId): self
    {
        return $this->whereHas('instructors', function ($query) use ($instructorId) {
            $query->where('instructor_id', $instructorId)
                ->where('is_primary', true);
        });
    }

    /**
     * Filter courses that are offline available
     *
     * @param bool $isOfflineAvailable
     * @return self
     */
    public function offlineAvailable(bool $isOfflineAvailable = true): self
    {
        return $this->where('is_offline_available', $isOfflineAvailable);
    }

    /**
     * Filter courses by delivery type
     *
     * @param string $deliveryType
     * @return self
     */
    public function byDeliveryType(string $deliveryType): self
    {
        return $this->where('course_delivery_type', $deliveryType);
    }

    /**
     * Filter courses that have at least one unit
     *
     * @return self
     */
    public function withUnits(): self
    {
        return $this->has('units');
    }

    /**
     * Filter courses that have at least one instructor
     *
     * @return self
     */
    public function withInstructors(): self
    {
        return $this->has('instructors');
    }

    /**
     * Filter courses that have enrollments
     *
     * @return self
     */
    public function withEnrollments(): self
    {
        return $this->has('enrollments');
    }

    /**
     * Filter courses that have active enrollments
     *
     * @return self
     */
    public function withActiveEnrollments(): self
    {
        return $this->whereHas('enrollments', function ($query) {
            $query->where('enrollment_status', 'active');
        });
    }

    // ============================================
    // EAGER LOADING METHODS
    // ============================================

    /**
     * Eager load common relationships
     *
     * @return self
     */
    public function withRelations(): self
    {
        return $this->with(['courseCategory', 'instructors', 'creator']);
    }

    /**
     * Eager load all relationships
     *
     * @return self
     */
    public function withAllRelations(): self
    {
        return $this->with(['courseCategory', 'instructors', 'creator', 'units', 'enrollments']);
    }

    /**
     * Eager load course category relationship
     *
     * @return self
     */
    public function withCourseCategory(): self
    {
        return $this->with('courseCategory');
    }

    /**
     * Eager load instructors relationship
     *
     * @return self
     */
    public function withInstructorsRelation(): self
    {
        return $this->with('instructors');
    }

    /**
     * Eager load creator relationship
     *
     * @return self
     */
    public function withCreator(): self
    {
        return $this->with('creator');
    }

    /**
     * Eager load units relationship
     *
     * @return self
     */
    public function withUnitsRelation(): self
    {
        return $this->with('units');
    }

    // ============================================
    // ORDERING METHODS
    // ============================================

    /**
     * Order by published date (descending)
     *
     * @return self
     */
    public function orderByPublished(): self
    {
        return $this->orderBy('published_at', 'desc');
    }

    /**
     * Order by created date (descending)
     *
     * @return self
     */
    public function orderByCreated(): self
    {
        return $this->orderBy('created_at', 'desc');
    }

    /**
     * Order by rating (descending)
     *
     * @return self
     */
    public function orderByRating(): self
    {
        return $this->orderBy('average_rating', 'desc');
    }

    /**
     * Order by title (ascending)
     *
     * @return self
     */
    public function orderByTitle(): self
    {
        return $this->orderBy('title', 'asc');
    }

    /**
     * Order by custom field
     *
     * @param string $field
     * @param string $direction
     * @return self
     */
    public function orderByField(string $field, string $direction = 'asc'): self
    {
        return $this->orderBy($field, $direction);
    }

    /**
     * Apply default ordering (by published_at desc, or created_at desc if not published)
     *
     * @return self
     */
    public function ordered(): self
    {
        return $this->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc');
    }

    // ============================================
    // FILTER METHODS
    // ============================================

    /**
     * Filter courses based on request parameters
     *
     * @param \Illuminate\Http\Request $request
     * @return self
     */
    public function filterByRequest($request): self
    {
        $query = $this;

        // Filter by status only when explicitly provided (no default = return all statuses)
        if ($request->filled('status')) {
            $query = $query->byStatus($request->input('status'));
        }

        // Filter by course category
        if ($request->filled('course_category_id')) {
            $query = $query->byCourseCategory((int) $request->input('course_category_id'));
        }

        // Filter by program
        if ($request->filled('program_id')) {
            $query = $query->byProgram((int) $request->input('program_id'));
        }

        // Filter by language
        if ($request->filled('language')) {
            $query = $query->byLanguage($request->input('language'));
        }

        // Filter by difficulty level
        if ($request->filled('difficulty_level')) {
            $query = $query->byDifficultyLevel($request->input('difficulty_level'));
        }

        // Filter by minimum rating
        if ($request->filled('min_rating')) {
            $query = $query->byMinRating((float) $request->input('min_rating'));
        }

        // Filter by creator
        if ($request->filled('created_by')) {
            $query = $query->createdBy((int) $request->input('created_by'));
        }

        // Filter by instructor
        if ($request->filled('instructor_id')) {
            $query = $query->byInstructor((int) $request->input('instructor_id'));
        }

        // Filter by offline availability
        if ($request->has('is_offline_available')) {
            $query = $query->offlineAvailable($request->boolean('is_offline_available'));
        }

        // Filter by delivery type
        if ($request->filled('course_delivery_type')) {
            $query = $query->byDeliveryType($request->input('course_delivery_type'));
        }

        // Order by
        $orderBy = $request->input('order_by', 'published_at');
        $orderDirection = $request->input('order_direction', 'desc');

        // Validate order direction
        $orderDirection = in_array(strtolower($orderDirection), ['asc', 'desc'])
            ? strtolower($orderDirection)
            : 'desc';

        $query = $query->orderByField($orderBy, $orderDirection);

        return $query;
    }

    /**
     * Paginate results based on request parameters
     *
     * @param \Illuminate\Http\Request $request
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginateFromRequest($request, int $perPage = 15)
    {
        $perPage = $request->input('per_page', $perPage);
        $perPage = max(1, min(100, (int) $perPage)); // Limit between 1 and 100

        return $this->paginate($perPage);
    }

    // ============================================
    // SEARCH METHODS
    // ============================================

    /**
     * Search courses by title or description
     *
     * @param string $searchTerm
     * @return self
     */
    public function search(string $searchTerm): self
    {
        return $this->where(function ($query) use ($searchTerm) {
            $query->where('title', 'like', "%{$searchTerm}%")
                ->orWhere('description', 'like', "%{$searchTerm}%")
                ->orWhere('slug', 'like', "%{$searchTerm}%");
        });
    }

    /**
     * Filter courses by slug
     *
     * @param string $slug
     * @return self
     */
    public function bySlug(string $slug): self
    {
        return $this->where('slug', $slug);
    }
}
