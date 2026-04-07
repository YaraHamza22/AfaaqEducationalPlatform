<?php

namespace Modules\LearningModule\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'LearningModule';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     */
    public function boot(): void
    {
        parent::boot();

        // Configure route model binding for models with slug support
        // Course and CourseCategory need explicit binding for slug + ID fallback support

        Route::bind('course', function ($value) {
            // Try slug first, fallback to course_id for backward compatibility
            return \Modules\LearningModule\Models\Course::where('slug', $value)
                ->orWhere('course_id', $value)
                ->firstOrFail();
        });

        Route::bind('courseCategory', function ($value) {
            // Try slug first, fallback to course_category_id for backward compatibility
            return \Modules\LearningModule\Models\CourseCategory::where('slug', $value)
                ->orWhere('course_category_id', $value)
                ->firstOrFail();
        });

        Route::bind('course_category', function ($value) {
            return \Modules\LearningModule\Models\CourseCategory::where('slug', $value)
                ->orWhere('course_category_id', $value)
                ->firstOrFail();
        });
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')->group(module_path($this->name, '/routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes(): void
    {
        Route::middleware('api')->prefix('api')->name('api.')->group(module_path($this->name, '/routes/api.php'));
    }
}
