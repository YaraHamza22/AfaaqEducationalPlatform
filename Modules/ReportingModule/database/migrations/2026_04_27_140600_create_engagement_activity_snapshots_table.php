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
        Schema::create('engagement_activity_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedInteger('messages_count')->default(0);
            $table->unsignedInteger('forum_posts_count')->default(0);
            $table->unsignedInteger('virtual_sessions_attended')->default(0);
            $table->timestamp('snapshot_date');
            $table->timestamps();
            $table->index(['course_id', 'snapshot_date']);
            $table->index(['user_id', 'snapshot_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('engagement_activity_snapshots');
    }
};
