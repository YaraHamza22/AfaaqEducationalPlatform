<?php

namespace Modules\CommunicationModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\CommunicationModule\Database\Factories\ForumThreadFactory;

class ForumThread extends Model
{
    use HasFactory;

    protected $fillable = ['course_id', 'author_id', 'title', 'body', 'is_pinned', 'is_locked'];
}
