<?php

namespace Modules\LearningModule\Builders;

use Illuminate\Database\Eloquent\Builder;

/**
 * UnitBuilder
 *
 * Purpose: Custom query builder for Unit model that encapsulates all query logic,
 * filters, and scopes. This follows the Builder pattern to keep query logic
 * separate from the model, improving maintainability and testability.

 */
class UnitBuilder extends Builder
{
    // ============================================
    // SCOPE METHODS
    // ============================================

    /**
     * Filter units by course
     *
     * @param int $courseId
     * @return self
     */
    public function byCourse(int $courseId): self
    {
        return $this->where('course_id', $courseId);
    }

    /**
     * Filter units by order
     *
     * @param int $order
     * @return self
     */
    public function byOrder(int $order): self
    {
        return $this->where('unit_order', $order);
    }

    /**
     * Filter units with lessons
     *
     * @return self
     */
    public function withLessons(): self
    {
        return $this->has('lessons');
    }

    /**
     * Filter units without lessons
     *
     * @return self
     */
    public function withoutLessons(): self
    {
        return $this->doesntHave('lessons');
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
        return $this->with(['course', 'lessons']);
    }

    /**
     * Eager load course relationship
     *
     * @return self
     */
    public function withCourse(): self
    {
        return $this->with('course');
    }

    /**
     * Eager load lessons relationship
     *
     * @return self
     */
    public function withLessonsRelation(): self
    {
        return $this->with('lessons');
    }

    // ============================================
    // ORDERING METHODS
    // ============================================

    /**
     * Order units by unit_order ascending
     *
     * @return self
     */
    public function ordered(): self
    {
        return $this->orderBy('unit_order', 'asc');
    }

    /**
     * Order units by unit_order descending
     *
     * @return self
     */
    public function orderedDesc(): self
    {
        return $this->orderBy('unit_order', 'desc');
    }

    /**
     * Order by created date
     *
     * @param string $direction
     * @return self
     */
    public function orderByCreated(string $direction = 'desc'): self
    {
        return $this->orderBy('created_at', $direction);
    }

    // ============================================
    // FILTER METHODS
    // ============================================

    /**
     * Filter units based on request parameters
     *
     * @param \Illuminate\Http\Request $request
     * @return self
     */
    public function filterByRequest($request): self
    {
        $query = $this;

        // Filter by course
        if ($request->filled('course_id')) {
            $query = $query->byCourse((int) $request->input('course_id'));
        }

        // Filter by order
        if ($request->filled('unit_order')) {
            $query = $query->byOrder((int) $request->input('unit_order'));
        }

        // Filter by has lessons (only when explicitly true or false; empty = no filter)
        if ($request->filled('has_lessons')) {
            if ($request->boolean('has_lessons')) {
                $query = $query->withLessons();
            } else {
                $query = $query->withoutLessons();
            }
        }

        // Search
        if ($request->filled('search')) {
            $query = $query->search($request->input('search'));
        }

        // Order by
        $orderBy = $request->input('order_by', 'unit_order');
        $orderDirection = $request->input('order_direction', 'asc');

        // Validate order direction
        $orderDirection = in_array(strtolower($orderDirection), ['asc', 'desc'])
            ? strtolower($orderDirection)
            : 'asc';

        if ($orderBy === 'unit_order') {
            $query = $query->orderBy('unit_order', $orderDirection);
        } elseif ($orderBy === 'created_at') {
            $query = $query->orderBy('created_at', $orderDirection);
        } else {
            $query = $query->orderBy('unit_order', 'asc');
        }

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
     * Search units by title or description
     *
     * @param string $searchTerm
     * @return self
     */
    public function search(string $searchTerm): self
    {
        return $this->where(function ($query) use ($searchTerm) {
            $query->where('title', 'like', "%{$searchTerm}%")
                ->orWhere('description', 'like', "%{$searchTerm}%");
        });
    }
}
