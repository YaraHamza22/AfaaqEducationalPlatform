<?php

namespace Modules\CommunicationModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\CommunicationModule\Database\Factories\VirtualSessionFactory;

class VirtualSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'integration_id',
        'host_id',
        'provider',
        'title',
        'description',
        'starts_at',
        'ends_at',
        'status',
        'provider_event_id',
        'join_url',
        'metadata',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'metadata' => 'array',
    ];
}
