<?php

namespace Modules\AssessmentModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\AssessmentModule\Database\Factories\QuestionOptionFactory;

class QuestionOption extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): QuestionOptionFactory
    // {
    //     // return QuestionOptionFactory::new();
    // }
}
