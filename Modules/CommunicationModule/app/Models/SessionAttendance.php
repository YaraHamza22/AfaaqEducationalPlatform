<?php

namespace Modules\CommunicationModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\CommunicationModule\Database\Factories\SessionAttendanceFactory;

class SessionAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'virtual_session_id',
        'user_id',
        'joined_at',
        'left_at',
        'duration_minutes',
    ];
}
