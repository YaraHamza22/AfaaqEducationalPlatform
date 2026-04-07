<?php

namespace Modules\LearningModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\LearningModule\Database\Factories\EnrollementLessonFactory;

class EnrollementLesson extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): EnrollementLessonFactory
    // {
    //     // return EnrollementLessonFactory::new();
    // }
}
