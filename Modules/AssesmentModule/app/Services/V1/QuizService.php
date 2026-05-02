<?php

namespace Modules\AssesmentModule\Services\V1;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\AssesmentModule\Enums\QuizStatus;
use Modules\AssesmentModule\Enums\QuizType;
use Modules\AssesmentModule\Models\Quiz;
use Throwable;

class QuizService extends BaseService
{
    public function index(array $filters = [], int $perPage = 15): array
    {
        try {
            $data = Quiz::query()
                ->with(['quizable', 'instructor'])
                ->filter($filters)
                ->paginate($perPage);

            return $this->ok('Quiz fetched successfully.', $data, 200);
        } catch (Throwable $e) {
            return $this->fail('Failed to fetch quizzes.', $e, 500);
        }
    }

    public function show(int $id): array
    {
        try {
            $quiz = Quiz::query()
                ->with(['questions.options', 'quizable', 'instructor'])
                ->findOrFail($id);

            return $this->ok('Quiz fetched successfully.', $quiz);
        } catch (ModelNotFoundException $e) {
            return $this->fail('Quiz not found.', $e, 404);
        } catch (Throwable $e) {
            return $this->fail('Failed to fetch quiz.', $e);
        }
    }

    public function store(array $data): array
    {
        try {
            $data = $this->preparePayload($data, true);

            $quiz = DB::transaction(function () use ($data) {
                return Quiz::query()->create($data);
            });

            return $this->ok(
                'Quiz created successfully.',
                $quiz->fresh()->load(['quizable', 'instructor'])
            );
        } catch (Throwable $e) {
            return $this->fail('Failed to create quiz.', $e, 500, [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }

    public function update(Quiz $quiz, array $data): array
    {
        try {
            $data = $this->preparePayload($data, false);

            DB::transaction(function () use ($quiz, $data) {
                $quiz->fill($data);
                $quiz->save();
            });

            return $this->ok(
                'Quiz updated successfully.',
                $quiz->fresh()->load(['quizable', 'instructor'])
            );
        } catch (Throwable $e) {
            return $this->fail('Failed to update quiz.', $e);
        }
    }

    public function publish(Quiz $quiz): array
    {
        try {
            $quiz->update([
                'status' => QuizStatus::PUBLISHED->value,
            ]);

            return $this->ok(
                'Quiz published successfully.',
                $quiz->fresh()->load(['quizable', 'instructor'])
            );
        } catch (Throwable $e) {
            return $this->fail('Failed to publish quiz', $e, 500);
        }
    }

    public function unpublish(Quiz $quiz): array
    {
        try {
            if ($quiz->status !== QuizStatus::PUBLISHED) {
                return $this->ok(
                    'Quiz is already unpublished (draft).',
                    $quiz->load(['quizable', 'instructor'])
                );
            }

            if ($quiz->attempts()->where('status', 'in_progress')->exists()) {
                return $this->fail(
                    'Cannot unpublish quiz because an attempt is currently in progress.',
                    null,
                    422
                );
            }

            $quiz->update([
                'status' => QuizStatus::DRAFT->value,
            ]);

            return $this->ok(
                'Quiz unpublished successfully.',
                $quiz->fresh()->load(['quizable', 'instructor'])
            );
        } catch (Throwable $e) {
            return $this->fail('Failed to unpublish quiz', $e, 500);
        }
    }

    public function destroy(Quiz $quiz): array
    {
        try {
            DB::transaction(function () use ($quiz) {
                $quiz->delete();
            });

            return $this->ok('Quiz deleted successfully.');
        } catch (Throwable $e) 
        {
            return $this->fail('Failed to delete quiz.', $e);
        }
    }

    public function archive(Quiz $quiz): array
    {
        try {
            $quiz->update([
                'status' => QuizStatus::ARCHIVED->value,
            ]);

            return $this->ok(
                'Quiz archived successfully',
                $quiz->fresh()->load(['quizable', 'instructor'])
            );
        } catch (Throwable $e) {
            return $this->fail('Failed to archive quiz', $e);
        }
    }

    public function preparePayload(array $data, bool $requireOwner = true): array
    {
        if (isset($data['status'])) {
            $status = $data['status'] instanceof QuizStatus
                ? $data['status']
                : QuizStatus::tryFrom((string) $data['status']);

            if (! $status) {
                throw new InvalidArgumentException('Invalid quiz status');
            }

            $data['status'] = $status->value;
        } elseif ($requireOwner) {
            $data['status'] = QuizStatus::DRAFT->value;
        }

        if (isset($data['quizable_type'])) {
            $type = $data['quizable_type'] instanceof QuizType
                ? $data['quizable_type']
                : QuizType::tryFrom((string) $data['quizable_type']);

            if (! $type) {
                throw new InvalidArgumentException('Invalid quiz type');
            }

            $data['quizable_type'] = $type->value;
        }

        if (! empty($data['quizable_type']) && ! empty($data['quizable_id'])) {
            unset($data['course_id'], $data['unit_id'], $data['lesson_id']);
            return $data;
        }

        $legacyMap = [
            'course_id' => QuizType::COURSE,
            'unit_id'   => QuizType::UNIT,
            'lesson_id' => QuizType::LESSON,
        ];

        $providedOwners = [];

        foreach ($legacyMap as $field => $enum) {
            if (! empty($data[$field])) {
                $providedOwners[$field] = $enum;
            }
        }

        if (count($providedOwners) > 1) {
            throw new InvalidArgumentException(
                'Quiz must belong to exactly one owner: course, unit, or lesson'
            );
        }

        if (count($providedOwners) === 1) {
            $field = array_key_first($providedOwners);
            $data['quizable_type'] = $providedOwners[$field]->value;
            $data['quizable_id'] = $data[$field];
        } elseif ($requireOwner) {
            throw new InvalidArgumentException('Quiz owner is required');
        }

        unset($data['course_id'], $data['unit_id'], $data['lesson_id']);

        return $data;
    }
}