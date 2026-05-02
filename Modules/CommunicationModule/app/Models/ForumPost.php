<?php

namespace Modules\CommunicationModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\CommunicationModule\Database\Factories\ForumPostFactory;

class ForumPost extends Model
{
    use HasFactory;

    protected $fillable = ['forum_thread_id', 'author_id', 'body'];
}
