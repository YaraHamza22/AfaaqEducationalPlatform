<?php

namespace Modules\AssesmentModule\Services\V1;

use Modules\AssesmentModule\Models\QuestionOption;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

/**
 * Class QuestionOptionService
 *
 * This service handles the business logic for managing question options, including:
 * - Fetching a list of question options with filters and pagination
 * - Creating, updating, and deleting question options
 * - Retrieving a single question option by its ID
 *
 * It encapsulates all operations related to question options, ensuring that the business rules are respected.
 * The service uses exception handling to manage errors and provides consistent responses.
 *
 * @package Modules\AssesmentModule\Services\V1
 */
class QuestionOptionService extends BaseService
{
    /**
     * Fetch a paginated list of question options based on the given filters.
     *
     * @param array $filters The filters to apply to the question options query (e.g., correctness, related question).
     * @param int $perPage The number of question options per page (default is 15).
     *
     * @return array<string, mixed> The result of the operation with status and data.
     */
    public function index(array $filters = [], int $perPage = 15)
    {
        try {
            $data = QuestionOption::query()
                ->filter($filters)
                ->paginate($perPage);

            return $this->ok('Question Option fetched successfully.', $data, 200);
        } catch (Throwable $e) {
            return $this->fail('Failed to fetch question options.', $e, 500);
        }
    }

    /**
     * Store a new question option with the provided data.
     *
     * @param array $data The data to create a new question option.
     *
     */
     public function store(array $data): QuestionOption
    {
    return QuestionOption::create([
        'question_id' => $data['question_id'],
        'option_text' => $data['option_text'],
        'is_correct'  => $data['is_correct'] ?? false,
    ]);
     }


    /**
     * Fetch a single question option by its ID.
     *
     * @param int $id The ID of the question option.
     *
     * @return array<string, mixed> The result of the operation with status and the fetched question option.
     */
    public function show(int $id): array
    {
        try {
            $option = QuestionOption::query()->findOrFail($id);

            return $this->ok('Question option fetched successfully.', $option, 200);
        } catch (ModelNotFoundException $e) {
            return $this->fail('Question option not found.', $e, 404);
        } catch (Throwable $e) {
            return $this->fail('Failed to fetch question option.', $e, 500);
        }
    }

    /**
     * Update an existing question option with the provided data.
     *
     * @param int $id The ID of the question option to update.
     * @param array $data The data to update the question option.
     *
     * @return array<string, mixed> The result of the operation with status and the updated question option.
     */
    public function update(int $id, array $data): array
    {
        try {
            $option = QuestionOption::query()->findOrFail($id);
            $option->update($data);

            return $this->ok('Question option updated successfully.', $option->fresh(), 200);
        } catch (ModelNotFoundException $e) {
            return $this->fail('Question option not found.', $e, 404);
        } catch (Throwable $e) {
            return $this->fail('Failed to update question option.', $e, 500);
        }
    }

    /**
     * Delete a question option by its ID.
     *
     * @param int $id The ID of the question option to delete.
     *
     * @return array<string, mixed> The result of the operation with status.
     */
    public function destroy(int $id): array
    {
        try {
            $option = QuestionOption::query()->findOrFail($id);
            $option->delete();

            return $this->ok('Question option deleted successfully.', null, 200);
        } catch (ModelNotFoundException $e) {
            return $this->fail('Question option not found.', $e, 404);
        } catch (Throwable $e) {
            return $this->fail('Failed to delete question option.', $e, 500);
        }
    }
}
