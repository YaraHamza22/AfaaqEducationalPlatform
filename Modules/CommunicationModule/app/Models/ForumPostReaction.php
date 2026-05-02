<?php

namespace Modules\CommunicationModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\CommunicationModule\Database\Factories\ForumPostReactionFactory;

class ForumPostReaction extends Model
{
    use HasFactory;

    protected $fillable = ['forum_post_id', 'user_id', 'reaction'];
}
