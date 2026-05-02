<?php

namespace Modules\CommunicationModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\CommunicationModule\Database\Factories\OfflineSyncLogFactory;

class OfflineSyncLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'offline_package_id', 'device_id', 'action', 'payload'];
    protected $casts = ['payload' => 'array'];
}
