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
        Schema::create('courses', function (Blueprint $table) {
           $table->id('course_id');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_category_id')->constrained('course_categories','course_category_id')->restrictOnDelete();
            $table->json('title')->nullable(); // translatable: en, ar
            $table->string('slug', 255)->unique();
            $table->json('description')->nullable(); // translatable: en, ar
            $table->json('objectives')->nullable(); // translatable: en, ar
            $table->json('prerequisites')->nullable(); // translatable: en, ar
            $table->integer('actual_duration_hours');

            $table->string('language', 10)->default('ar');
            $table->string('status', 20)->default('draft'); // draft, review, published, archived
            $table->decimal('min_score_to_pass', 5, 2)->default(60.00);
            $table->boolean('is_offline_available')->default(false);
            $table->string('course_delivery_type', 50)->default('self_paced'); // self_paced, interactive, hybrid
            $table->string('difficulty_level', 20)->nullable(); // beginner, intermediate, advanced
            $table->decimal('average_rating', 3, 2)->default(0.00);
            $table->integer('total_ratings')->default(0);

            $table->timestamp('published_at')->nullable();
            $table->softDeletes('deleted_at');
            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('published_at');
            $table->index('deleted_at');
            $table->index('average_rating');
            $table->index('total_ratings');
            // fast search for courses by status and published_at at the same time
            $table->index(['status', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
