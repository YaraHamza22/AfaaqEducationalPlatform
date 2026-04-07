<?php

namespace App\Traits;

use Spatie\Activitylog\Traits\LogsActivity as SpatieLogsActivity;
use Spatie\Activitylog\LogOptions;

trait LogsActivity
{
    use SpatieLogsActivity;

    /**
     * Get the default activity log options.
     * Override this method in your models to customize logging.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName($this->getLogName())
            ->setDescriptionForEvent(fn(string $eventName) => $this->getDescriptionForEvent($eventName));
    }

    /**
     * Get the log name for this model.
     * Override in your model if needed.
     */
    protected function getLogName(): string
    {
        return strtolower(class_basename($this));
    }

    /**
     * Get the description for the event.
     * Override in your model if needed.
     */
    protected function getDescriptionForEvent(string $eventName): string
    {
        $modelName = class_basename($this);
        return "{$modelName} was {$eventName}";
    }
}