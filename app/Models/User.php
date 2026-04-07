<?php

namespace App\Models;


use Modules\LearningModule\Models\Course;
use Modules\LearningModule\Models\CourseInstructor;
use Modules\LearningModule\Models\Enrollment;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject
{ 
    use HasRoles;
   
    /**
     * Represents a user account in the e-learning platform.
     * Handles authentication, user profiles, and relationships with courses, enrollments, and various role-specific profiles.
     */


    /*
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships

    /**
     * Get the courses created by this user.
     *
     * @return HasMany
     */
    public function createdCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'created_by', 'id');
    }

    /**
     * Get the courses where this user is an instructor.
     *
     * @return BelongsToMany
     */
    public function instructorCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_instructor', 'instructor_id', 'course_id')
            ->using(CourseInstructor::class)
            ->withPivot(['course_instructor_id', 'is_primary', 'assigned_at', 'assigned_by']);
    }

    /**
     * Get the enrollments for this user (as a learner).
     *
     * @return HasMany
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'learner_id', 'id');
    }

    /**
     * Get the courses that this user is enrolled in (as a learner).
     *
     * @return BelongsToMany
     */
    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'enrollments', 'learner_id', 'course_id')
            ->using(Enrollment::class)
            ->withPivot([
                'enrollment_id',
                'enrollment_type',
                'enrollment_status',
                'enrolled_at',
                'enrolled_by',
                'completed_at',
                'progress_percentage',
                'final_grade'
            ]);
    }

    /**
     * Get the activity log causer name.
     * This helps identify who performed the action in activity logs.
     *
     * @return string
     */
    public function getActivitylogCauserName(): string
    {
        return $this->name ?? $this->email ?? "User #{$this->id}";
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
