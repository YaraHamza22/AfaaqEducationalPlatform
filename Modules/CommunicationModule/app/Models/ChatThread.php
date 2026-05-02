<?php

namespace Modules\CommunicationModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\CommunicationModule\Database\Factories\ChatThreadFactory;

class ChatThread extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'course_id', 'created_by', 'is_archived', 'archived_at'];
}
