<?php

namespace Modules\AssessmentModule\Services\V1;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Modules\AssessmentModule\Models\Quiz;
use Modules\AssessmentModule\Models\Builders\QuizBuilder;
use Throwable;

/**
 * Class QuizService
 *
 * This service handles the business logic for quizzes, including:
 * - Fetching a list of quizzes with filters and pagination
 * - Viewing, creating, updating, publishing, and unpublishing quizzes
 * - Deleting quizzes
 *
 * @package Modules\AssesmentModule\Services\V1
 */
class QuizService extends BaseService
{
    /**
     * Handle the service logic. Currently a placeholder for any additional functionality.
     *
     * @return void
     */
    public function handle() {}

    /**
     * Fetch a paginated list of quizzes based on the given filters.
     *
     * @param array $filters The filters to apply to the quiz query (e.g., status, type).
     * @param int $perPage The number of quizzes per page (default is 15).
     *
     * @return array<string, mixed> The result of the operation with status and data.
     */
    public function index(array $filters = [], int $perPage = 15): array
    {
        try {
            $data = Quiz::query()
                ->filter($filters)
                ->paginate($perPage);

            return $this->ok('Quiz fetched successfully.', $data, 200);
        } catch (Throwable $e) {
            return $this->fail('Failed to fetch quizzes.', $e, 500);
        }
    }

    /**
     * Fetch a single quiz by its ID, including its associated questions and options.
     *
     * @param int $id The ID of the quiz.
     *
     * @return array<string, mixed> The result of the operation with status and data.
     */
    public function show(int $id): array
    {
        try {
            $quiz = Quiz::query()->with(['questions.options'])->findOrFail($id);
            return $this->ok('Quiz fetched successfully.', $quiz);
        } catch (ModelNotFoundException $e) {
            return $this->fail('Quiz not found.', $e, 404);
        } catch (Throwable $e) {
            return $this->fail('Failed to fetch quiz.', $e);
        }
    }

    /**
     * Create a new quiz with the provided data.
     *
     * @param array $data The data to create a new quiz.
     *
     * @return array<string, mixed> The result of the operation with status and the created quiz.
     */
    public function store(array $data): array
    {
        try {
            $quiz = DB::transaction(function () use ($data) {
                return Quiz::query()->create($data);
            });

            return $this->ok('Quiz created successfully.', $quiz);
        } catch (Throwable $e) {
            return $this->fail('Failed to create quiz.', $e);
        }
    }

    /**
     * Update an existing quiz with the provided data.
     *
     * @param Quiz $quiz The quiz to update.
     * @param array $data The data to update the quiz.
     *
     * @return array<string, mixed> The result of the operation with status and the updated quiz.
     */
    public function update(Quiz $quiz, array $data): array
    {
        try {
            DB::transaction(function () use ($quiz, $data) {
                $quiz->fill($data);
                $quiz->save();
            });

            return $this->ok('Quiz updated successfully.', $quiz->fresh());
        } catch (Throwable $e) {
            return $this->fail('Failed to update quiz.', $e);
        }
    }

    /**
     * Publish a quiz, changing its status to 'published'.
     *
     * @param Quiz $quiz The quiz to publish.
     *
     * @return array<string, mixed> The result of the operation with status and the published quiz.
     */
    public function publish(Quiz $quiz): array
    {
        try {
            $quiz->update(['status' => 'published']);
            return $this->ok('Quiz published successfully.', $quiz);
        } catch (Throwable $e) {
            return $this->fail('Failed to publish quiz', null, 500, $e->getMessage());
        }
    }

    /**
     * Unpublish a quiz, changing its status to 'draft'.
     *
     * @param Quiz $quiz The quiz to unpublish.
     *
     * @return array<string, mixed> The result of the operation with status and the unpublished quiz.
     */
    public function unpublish(Quiz $quiz): array
    {
        try {
            if ($quiz->status !== 'published') {
                return $this->ok('Quiz is already unpublished (draft).', $quiz);
            }

            if ($quiz->attempts()->where('status', 'in_progress')->exists()) {
                return $this->fail(
                    'Cannot unpublish quiz because an attempt is currently in progress.',
                    null,
                    422
                );
            }

            $quiz->update(['status' => 'draft']);
            return $this->ok('Quiz unpublished successfully.', $quiz);
        } catch (Throwable $e) {
            return $this->fail('Failed to unpublish quiz', null, 500, $e->getMessage());
        }
    }

    /**
     * Delete a quiz.
     *
     * @param Quiz $quiz The quiz to delete.
     *
     * @return array<string, mixed> The result of the operation with status.
     */
    public function destroy(Quiz $quiz): array
    {
        try {
            DB::transaction(function () use ($quiz) {
                $quiz->delete();
            });

            return $this->ok('Quiz deleted successfully.');
        } catch (Throwable $e) {
            return $this->fail('Failed to delete quiz.', $e);
        }
    }
}