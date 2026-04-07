<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
/**
 * Class CreateQuestionOptionsTable
 *
 * This migration creates the `question_options` table, which stores
 * selectable options for questions (e.g., multiple-choice questions).
 *
 * Each option:
 * - Belongs to a single question
 * - Supports multilingual or rich text via JSON
 * - Indicates whether it represents the correct answer
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the `question_options` table with a foreign key relationship
     * to the `questions` table and cascades deletion when a question is removed.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();

            /** @var int $question_id Reference to the parent question */
            $table->foreignId('question_id')
                ->constrained('questions')
                ->cascadeOnDelete();

            /** @var array|string $option_text JSON-based option content (supports localization) */
            $table->json('option_text');

            /** @var bool $is_correct Indicates whether this option is correct */
            $table->boolean('is_correct')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the `question_options` table.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('question_options');
    }
};