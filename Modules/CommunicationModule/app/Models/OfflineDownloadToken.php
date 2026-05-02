<?php

namespace Modules\CommunicationModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\CommunicationModule\Database\Factories\OfflineDownloadTokenFactory;

class OfflineDownloadToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'offline_package_id',
        'user_id',
        'token',
        'device_id',
        'expires_at',
        'revoked_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];
}
