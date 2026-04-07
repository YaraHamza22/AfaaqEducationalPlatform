<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateQuizzesTable
 *
 * This migration creates the `quizzes` table, which stores the quizzes or assignments 
 * for a course. It includes fields for quiz metadata, status, instructor details, 
 * scoring, and availability.
 *
 * Each quiz:
 * - Belongs to a course and an instructor
 * - Supports multiple types (quiz, assignment, practice)
 * - Includes a title and description (supports multilingual via JSON)
 * - Tracks timing, scoring, and auto-grading settings
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the `quizzes` table with relationships to the `courses` and `users` tables.
     * It also supports polymorphic relationships (`quizable`), timing, grading, and status management.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();

            /** @var int $course_id Reference to the parent course */
            $table->foreignId('course_id')
                ->constrained('courses', 'course_id')
                ->cascadeOnDelete();

            /** @var int $instructor_id Reference to the instructor (user) */
            $table->foreignId('instructor_id')
                ->constrained('users')
                ->cascadeOnDelete();

            /** @var int|null $quizable_id Polymorphic reference (e.g., other types like 'event') */
            $table->unsignedBigInteger('quizable_id')->nullable();

            /** @var string|null $quizable_type Polymorphic type for flexible relationships */
            $table->string('quizable_type')->nullable();

            /** @var array|string $title JSON-based title (supports localization) */
            $table->json('title');

            /** @var array|string|null $description JSON-based description (supports localization) */
            $table->json('description')->nullable();

            /** @var string $type Quiz type (quiz, assignment, practice) */
            $table->enum('type', ['quiz', 'assignment', 'practice'])->default('quiz');

            /** @var int $max_score Maximum score for the quiz */
            $table->unsignedInteger('max_score')->default(100);

            /** @var int|null $passing_score Minimum score required to pass the quiz */
            $table->unsignedInteger('passing_score')->nullable();

            /** @var string $status Current status of the quiz (published, draft) */
            $table->enum('status', ['published', 'draft'])->default('draft');

            /** @var bool $auto_grade_enabled Indicates if auto-grading is enabled */
            $table->boolean('auto_grade_enabled')->default(true);

            /** @var \Illuminate\Support\Carbon|null $available_from The timestamp when the quiz is available */
            $table->timestamp('available_from')->nullable();

            /** @var \Illuminate\Support\Carbon|null $due_date The deadline for quiz submissions */
            $table->timestamp('due_date')->nullable();

            /** @var int|null $duration_minutes The total duration of the quiz in minutes */
            $table->unsignedBigInteger('duration_minutes')->nullable();

            $table->timestamps();
            

            /** Index on course_id and status for faster querying by course and quiz status */
            $table->index(['course_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the `quizzes` table.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};