<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateAnswersTable
 *
 * This migration creates the `answers` table, which stores user answers
 * for assessment attempts. Each answer is uniquely associated with
 * a specific attempt and question.
 *
 * The table supports:
 * - Multiple question types (MCQ, boolean, text-based)
 * - Grading metadata (correctness, score, grader, grading time)
 * - Referential integrity with cascading and restricted deletes
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the `answers` table with foreign key relationships to:
     * - attempts
     * - questions
     * - question_options (optional)
     * - users (grader)
     *
     * Ensures that each question can only be answered once per attempt.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('answers', function (Blueprint $table) {
            $table->id();

            /** @var int $attempt_id Reference to the assessment attempt */
            $table->foreignId('attempt_id')
                ->constrained('attempts')
                ->cascadeOnDelete();

            /** @var int|null $selected_option_id Selected option for MCQ questions */
            $table->foreignId('selected_option_id')
                ->nullable()
                ->constrained('question_options')
                ->nullOnDelete();

            /** @var int $question_id Reference to the answered question */
            $table->foreignId('question_id')
                ->constrained('questions')
                ->restrictOnDelete();

            /** @var bool|null $boolean_answer Answer for true/false questions */
            $table->boolean('boolean_answer')->nullable();

            /** @var array|string|null $answer_text JSON-based answer for text or complex inputs */
            $table->json('answer_text')->nullable();

            /** @var bool|null $is_correct Indicates whether the answer is correct */
            $table->boolean('is_correct')->nullable();

            /** @var int|null $question_score Score awarded for this answer */
            $table->unsignedInteger('question_score')->nullable();

            /** @var \Illuminate\Support\Carbon|null $graded_at Timestamp of grading */
            $table->timestamp('graded_at')->nullable();

            /** @var int|null $graded_by User who graded the answer */
            $table->foreignId('graded_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

            /** Ensure a question is answered only once per attempt */
            $table->unique(['attempt_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the `answers` table.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
