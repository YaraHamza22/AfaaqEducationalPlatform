<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('quiz_id')
                ->constrained('quizzes')
                ->cascadeOnDelete();

            /** @var string $type Type of question: MCQ, True/False, or Text */
            $table->string('type')->default('multiple_choice');

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
