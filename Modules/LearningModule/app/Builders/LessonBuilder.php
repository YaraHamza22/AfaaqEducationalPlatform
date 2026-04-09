<?php

namespace Modules\LearningModule\Builders;

use Illuminate\Database\Eloquent\Builder;

/**
 * LessonBuilder
 *
 * Purpose: Custom query builder for Lesson model that encapsulates all query logic,
 * filters, and scopes. This follows the Builder pattern to keep query logic
 * separate from the model, improving maintainability and testability.
 */
class LessonBuilder extends Builder
{
    // ============================================
    // SCOPE METHODS
    // ============================================

    /**
     * Filter lessons by unit
     *
     * @param int $unitId
     * @return self
     */
    public function byUnit(int $unitId): self
    {
        return $this->where('unit_id', $unitId);
    }

    /**
     * Filter lessons by order
     *
     * @param int $order
     * @return self
     */
    public function byOrder(int $order): self
    {
        return $this->where('lesson_order', $order);
    }

    /**
     * Filter lessons by type
     *
     * @param string $type
     * @return self
     */
    public function byType(string $type): self
    {
        return $this->where('lesson_type', $type);
    }

    /**
     * Filter required lessons
     *
     * @param bool $isRequired
     * @return self
     */
    public function required(bool $isRequired = true): self
    {
        return $this->where('is_required', $isRequired);
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
        return $this->with(['unit']);
    }

    /**
     * Eager load unit relationship
     *
     * @return self
     */
    public function withUnit(): self
    {
        return $this->with('unit');
    }

    // ============================================
    // ORDERING METHODS
    // ============================================

    /**
     * Order lessons by lesson_order ascending
     *
     * @return self
     */
    public function ordered(): self
    {
        return $this->orderBy('lesson_order', 'asc');
    }

    /**
     * Order lessons by lesson_order descending
     *
     * @return self
     */
    public function orderedDesc(): self
    {
        return $this->orderBy('lesson_order', 'desc');
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
     * Filter lessons based on request parameters
     *
     * @param \Illuminate\Http\Request $request
     * @return self
     */
    public function filterByRequest($request): self
    {
        $query = $this;

        // Filter by unit
        if ($request->has('unit_id')) {
            $query = $query->byUnit($request->input('unit_id'));
        }

        // Filter by order
        if ($request->has('lesson_order')) {
            $query = $query->byOrder($request->input('lesson_order'));
        }

        // Filter by type
        if ($request->has('lesson_type')) {
            $query = $query->byType($request->input('lesson_type'));
        }

        // Filter by required
        if ($request->has('is_required')) {
            $query = $query->required($request->boolean('is_required'));
        }

        // Search
        if ($request->has('search')) {
            $query = $query->search($request->input('search'));
        }

        // Order by
        $orderBy = $request->input('order_by', 'lesson_order');
        $orderDirection = $request->input('order_direction', 'asc');

        // Validate order direction
        $orderDirection = in_array(strtolower($orderDirection), ['asc', 'desc'])
            ? strtolower($orderDirection)
            : 'asc';

        if ($orderBy === 'lesson_order') {
            $query = $query->orderBy('lesson_order', $orderDirection);
        } elseif ($orderBy === 'created_at') {
            $query = $query->orderBy('created_at', $orderDirection);
        } else {
            $query = $query->orderBy('lesson_order', 'asc');
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
     * Search lessons by title or description
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
