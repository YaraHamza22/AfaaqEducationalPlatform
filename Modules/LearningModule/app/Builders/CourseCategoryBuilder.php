<?php

namespace Modules\LearningModule\Builders;

use Illuminate\Database\Eloquent\Builder;

/**
 * CourseTypeBuilder
 *
 * Custom query builder for CourseType model that encapsulates query logic,
 * filters, and scopes. Follows the Builder pattern for maintainability.
 */
class CourseCategoryBuilder extends Builder
{
    /**
     * Filter course categories by active status.
     *
     * @param bool $isActive
     * @return self
     */
    public function active(bool $isActive = true): self
    {
        return $this->where('is_active', $isActive);
    }

    /**
     * Filter course categories by slug.
     *
     * @param string $slug
     * @return self
     */
    public function bySlug(string $slug): self
    {
        return $this->where('slug', $slug);
    }

    /**
     * Filter course categories by name.
     *
     * @param string $name
     * @return self
     */
    public function byName(string $name): self
    {
        return $this->where('name', $name);
    }

    /**
     * Search course categories by name or description.
     *
     * @param string $searchTerm
     * @return self
     */
    public function search(string $searchTerm): self
    {
        return $this->where(function ($query) use ($searchTerm) {
            $query->where('name', 'like', "%{$searchTerm}%")
                ->orWhere('description', 'like', "%{$searchTerm}%")
                ->orWhere('slug', 'like', "%{$searchTerm}%");
        });
    }

    /**
     * Eager load courses relationship.
     *
     * @return self
     */
    public function withCourses(): self
    {
        return $this->with('courses');
    }

    /**
     * Filter course types that have courses.
     *
     * @return self
     */
    public function withCoursesCount(): self
    {
        return $this->has('courses');
    }

    /**
     * Order by name (ascending).
     *
     * @return self
     */
    public function orderByName(): self
    {
        return $this->orderBy('name', 'asc');
    }

    /**
     * Apply default ordering (by name ascending).
     *
     * @return self
     */
    public function ordered(): self
    {
        return $this->orderBy('name', 'asc');
    }

    /**
     * Filter course categories  based on request parameters.
     *
     * @param \Illuminate\Http\Request $request
     * @return self
     */
    public function filterByRequest($request): self
    {
        $query = $this;

        if ($request->filled('search')) {
            $query = $query->search($request->input('search'));
        }

        if ($request->filled('slug')) {
            $query = $query->bySlug($request->input('slug'));
        }

        if ($request->filled('name')) {
            $query = $query->byName($request->input('name'));
        }

        if ($request->has('is_active')) {
            $query = $query->active($request->boolean('is_active'));
        }

        return $query;
    }

    /**
     * Paginate results based on request parameters.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginateFromRequest($request, int $perPage = 15)
    {
        $perPage = $request->input('per_page', $perPage);
        $perPage = max(1, min(100, (int) $perPage));

        return $this->paginate($perPage);
    }
}
