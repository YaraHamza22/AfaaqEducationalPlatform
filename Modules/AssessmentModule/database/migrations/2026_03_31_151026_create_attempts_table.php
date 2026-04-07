<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
/**
 * Class CreateAttemptsTable
 *
 * This migration creates the `attempts` table, which represents
 * a student's attempt to complete a quiz.
 *
 * Each attempt tracks:
 * - The quiz and student relationship
 * - Attempt ordering and uniqueness
 * - Progress and grading lifecycle
 * - Scoring, timing, and pass/fail status
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the `attempts` table with foreign key relationships to:
     * - quizzes
     * - users (students)
     * - users (grader)
     *
     * Enforces:
     * - Unique attempt number per student per quiz
     * - Indexed access for common query patterns
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('attempts', function (Blueprint $table) {
            $table->id();

            /** @var int $quiz_id Reference to the quiz */
            $table->foreignId('quiz_id')
                ->constrained('quizzes')
                ->restrictOnDelete();

            /** @var int $student_id Reference to the student (user) */
            $table->foreignId('student_id')
                ->constrained('users')
                ->restrictOnDelete();

            /** @var int $attempt_number Sequential attempt number per quiz */
            $table->unsignedInteger('attempt_number')->default(1);

            /** @var int|null $score Final score for the attempt */
            $table->unsignedInteger('score')->nullable();

            /** @var string $status Current attempt lifecycle status */
            $table->enum('status', ['in_progress', 'submitted', 'graded'])
                ->default('in_progress');

            /** @var bool|null $is_passed Indicates whether the student passed */
            $table->boolean('is_passed')->nullable();

            /** @var int|null $time_spent_seconds Total time spent on the attempt */
            $table->unsignedInteger('time_spent_seconds')->nullable();

            /** @var \Illuminate\Support\Carbon|null $start_at Attempt start timestamp */
            $table->timestamp('start_at')->nullable();

            /** @var \Illuminate\Support\Carbon|null $ends_at Attempt end timestamp */
            $table->timestamp('ends_at')->nullable();
            /** @var \Illuminate\Support\Carbon|null $submitted_at Attempt end timestamp */
            $table->timestamp('submitted_at')->nullable();

            /** @var \Illuminate\Support\Carbon|null $graded_at Attempt grading timestamp */
            $table->timestamp('graded_at')->nullable();

            /** @var int|null $graded_by User who graded the attempt */
            $table->foreignId('graded_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

            /** Ensure unique attempt numbering per student per quiz */
            $table->unique(['quiz_id', 'student_id', 'attempt_number']);

            /** Optimize common quiz/student status queries */
            $table->index(['quiz_id', 'student_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the `attempts` table.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('attempts');
    }
};
