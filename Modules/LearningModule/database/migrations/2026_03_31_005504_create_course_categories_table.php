<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *  * name is JSON (en, ar). Uniqueness on English value via generated column name_en (MySQL).
     */

    public function up(): void
    {
        Schema::create('course_categories', function (Blueprint $table) {
            $table->id('course_category_id');
            $table->json('name')->nullable(); // translatable: en, ar
            $table->string('slug', 100)->unique();
            $table->json('description')->nullable(); // translatable: en, ar
            $table->boolean('is_active')->default(true);
            $table->text('target_audience')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });

          // MySQL: generated column for name.en so we can enforce UNIQUE at DB level
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE course_categories ADD name_en VARCHAR(100) GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) VIRTUAL"
            );
            Schema::table('course_categories', function (Blueprint $table) {
                $table->unique('name_en');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_categories');
    }
};
