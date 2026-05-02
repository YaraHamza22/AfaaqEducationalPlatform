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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('instructor_id')->constrained('users')->cascadeOnDelete(); 

            $table->json('title');

            $table->json('description')->nullable();

            $table->string('status')->default('draft');

            $table->string('type')->default('quiz');

            $table->timestamp('available_from')->nullable();

            $table->timestamp('due_date')->nullable();

            $table->morphs('quizable');
              /** @var int $max_score Maximum score for the quiz */
            $table->unsignedInteger('max_score')->default(100);

            /** @var int|null $passing_score Minimum score required to pass the quiz */
            $table->unsignedInteger('passing_score')->nullable();

            /** @var bool $auto_grade_enabled Indicates if auto-grading is enabled */
            $table->boolean('auto_grade_enabled')->default(true);

            $table->unsignedBigInteger('duration_minutes')->nullable();

            $table->timestamps();

            $table->index(['quizable_type','quizable_id','status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('quizzes');
        Schema::enableForeignKeyConstraints();
    }
};
