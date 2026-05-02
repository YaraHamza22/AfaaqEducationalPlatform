<?php

namespace Modules\CommunicationModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\CommunicationModule\Database\Factories\OfflinePackageFactory;

class OfflinePackage extends Model
{
    use HasFactory;

    protected $fillable = ['course_id', 'created_by', 'version', 'manifest', 'file_url', 'is_active'];
    protected $casts = ['manifest' => 'array', 'is_active' => 'boolean'];
}
