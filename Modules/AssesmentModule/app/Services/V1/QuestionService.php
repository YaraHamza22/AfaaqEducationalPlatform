<?php

namespace Modules\AssesmentModule\Services\V1;

use Modules\AssesmentModule\Models\Question;
use Throwable;

/**
 * QuestionService handles the business logic for managing questions, including:
 * - Storing new questions.
 * - Retrieving a specific question by ID.
 * - Updating an existing question.
 * - Deleting a question.
 *
 * @package Modules\AssesmentModule\Services\V1
 */
class QuestionService extends BaseService
{
    /**
     * Fetch a paginated list of questions based on the given filters.
     *
     * @param array $filters The filters to apply to the question query (e.g., quiz_id, type).
     * @param int $perPage The number of questions per page (default is 15).
     * @return mixed The paginated list of questions.
     *
     * @throws \Exception If an error occurs while fetching the questions.
     */
    public function index(array $filters = [], int $perPage = 15)
    {
        try {
            return Question::query()->filter($filters)->paginate($perPage);
        } catch (Throwable $e) {
            throw new \Exception('Failed to fetch questions: ' . $e->getMessage());
        }
    }

    /**
     * Store a new question with the provided data.
     *
     * @param array $data The data to create the question.
     * @return \Modules\AssesmentModule\Models\Question The created question.
     *
     * @throws Throwable If an error occurs while saving the question.
     */
    public function store(array $data)
    {
        try {
            return Question::create($data);
        } catch (Throwable $e) {
            throw new \Exception('Failed to create question: ' . $e->getMessage());
        }
    }

    /**
     * Retrieve a specific question by its ID.
     *
     * @param int $id The ID of the question to retrieve.
     * @return \Modules\AssesmentModule\Models\Question The retrieved question.
     *
     * @throws Throwable If an error occurs while retrieving the question.
     */
    public function show($id)
    {
        try {
            $question = Question::find($id);

            if (! $question) {
                throw new \Exception('Question not found');
            }

            return $question;
        } catch (Throwable $e) {
            throw new \Exception('Failed to retrieve question: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing question with the provided data.
     *
     * @param int $id The ID of the question to update.
     * @param array $data The data to update the question.
     * @return \Modules\AssesmentModule\Models\Question The updated question.
     *
     * @throws Throwable If an error occurs while updating the question.
     */
    public function update($id, array $data)
    {
        try {
            $question = Question::findOrFail($id);

            $question->update($data);

            return $question;
        } catch (Throwable $e) {
            throw new \Exception('Failed to update question: ' . $e->getMessage());
        }
    }

    /**
     * Delete a question by its ID.
     *
     * @param int $id The ID of the question to delete.
     * @return void
     *
     * @throws Throwable If an error occurs while deleting the question.
     */
    public function destroy($id)
    {
        try {
            $question = Question::findOrFail($id);
            $question->delete();
        } catch (Throwable $e) {
            throw new \Exception('Failed to delete question: ' . $e->getMessage());
        }
    }
}
