<?php

namespace Modules\AssesmentModule\Services\V1;

use Illuminate\Support\Collection;
use Modules\AssesmentModule\Enums\AttemptStatus;
use Modules\AssesmentModule\Enums\QuizType;
use Modules\AssesmentModule\Models\Attempt;
use Modules\AssesmentModule\Models\Quiz;
use Modules\LearningModule\Models\Lesson;
use Modules\LearningModule\Models\Unit;

class CourseQuizProgressService
{
    public function build(int $courseId, int $studentId): array
    {
        $unitIds = Unit::query()->where('course_id', $courseId)->pluck('unit_id');
        $lessonIds = Lesson::query()->whereIn('unit_id', $unitIds)->pluck('lesson_id');

        $quizzes = Quiz::query()
            ->where(function ($query) use ($courseId, $unitIds, $lessonIds) {
                $query->where(function ($q) use ($courseId) {
                    $q->where('quizable_type', QuizType::COURSE->value)
                        ->where('quizable_id', $courseId);
                })->orWhere(function ($q) use ($unitIds) {
                    $q->where('quizable_type', QuizType::UNIT->value)
                        ->whereIn('quizable_id', $unitIds);
                })->orWhere(function ($q) use ($lessonIds) {
                    $q->where('quizable_type', QuizType::LESSON->value)
                        ->whereIn('quizable_id', $lessonIds);
                });
            })
            ->with(['attempts' => function ($query) use ($studentId) {
                $query->where('student_id', $studentId)
                    ->orderByDesc('score');
            }])
            ->get();

        $gradedStatus = AttemptStatus::GRADED->value;

        $quizRows = $quizzes->map(function (Quiz $quiz) use ($gradedStatus) {
            $attempts = $quiz->attempts;
            $gradedAttempts = $attempts->where('status', $gradedStatus);

            $bestGradedAttempt = $gradedAttempts->first();
            $hasPassed = $gradedAttempts->contains(fn (Attempt $attempt) => $attempt->is_passed === true);
            $attemptsUsed = $attempts->count();
            $bestScore = (int) ($bestGradedAttempt?->score ?? 0);
            $maxScore = (int) ($quiz->max_score ?? 0);

            return [
                'quiz_id' => $quiz->id,
                'max_score' => $maxScore,
                'best_score' => $bestScore,
                'attempts_used' => $attemptsUsed,
                'attempts_left' => max(0, 3 - $attemptsUsed),
                'pass_lock' => $hasPassed,
                'is_passed' => $hasPassed,
                'is_graded' => $gradedAttempts->isNotEmpty(),
            ];
        });

        $earnedTotal = (int) $quizRows->sum('best_score');
        $maxTotal = (int) $quizRows->sum('max_score');
        $percentage = $maxTotal > 0 ? round(($earnedTotal / $maxTotal) * 100, 2) : 0.0;
        $allQuizzesGraded = $quizRows->every(fn (array $quizRow) => $quizRow['is_graded'] === true);

        return [
            'course_id' => $courseId,
            'student_id' => $studentId,
            'required_quizzes_count' => $quizzes->count(),
            'all_required_quizzes_graded' => $allQuizzesGraded,
            'earned_total' => $earnedTotal,
            'max_total' => $maxTotal,
            'weighted_percentage' => $percentage,
            'quizzes' => $quizRows->values()->all(),
        ];
    }
}
