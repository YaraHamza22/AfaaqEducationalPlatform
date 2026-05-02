<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            $table->index(['quiz_id', 'student_id', 'is_passed'], 'attempts_quiz_student_pass_idx');
            $table->index(['student_id', 'status'], 'attempts_student_status_idx');
        });

        Schema::create('course_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')
                ->constrained('courses', 'course_id')
                ->cascadeOnDelete();
            $table->foreignId('student_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->decimal('weighted_percentage', 5, 2);
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();

            $table->unique(['course_id', 'student_id'], 'course_certificates_unique_course_student');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_certificates');
    }
};
