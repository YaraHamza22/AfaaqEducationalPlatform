<?php

namespace Modules\CommunicationModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\CommunicationModule\Database\Factories\ChatParticipantFactory;

class ChatParticipant extends Model
{
    use HasFactory;

    protected $fillable = ['chat_thread_id', 'user_id', 'role'];
}
