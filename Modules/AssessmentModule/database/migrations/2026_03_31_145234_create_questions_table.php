<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

  /**
 * Class CreateQuestionsTable
 *
 * This migration creates the `questions` table, which stores the questions
 * for each quiz, including multiple-choice questions (MCQ), true/false questions, 
 * and text-based questions.
 *
 * Each question:
 * - Belongs to a specific quiz
 * - Has a type (MCQ, true/false, or text)
 * - Includes text content (supports localization via JSON)
 * - Supports point allocation and ordering within a quiz
 * - Tracks if the question is required and its index for ordering
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the `questions` table with a foreign key relationship to the `quizzes` table.
     * It also supports soft deletes for tracking deleted questions without losing data.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();

            /** @var int $quiz_id Reference to the parent quiz */
            $table->foreignId('quiz_id')
                ->constrained('quizzes')
                ->cascadeOnDelete();

            /** @var string $type Type of question: MCQ, True/False, or Text */
            $table->enum('type', ['mcq', 'true_false', 'text'])->default('mcq');

            /** @var array|string $question_text JSON-based content for the question */
            $table->json('question_text');

            /** @var int $point Points allocated for the question */
            $table->unsignedInteger('point')->default(1);

            /** @var bool $is_required Indicates if this question is mandatory */
            $table->boolean('is_required')->default(true);

            /** @var int $order_index Ordering index for the question within a quiz */
            $table->unsignedInteger('order_index')->default(1);

            /** @var \Illuminate\Support\Carbon|null $deleted_at Timestamp for soft deletion */
            $table->softDeletes();

            $table->timestamps();

            /** Ensure each question has a unique order index per quiz */
            $table->unique(['quiz_id', 'order_index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
