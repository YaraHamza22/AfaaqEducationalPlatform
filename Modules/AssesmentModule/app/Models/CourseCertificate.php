<?php

namespace Modules\AssesmentModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\LearningModule\Models\Course;
use Modules\UserMangementModule\Models\User;

class CourseCertificate extends Model
{
    protected $fillable = [
        'course_id',
        'student_id',
        'weighted_percentage',
        'issued_at',
    ];

    protected $casts = [
        'weighted_percentage' => 'decimal:2',
        'issued_at' => 'datetime',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
