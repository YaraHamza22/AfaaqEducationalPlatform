<?php

namespace Modules\AssesmentModule\Services\V1;

use Modules\AssesmentModule\Models\Question;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use function collect;
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
     * Fetch a paginated list of questions based on the given filters with options.
     */
    public function indexWithOptions(array $filters = [], int $perPage = 15)
    {
        try {
            return Question::query()
                ->with('options')
                ->filter($filters)
                ->paginate($perPage);
        } catch (Throwable $e) {
            throw new \Exception('Failed to fetch questions: ' . $e->getMessage());
        }
    }

    /**
     * Store a new question with its options.
     */
    public function storeWithOptions(array $data)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            $questionData = \Illuminate\Support\Arr::except($data, ['options']);
            $question = Question::create($questionData);

            if (isset($data['options']) && is_array($data['options'])) {
                foreach ($data['options'] as $option) {
                    $question->options()->create($option);
                }
            }

            return $question;
        });
    }

    /**
     * Store multiple questions with their options in bulk.
     */
    public function storeBulkWithOptions(array $questionsData)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($questionsData) {
            $createdQuestions = [];
            foreach ($questionsData as $data) {
                $createdQuestions[] = $this->storeWithOptions($data);
            }
            return collect($createdQuestions);
        });
    }

    /**
     * Retrieve a specific question by its ID with options.
     */
    public function showWithOptions(int $id)
    {
        try {
            $question = Question::with('options')->find($id);

            if (!$question) {
                throw new \Exception('Question not found');
            }

            return $question;
        } catch (Throwable $e) {
            throw new \Exception('Failed to retrieve question: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing question and its options.
     */
    public function updateWithOptions(int $id, array $data)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($id, $data) {
            $question = Question::findOrFail($id);
            
            $questionData = \Illuminate\Support\Arr::except($data, ['options']);
            $question->update($questionData);

            if (isset($data['options']) && is_array($data['options'])) {
                $incomingOptionIds = collect($data['options'])->pluck('id')->filter()->toArray();
                
                // Delete options not in the request
                $question->options()->whereNotIn('id', $incomingOptionIds)->delete();

                foreach ($data['options'] as $optionData) {
                    if (isset($optionData['id'])) {
                        $question->options()->where('id', $optionData['id'])->update(\Illuminate\Support\Arr::except($optionData, ['id']));
                    } else {
                        $question->options()->create($optionData);
                    }
                }
            }

            return $question->load('options');
        });
    }

    /**
     * Bulk delete questions.
     */
    public function bulkDestroy(array $ids)
    {
        try {
            return Question::whereIn('id', $ids)->delete();
        } catch (Throwable $e) {
            throw new \Exception('Failed to bulk delete questions: ' . $e->getMessage());
        }
    }

    /**
     * Original index method for backward compatibility if needed.
     */
    public function index(array $filters = [], int $perPage = 15)
    {
        return $this->indexWithOptions($filters, $perPage);
    }

    public function show(int $id)
    {
        return $this->showWithOptions($id);
    }

    public function store(array $data)
    {
        return $this->storeWithOptions($data);
    }

    public function update(int $id, array $data)
    {
        return $this->updateWithOptions($id, $data);
    }

    public function destroy(int $id)
    {
        try {
            $question = Question::findOrFail($id);
            $question->delete();
        } catch (Throwable $e) {
            throw new \Exception('Failed to delete question: ' . $e->getMessage());
        }
    }
}
