<?php

namespace Modules\CommunicationModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\CommunicationModule\Database\Factories\ForumPostReportFactory;

class ForumPostReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'forum_post_id',
        'reporter_id',
        'reason',
        'details',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];
}
