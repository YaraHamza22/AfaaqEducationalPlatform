<?php

namespace Modules\AssesmentModule\Services\V1;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\AssesmentModule\Enums\AttemptStatus;
use Modules\AssesmentModule\Enums\QuestionType;
use Modules\AssesmentModule\Models\Attempt;
use Modules\AssesmentModule\Models\Answer;
use Modules\AssesmentModule\Models\Quiz;
use Throwable;

class AttemptService extends BaseService
{
    public function index(array $filters = [], int $perPage = 15): array
    {
        try {
            $query = Attempt::query()->with(['quiz', 'answers']);

            $data = $query->paginate($perPage);

            return $this->ok('Attempts fetched successfully.', $data);
        } catch (Throwable $e) {
            return $this->fail('Failed to fetch attempts.', $e);
        }
    }

    public function show(Attempt $attempt): array
    {
        try {
            $attempt->load(['quiz.questions.options', 'answers']);

            return $this->ok('Attempt fetched successfully.', $attempt);
        } catch (Throwable $e) {
            return $this->fail('Failed to fetch attempt.', $e);
        }
    }

    public function store(array $data): array
    {
        try {
            $quiz = Quiz::query()->findOrFail($data['quiz_id']);
            $studentId = $data['student_id'] ?? Auth::id();

            $attempt = DB::transaction(function () use ($quiz, $studentId) {
                $attemptQuery = Attempt::query()
                    ->where('quiz_id', $quiz->id)
                    ->where('student_id', $studentId)
                    ->lockForUpdate();

                $passed = (clone $attemptQuery)
                    ->where('status', AttemptStatus::GRADED->value)
                    ->where('is_passed', true)
                    ->exists();

                if ($passed) {
                    return null;
                }

                $open = (clone $attemptQuery)
                    ->whereIn('status', [
                        AttemptStatus::PENDING->value,
                        AttemptStatus::IN_PROGRESS->value,
                        AttemptStatus::SUBMITTED->value,
                    ])
                    ->exists();

                if ($open) {
                    throw new \RuntimeException('You already have an open attempt.');
                }

                $count = (clone $attemptQuery)->count();
                if ($count >= 3) {
                    throw new \RuntimeException('Max attempts reached.');
                }

                $num = ((clone $attemptQuery)->max('attempt_number') ?? 0) + 1;

                return Attempt::query()->create([
                    'quiz_id' => $quiz->id,
                    'student_id' => $studentId,
                    'attempt_number' => $num,
                    'status' => AttemptStatus::PENDING->value,
                ]);
            });

            if (!$attempt) {
                return $this->fail('You already passed this quiz.', null, 422);
            }

            return $this->ok('Attempt created.', $attempt);
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), null, 422);
        } catch (QueryException $e) {
            return $this->fail('Failed to create attempt due to a concurrent request.', $e, 409);
        } catch (Throwable $e) {
            return $this->fail('Failed to create attempt.', $e);
        }
    }

    public function start(Attempt $attempt): array
    {
        if ($attempt->status !== AttemptStatus::PENDING) {
            return $this->fail('Invalid attempt state.', null, 422);
        }

        $attempt->update([
            'status' => AttemptStatus::IN_PROGRESS,
            'start_at' => now(),
            'ends_at' => $attempt->quiz->duration_minutes
                ? now()->addMinutes((int) $attempt->quiz->duration_minutes)
                : null,
        ]);

        return $this->ok('Started.', $attempt);
    }

    public function update(Attempt $attempt, array $data): array
    {
        $attempt->update($data);

        return $this->ok('Attempt updated.', $attempt->fresh());
    }

    public function submit(Attempt $attempt, array $data): array
    {
        if ($attempt->status !== AttemptStatus::IN_PROGRESS) {
            return $this->fail('Invalid state.', null, 422);
        }

        $hasManual = false;
        DB::transaction(function () use ($attempt, $data, &$hasManual) {

            foreach ($data['answers'] as $ans) {

                $question = $attempt->quiz->questions()
                    ->with('options')
              ->find($ans['question_id']);

                $answer = Answer::updateOrCreate(
                    [
                        'attempt_id' => $attempt->id,
                        'question_id' => $question->id
                    ],
                    [
                        'selected_option_id' => $ans['selected_option_id'] ?? null,
                        'answer_text' => $ans['answer_text'] ?? null,
                        'boolean_answer' => $ans['boolean_answer'] ?? null,
                    ]
                );

                if ($question->type === QuestionType::MULTIPLE_CHOICE
                    || $question->type === QuestionType::TRUE_FALSE) {

                    $opt = $question->options
                        ->firstWhere('id', $ans['selected_option_id']);

                    $correct = $opt?->is_correct ?? false;

                    $answer->update([
                        'is_correct' => $correct,
                        'question_score' => $correct ? $question->point : 0
                    ]);
                }

                if ($question->type === QuestionType::SHORT_ANSWER) {
                    $hasManual = true;
                    $answer->update([
                        'is_correct' => null,
                        'question_score' => 0,
                    ]);
                }
            }
            $score = $attempt->answers()->sum('question_score');

            if ($hasManual) {
                $attempt->update([
                    'status' => AttemptStatus::SUBMITTED,
                    'score' => $score,
                    'submitted_at' => now(),
                ]);
            } else {
                $attempt->update([
                    'status' => AttemptStatus::GRADED,
                    'score' => $score,
                    'is_passed' => $score >= $attempt->quiz->passing_score,
                    'submitted_at' => now(),
                    'graded_at' => now(),
                ]);
            }
        });

        return $this->ok('Submitted.', $attempt->fresh());
    }

    public function grade(Attempt $attempt, array $data): array
    {
        if ($attempt->status !== AttemptStatus::SUBMITTED) {
            return $this->fail('Invalid state.', null, 422);
        }

        DB::transaction(function () use ($attempt, $data) {

            foreach ($data['answers'] as $ans) {

                $answer = $attempt->answers()
                    ->where('question_id', $ans['question_id'])
                    ->first();

                $answer->update([
                    'question_score' => $ans['earned_score'],
                    'is_correct' => $ans['is_correct'],
                    'graded_at' => now(),
                ]);
            }

            $score = $attempt->answers()->sum('question_score');

            $attempt->update([
                'status' => AttemptStatus::GRADED,
                'score' => $score,
                'is_passed' => $score >= $attempt->quiz->passing_score,
                'graded_at' => now(),
            ]);
        });

        return $this->ok('Graded.', $attempt->fresh());
    }

    public function destroy(Attempt $attempt): array
    {
        $attempt->delete();
        return $this->ok('Deleted.');
    }
}