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
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
             /** @var int $question_id Reference to the parent question */
            $table->foreignId('question_id')
                ->constrained('questions')
                ->cascadeOnDelete();

            /** @var array|string $option_text JSON-based option content (supports localization) */
            $table->json('option_text');

            /** @var bool $is_correct Indicates whether this option is correct */
            $table->boolean('is_correct')->default(true);

            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_options');
    }
};
