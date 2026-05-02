<?php

namespace Modules\LearningModule\Services;

use App\Traits\CachesQueries;
use App\Traits\HelperTrait;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\LearningModule\Models\CourseCategory;

/**
 * Service class for managing course category business logic.
 * Handles course category creation, updates, activation/deactivation, and various course category operations.
 */
class CourseCategoryService
{
    use HelperTrait, CachesQueries;
    /**
     * Create a new course category.
     *
     * @param array $data
     * @return CourseCategory
     * @throws Exception
     */
    public function create(array $data): ?CourseCategory
    {
        try {
            // Generate slug if not provided (use English name)
            if (empty($data['slug']) && !empty($data['name'])) {
                $nameForSlug = $this->translatableToSlugSource($data['name'], 'en');
                if ($nameForSlug !== '') {
                    $data['slug'] = $this->generateUniqueSlug($nameForSlug, CourseCategory::class);
                }
            }

            // Ensure slug is unique
            if (isset($data['slug'])) {
                $data['slug'] = $this->ensureUniqueSlug($data['slug'], CourseCategory::class);
            }

            $courseCategory = CourseCategory::create($data);

            // Clear cache after creation
            $this->clearCourseCategoryCache();

            Log::info("Course category created", [
                'course_category_id' => $courseCategory->course_category_id,
                'name' => $this->translatableToSlugSource($courseCategory->name ?? [], 'en'),
                'slug' => $courseCategory->slug,
            ]);

            return $courseCategory;
        } catch (Exception $e) {
            Log::error("Failed to create course category", [
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Update an existing course category.
     *
     * @param CourseCategory $courseCategory
     * @param array $data
     * @return CourseCategory
     * @throws Exception
     */
    public function update(CourseCategory $courseCategory, array $data): ?CourseCategory
    {
        try {
            // Handle slug update (use English name)
            if (isset($data['name']) && empty($data['slug'])) {
                $nameForSlug = $this->translatableToSlugSource($data['name'], 'en');
                if ($nameForSlug !== '') {
                    $data['slug'] = $this->generateUniqueSlug($nameForSlug, CourseCategory::class, 'slug', 'course_category_id', $courseCategory->course_category_id);
                }
            } elseif (isset($data['slug'])) {
                $data['slug'] = $this->ensureUniqueSlug($data['slug'], CourseCategory::class, 'slug', 'course_category_id', $courseCategory->course_category_id);
            }

            $courseCategory->update($data);

            // Clear cache after update
            $this->clearCourseCategoryCache($courseCategory);

            Log::info("Course category updated", [
                'course_category_id' => $courseCategory->course_category_id,
                'updated_fields' => array_keys($data),
            ]);

            return $courseCategory->fresh();
        } catch (Exception $e) {
            Log::error("Failed to update course category", [
                'course_category_id' => $courseCategory->course_category_id,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Activate a course category.
     *
     * @param CourseCategory $courseCategory
     * @return CourseCategory
     * @throws Exception
     */
    public function activate(CourseCategory $courseCategory): ?CourseCategory
    {
        if ($courseCategory->is_active) {
            return $courseCategory;
        }

        try {
            $courseCategory->update(['is_active' => true]);

            // Clear cache after activation
            $this->clearCourseCategoryCache($courseCategory);

            Log::info("Course category activated", [
                'course_category_id' => $courseCategory->course_category_id,
                'name' => $courseCategory->name,
            ]);

            return $courseCategory->fresh();
        } catch (Exception $e) {
            Log::error("Failed to activate course category", [
                'course_category_id' => $courseCategory->course_category_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Deactivate a course category.
     *
     * @param CourseCategory $courseCategory
     * @return CourseCategory
     * @throws Exception
     */
    public function deactivate(CourseCategory $courseCategory): ?CourseCategory
    {
        if (!$courseCategory->is_active) {
            return $courseCategory;
        }

        // Check if course category has active courses
        $activeCoursesCount = $courseCategory->courses()
            ->where('status', 'published')
            ->count();

        if ($activeCoursesCount > 0) {
            Log::warning("Attempted to deactivate course category with active published courses", [
                'course_category_id' => $courseCategory->course_category_id,
                'active_courses_count' => $activeCoursesCount,
            ]);
            return null;
        }

        try {
            $courseCategory->update(['is_active' => false]);

            // Clear cache after deactivation
            $this->clearCourseCategoryCache($courseCategory);

            Log::info("Course category deactivated", [
                'course_category_id' => $courseCategory->course_category_id,
                'name' => $courseCategory->name,
            ]);

            return $courseCategory->fresh();
        } catch (Exception $e) {
            Log::error("Failed to deactivate course category", [
                'course_category_id' => $courseCategory->course_category_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
          //  return null;
            throw $e;
        }
    }

    /**
     * Delete a course category (soft delete).
     *
     * @param CourseCategory $courseCategory
     * @return bool
     * @throws Exception
     */
    public function delete(CourseCategory $courseCategory): bool
    {
        // Check if course category has courses
        $coursesCount = $courseCategory->courses()->count();

        if ($coursesCount > 0) {
            Log::warning("Attempted to delete course category with courses", [
                'course_category_id' => $courseCategory->course_category_id,
                'courses_count' => $coursesCount,
            ]);
            return false;
        }

        try {
            return DB::transaction(function () use ($courseCategory) {
                $courseCategoryId = $courseCategory->course_category_id;
                $courseCategoryName = $courseCategory->name;
                $deleted = $courseCategory->delete();

                if ($deleted) {
                    // Clear cache after deletion
                    $this->clearCourseCategoryCache();

                    Log::info("Course category deleted", [
                        'course_category_id' => $courseCategoryId,
                        'name' => $courseCategoryName,
                    ]);
                }

                return $deleted;
            });
        } catch (Exception $e) {
            Log::error("Failed to delete course category", [
                'course_category_id' => $courseCategory->course_category_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Check if course category can be deleted.
     *
     * @param CourseCategory $courseCategory
     * @return bool
     */
    public function canBeDeleted(CourseCategory $courseCategory): bool
    {
        return $courseCategory->courses()->count() === 0;
    }

    /**
     * Check if course category can be deactivated.
     *
     * @param CourseCategory $courseCategory
     * @return bool
     */
    public function canBeDeactivated(CourseCategory $courseCategory): bool
    {
        $activeCoursesCount = $courseCategory->courses()
            ->where('status', 'published')
            ->count();

        return $activeCoursesCount === 0;
    }

    /**
     * Get all active course categories.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveCourseCategories()
    {
        return $this->remember('course_categories.active', 3600, function () {
            return CourseCategory::where('is_active', true)
                ->orderBy('name', 'asc')
                ->get();
        }, ['course_categories']);
    }

    /**
     * Get all course categories (active and inactive).
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllCourseCategories()
    {
        return $this->remember('course_categories.all', 3600, function () {
            return CourseCategory::orderBy('name', 'asc')->get();
        }, ['course_categories']);
    }

    /**
     * Get course category by slug.
     *
     * @param string $slug
     * @return CourseCategory|null
     */
    public function getBySlug(string $slug): ?CourseCategory
    {
        return $this->remember("course_category.slug.{$slug}", 3600, function () use ($slug) {
            return CourseCategory::where('slug', $slug)->first();
        }, ['course_categories']);
    }

    /**
     * Get course category by ID.
     *
     * @param int $courseCategoryId
     * @return CourseCategory|null
     */
    public function getById(int $courseCategoryId): ?CourseCategory
    {
        return $this->remember("course_category.{$courseCategoryId}", 3600, function () use ($courseCategoryId) {
            $courseCategory = CourseCategory::find($courseCategoryId);

            if (!$courseCategory) {
                Log::warning("Course category not found", [
                    'course_category_id' => $courseCategoryId,
                ]);
                return null;
            }

            return $courseCategory;
        }, ['course_categories']);
    }

    /**
     * Get course count for a course category.
     *
     * @param CourseCategory $courseCategory
     * @return int
     */
    public function getCourseCount(CourseCategory $courseCategory): int
    {
        return $courseCategory->courses()->count();
    }

    /**
     * Get active course count for a course category.
     *
     * @param CourseCategory $courseCategory
     * @return int
     */
    public function getActiveCourseCount(CourseCategory $courseCategory): int
    {
        $cacheKey = "course_category.{$courseCategory->course_category_id}.active_count";

        return $this->remember($cacheKey, 3600, function () use ($courseCategory) {
            return $courseCategory->courses()
                ->where('status', 'published')
                ->count();
        }, ['course_categories', "course_category.{$courseCategory->course_category_id}"]);
    }

    /**
     * Clear course category related cache.
     * Uses Redis tags for efficient bulk invalidation.
     *
     * @param CourseCategory|null $courseCategory Optional course category to clear specific cache
     * @return void
     */
    protected function clearCourseCategoryCache(?CourseCategory $courseCategory = null): void
    {
        // Use Redis tags for efficient bulk invalidation
        $this->flushTags(['course_categories']);
        if ($courseCategory) {
            $this->flushTags(["course_category.{$courseCategory->course_category_id}"]);
        }
    }
}
