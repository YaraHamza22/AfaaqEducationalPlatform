<?php

namespace Modules\UserMangementModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Modules\AssesmentModule\Models\Quiz;
use Modules\UserMangementModule\Models\Builders\StudentBuilder;
use Modules\UserMangementModule\Enums\EducationalLevel;
// use Modules\UserMangementModule\Database\Factories\StudentFactory;

class Student extends Model
{
    use HasFactory, SoftDeletes ,Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'educational_level',
        'country',
        'bio',
        'specialization',
        'joined_at'

    ];

    protected function casts(): array
    {
        return [
            'educational_level' =>EducationalLevel::class,
            'joined_at' => 'datetime'

        ];
    }

    public function newEloquentBuilder($query): StudentBuilder
    {
        return new StudentBuilder($query);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function quizzes(){
        return $this->belongsToMany(Quiz::class,'quiz_student');
    }
}