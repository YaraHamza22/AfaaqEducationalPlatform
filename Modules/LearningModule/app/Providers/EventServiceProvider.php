<?php

namespace Modules\LearningModule\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}

    /**
     * Register model observers.
     */
    public function boot(): void
    {
        parent::boot();

        \Modules\LearningModule\Models\Lesson::observe(\Modules\LearningModule\Observers\LessonObserver::class);
    }
}
