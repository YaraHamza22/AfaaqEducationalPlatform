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
        Schema::create('assessment_progress_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('student_id');
            $table->decimal('weighted_percentage', 5, 2)->default(0);
            $table->unsignedInteger('attempts_used')->default(0);
            $table->unsignedInteger('attempts_left')->default(0);
            $table->timestamp('snapshot_date');
            $table->timestamps();
            $table->index(['course_id', 'snapshot_date']);
            $table->index(['student_id', 'snapshot_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_progress_snapshots');
    }
};
