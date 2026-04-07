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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id('enrollment_id');
            $table->foreignId('learner_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('course_id')->constrained('courses', 'course_id')->restrictOnDelete();
            $table->foreignId('enrolled_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('enrollment_type', 20)->default('self'); // self, assigned
            $table->string('enrollment_status', 20)->default('active'); // active, completed, dropped, suspended
            $table->timestamp('enrolled_at');

            $table->timestamp('completed_at')->nullable();

            // calculate progress percentage based on the total lessons completed and the total lessons of the course progress percentage = ($completedLessons / $totalLessons) * 100
            $table->decimal('progress_percentage', 5, 2)->default(0.00);

            // final grade for course
            // calculated from quizzes , can't set until course marked completed and progresss percentage = 100%
            $table->decimal('final_grade', 5, 2)->nullable();

            $table->timestamps();

            // Unique constraint to prevent duplicate enrollments
            $table->unique(['learner_id', 'course_id']);

            // Indexes
            $table->index('enrollment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollements');
    }
};
