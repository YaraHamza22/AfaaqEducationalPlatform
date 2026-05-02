<?php

namespace Modules\AssesmentModule\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\LearningModule\Models\Course;
use Modules\LearningModule\Models\Unit;
use Modules\LearningModule\Models\Lesson;
use Illuminate\Database\Eloquent\Relations\Relation;

class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'AssesmentModule';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'course' => Course::class,
            'unit' => Unit::class,
            'lesson' => Lesson::class
        ]);
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
