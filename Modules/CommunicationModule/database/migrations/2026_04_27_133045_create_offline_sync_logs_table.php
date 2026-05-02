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
        if (Schema::hasTable('offline_sync_logs')) {
            return;
        }

        Schema::create('offline_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('offline_package_id')->nullable()->constrained('offline_packages')->nullOnDelete();
            $table->string('device_id', 120);
            $table->string('action', 60);
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'device_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_sync_logs');
    }
};
