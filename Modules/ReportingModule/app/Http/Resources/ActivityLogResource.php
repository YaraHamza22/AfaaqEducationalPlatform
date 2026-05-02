<?php

namespace Modules\ReportingModule\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Spatie\Activitylog\Models\Activity */
class ActivityLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'log_name' => $this->log_name,
            'description' => $this->description,
            'event' => $this->event,
            'subject_type' => $this->subject_type,
            'subject_id' => $this->subject_id,
            'causer_type' => $this->causer_type,
            'causer_id' => $this->causer_id,
            'properties' => $this->properties,
            'batch_uuid' => $this->batch_uuid ?? null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'causer' => $this->when(
                $this->relationLoaded('causer') && $this->causer !== null,
                fn () => [
                    'id' => $this->causer->id,
                    'name' => $this->causer->name ?? null,
                    'email' => $this->causer->email ?? null,
                ]
            ),
        ];
    }
}
