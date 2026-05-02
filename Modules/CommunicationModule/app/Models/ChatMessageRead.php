<?php

namespace Modules\CommunicationModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\CommunicationModule\Database\Factories\ChatMessageReadFactory;

class ChatMessageRead extends Model
{
    use HasFactory;

    protected $fillable = ['chat_message_id', 'user_id', 'read_at'];
}
