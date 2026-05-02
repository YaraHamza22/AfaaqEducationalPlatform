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
        if (Schema::hasTable('offline_packages')) {
            return;
        }

        Schema::create('offline_packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('version', 60);
            $table->json('manifest');
            $table->string('file_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['course_id', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_packages');
    }
};
