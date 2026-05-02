<?php

namespace Modules\CommunicationModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Modules\CommunicationModule\Database\Factories\ChatMessageFactory;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = ['chat_thread_id', 'sender_id', 'body', 'metadata'];
    protected $casts = ['metadata' => 'array'];

    public function reads(): HasMany
    {
        return $this->hasMany(ChatMessageRead::class);
    }
}
