<?php

namespace Modules\LearningModule\Services;

use App\Traits\CachesQueries;
use App\Traits\HelperTrait;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\LearningModule\Enums\CourseStatus;
use Modules\LearningModule\Models\Unit;
use Modules\LearningModule\Models\Course;
use Modules\LearningModule\Models\CourseCategory;
use Modules\LearningModule\Models\Enrollment;
use Modules\LearningModule\Services\CourseInstructorService;

/**
 * Service class for managing course business logic.
 * Handles course creation, updates, publishing, and various course operations.
 */
class CourseService
{
    use HelperTrait, CachesQueries;

    /**
     * Create a new course.
     *
     * @param array $data
     * @return Course
     * @throws Exception
     */
    public function create(array $data): ?Course
    {
        // Validate course category exists
        if (isset($data['course_category_id'])) {
            $courseCategory = CourseCategory::find($data['course_category_id']);
            if (!$courseCategory) {
                Log::warning("Attempted to create course with invalid course category", [
                    'course_category_id' => $data['course_category_id'],
                ]);
                throw new Exception("Course category with ID {$data['course_category_id']} does not exist.", 422);
            }
        }

        try {
            // Generate slug if not provided (use English title for consistent slugs)
            if (empty($data['slug']) && !empty($data['title'])) {
                $titleForSlug = $this->translatableToSlugSource($data['title'], 'en');
                if ($titleForSlug !== '') {
                    $data['slug'] = $this->generateUniqueSlug($titleForSlug, Course::class);
                }
            }

            // Ensure slug is unique
            if (isset($data['slug'])) {
                $data['slug'] = $this->ensureUniqueSlug($data['slug'], Course::class);
            }

            // Set created_by
            $data['created_by'] = Auth::id();

            // Set default status if not provided
            if (empty($data['status'])) {
                $data['status'] = CourseStatus::DRAFT->value;
            }

            $course = Course::create($data);
            if (isset($data['cover']) && $data['cover']->isValid()) {
                $course->addMedia($data['cover'])->toMediaCollection('cover');
            }

            if (isset($data['intro_video']) && $data['intro_video']->isValid()) {
                $course->addMedia($data['intro_video'])->toMediaCollection('intro_video');
            }

            // Clear course cache after creation
            $this->clearCourseCache();

            Log::info("Course created", [
                'course_id' => $course->course_id,
                'title' => $this->translatableToSlugSource($course->title ?? [], 'en'),
                'created_by' => $data['created_by'],
            ]);

            return $course;
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error("Database error creating course", [
                'data' => $data,
                'error' => $e->getMessage(),
                'sql' => $e->getSql() ?? null,
                'bindings' => $e->getBindings() ?? null,
            ]);

            // Check for specific database errors
            if (str_contains($e->getMessage(), 'foreign key constraint')) {
                throw new Exception("Database constraint violation: " . $e->getMessage(), 422);
            }
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                throw new Exception("A course with this slug already exists.", 422);
            }

            throw new Exception("Database error: " . $e->getMessage(), 500);
        } catch (Exception $e) {
            Log::error("Failed to create course", [
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing course.
     *
     * @param Course $course
     * @param array $data
     * @return Course
     * @throws Exception
     */
    public function update(Course $course, array $data): ?Course
    {
        // Validate course category if being changed
        if (isset($data['course_category_id']) && $data['course_category_id'] != $course->course_category_id) {
            $courseCategory = CourseCategory::find($data['course_category_id']);
            if (!$courseCategory) {
                Log::warning("Attempted to update course with invalid course category", [
                    'course_id' => $course->course_id,
                    'course_category_id' => $data['course_category_id'],
                ]);
                return null;
            }
        }

        try {
            // Handle slug update (use English title for consistent slugs)
            if (isset($data['title']) && empty($data['slug'])) {
                $titleForSlug = $this->translatableToSlugSource($data['title'], 'en');
                if ($titleForSlug !== '') {
                    $data['slug'] = $this->generateUniqueSlug($titleForSlug, Course::class, 'slug', 'course_id', $course->course_id);
                }
            } elseif (isset($data['slug'])) {
                $data['slug'] = $this->ensureUniqueSlug($data['slug'], Course::class, 'slug', 'course_id', $course->course_id);
            }

            $course->update($data);
                        if (isset($data['cover']) && $data['cover']->isValid()) {
                $course->addMedia($data['cover'])->toMediaCollection('cover');
            }

            if (isset($data['intro_video']) && $data['intro_video']->isValid()) {
                $course->addMedia($data['intro_video'])->toMediaCollection('intro_video');
            }


            // Clear course cache after update
            $this->clearCourseCache($course);

            Log::info("Course updated", [
                'course_id' => $course->course_id,
                'updated_fields' => array_keys($data),
            ]);

            return $course->fresh();
        } catch (Exception $e) {
            Log::error("Failed to update course", [
                'course_id' => $course->course_id,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Publish a course.
     *
     * @param Course $course
     * @return Course
     * @throws Exception
     */
    public function publish(Course $course): ?Course
    {
        if (!$this->isPublishable($course)) {
            $reasons = $this->getUnpublishabilityReasons($course);
            Log::warning("Attempted to publish course that cannot be published", [
                'course_id' => $course->course_id,
                'reasons' => $reasons,
            ]);
            return null;
        }

        try {
            $course->update([
                'status' => CourseStatus::PUBLISHED->value,
                'published_at' => now(),
            ]);

            // Clear course cache after publishing
            $this->clearCourseCache($course);

            Log::info("Course published", [
                'course_id' => $course->course_id,
                'title' => $course->title,
            ]);

            return $course->fresh();
        } catch (Exception $e) {
            Log::error("Failed to publish course", [
                'course_id' => $course->course_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Unpublish a course.
     *
     * @param Course $course
     * @return Course
     */
    public function unpublish(Course $course): Course
    {
        $course->update([
            'status' => CourseStatus::DRAFT->value,
            'published_at' => null,
        ]);

        // Clear course cache after unpublishing
        $this->clearCourseCache($course);

        return $course->fresh();
    }

    /**
     * Change course status.
     *
     * @param Course $course
     * @param string $newStatus
     * @return Course
     * @throws Exception
     */
    public function changeStatus(Course $course, string $newStatus): ?Course
    {
        $validStatuses = [
            CourseStatus::DRAFT->value,
            CourseStatus::REVIEW->value,
            CourseStatus::PUBLISHED->value,
            CourseStatus::ARCHIVED->value,
        ];
        if (!in_array($newStatus, $validStatuses)) {
            Log::warning("Attempted to change course status to invalid value", [
                'course_id' => $course->course_id,
                'new_status' => $newStatus,
            ]);
            return null;
        }

        try {
            $currentStatus = $course->status;

            // Handle special status changes
            if ($newStatus === CourseStatus::PUBLISHED->value && $currentStatus !== CourseStatus::PUBLISHED->value) {
                return $this->publish($course);
            }

            $updateData = ['status' => $newStatus];

            // Clear published_at if unpublishing
            if ($newStatus !== CourseStatus::PUBLISHED->value && $course->published_at) {
                $updateData['published_at'] = null;
            }

            $course->update($updateData);

            // Clear course cache after status change
            $this->clearCourseCache($course);

            Log::info("Course status changed", [
                'course_id' => $course->course_id,
                'old_status' => $currentStatus,
                'new_status' => $newStatus,
            ]);

            return $course->fresh();
        } catch (Exception $e) {
            Log::error("Failed to change course status", [
                'course_id' => $course->course_id,
                'new_status' => $newStatus,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Check if course can be published.
     *
     * @param Course $course
     * @return bool
     */
    public function isPublishable(Course $course): bool
    {
        $reasons = $this->getUnpublishabilityReasons($course);
        return empty($reasons);
    }

    /**
     * Get reasons why course cannot be published.
     *
     * @param Course $course
     * @return array
     */
    public function getUnpublishabilityReasons(Course $course): array
    {
        $reasons = [];

        // Check if course has at least one instructor
        if ($course->instructors()->count() === 0) {
            $reasons[] = 'Course must have at least one instructor';
        }

        // Check if course has at least one unit
        if ($course->units()->count() === 0) {
            $reasons[] = 'Course must have at least one unit';
        }

        // Check if course category is active
        if ($course->courseCategory && !$course->courseCategory->is_active) {
            $reasons[] = 'Course category must be active';
        }

        // Check if title and description are provided
        if (empty($course->title)) {
            $reasons[] = 'Course title is required';
        }

        if (empty($course->description)) {
            $reasons[] = 'Course description is required';
        }

        return $reasons;
    }

    /**
     * Check if course can be deleted.
     *
     * @param Course $course
     * @return bool
     */
    public function canBeDeleted(Course $course): bool
    {
        // Check for active enrollments
        $activeEnrollments = $course->enrollments()
            ->where('enrollment_status', 'active')
            ->count();

        return $activeEnrollments === 0;
    }

    /**
     * Delete a course (soft delete).
     *
     * @param Course $course
     * @return bool
     * @throws Exception
     */
    public function delete(Course $course): bool
    {
        if (!$this->canBeDeleted($course)) {
            Log::warning("Attempted to delete course with active enrollments", [
                'course_id' => $course->course_id,
            ]);
            return false;
        }

        try {
            return DB::transaction(function () use ($course) {
                $courseId = $course->course_id;
                $courseTitle = $course->title;
                $deleted = $course->delete();

                if ($deleted) {
                    // Clear course cache after deletion
                    $this->clearCourseCache($course);

                    Log::info("Course deleted", [
                        'course_id' => $courseId,
                        'title' => $courseTitle,
                    ]);
                }

                return $deleted;
            });
        } catch (Exception $e) {
            Log::error("Failed to delete course", [
                'course_id' => $course->course_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Check if course is available for enrollment.
     *
     * @param Course $course
     * @return bool
     */
    public function isAvailableForEnrollment(Course $course): bool
    {
        // Course must be published
        if ($course->status !== CourseStatus::PUBLISHED->value || !$course->published_at) {
            return false;
        }

        // Course must not be deleted
        if ($course->trashed()) {
            return false;
        }

        // Course category must be active
        if ($course->courseCategory && !$course->courseCategory->is_active) {
            return false;
        }

        return true;
    }

    /**
     * Get course duration.
     *
     * @param Course $course
     * @return int Duration in hours
     */
    public function getDuration(Course $course): int
    {
        return $course->actual_duration_hours ?? 0;
    }

    /**
     * Clear course related cache.
     * Uses Redis tags for efficient bulk invalidation.
     *
     * @param Course|null $course Optional course to clear specific cache
     * @return void
     */
    protected function clearCourseCache(?Course $course = null): void
    {
        // Use Redis tags for efficient bulk invalidation
        $this->flushTags(['courses']);
        if ($course) {
            $this->flushTags(["course.{$course->course_id}"]);
        }
    }
}
