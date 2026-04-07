<?php

/**
 * User model for the UserManagementModule.
 *
 * Logic:
 * - Extends Laravel's `Authenticatable` to act as an auth-capable user model.
 * - Implements `JWTSubject` for JWT token integration.
 *
 * @package Modules\UserManagementModule\Models
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $password
 * @property string|null $phone
 * @property string|null $address
 * @property \Illuminate\Support\Carbon|null $date_of_birth
 * @property string|null $gender
 */

namespace Modules\UserMangementModule\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Modules\LearningModule\Models\Course;
use Modules\LearningModule\Models\CourseInstructor;
use Modules\LearningModule\Models\Enrollment;
use Modules\UserMangementModule\Models\Auditor;
use Modules\UserMangementModule\Models\Builders\UserBuilder;
use Modules\UserMangementModule\Models\Instructor;
use Modules\UserMangementModule\Models\Student;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable implements JWTSubject, HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,  HasRoles, SoftDeletes, CascadeSoftDeletes, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * We define this list to protect against Mass Assignment Vulnerabilities.
     * Only these fields can be updated via `User::create($request->all())`.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'date_of_birth',
        'gender'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $cascadeDeletes = ['studentProfile', 'instructorProfile', 'auditorProfile'];

    protected $guard_name = 'api';

    /**
     * Return the attribute casting map for the model.
     *
     * Logic:
     * - Declares how specific attributes should be cast when accessed or set.
     * - 'email_verified_at' => 'datetime' ensures datetime instance.
     * - 'password' => 'hashed' uses Laravel's native hashed casting when setting passwords.
     * - 'date_of_birth' => 'date' casts to a date instance.
     *
     * @return array<string, string> Array of attribute => cast type.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date'
        ];
    }


    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * Logic:
     * - Returns the model primary key via `$this->getKey()`.

     * - Used by the JWT library as the unique identifier (sub) for the token.
     *
     * @return mixed The primary key value of the user model.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }


    public function getJWTCustomClaims()
    {
        return [];
    }


    /**
     * Create a new Eloquent query builder instance for the model.
     *
     * Logic:
     * - Returns an instance of `UserBuilder` to provide custom query scope/methods
     *   specific to the `User` model.
     * - Allows using fluent, reusable query helpers typed to `UserBuilder`.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Modules\UserManagementModule\Models\Builders\UserBuilder
     */
    public function newEloquentBuilder($query): UserBuilder
    {
        return new UserBuilder($query);
    }



    /**
     * Define a one-to-one relationship to the student profile.
     *
     * Logic:
     * - `hasOne(Student::class)` indicates a user may have a single Student profile.
     * - This relation is included in `cascadeDeletes` so deleting the user will
     *   also remove the related student profile.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function studentProfile()
    {
        return $this->hasOne(Student::class);
    }



    /**
     * Define a one-to-one relationship to the instructor profile.
     *
     * Logic:
     * - `hasOne(instructor::class)` indicates a user may have a single Instructor profile.
     * - Included in `cascadeDeletes` so related instructor profile is removed on user delete.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function instructorProfile()
    {
        return $this->hasOne(Instructor::class);
    }




    /**
     * Define a one-to-one relationship to the auditor profile.
     *
     * Logic:
     * - `hasOne(auditor::class)` indicates a user may have a single Auditor profile.
     * - This relation is included in `cascadeDeletes` so deleting the user will
     *   also remove the related auditor profile.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function auditorProfile()
    {
        return $this->hasOne(Auditor::class);
    }



    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->useFallbackUrl(asset('images/default-avatar.png'));
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(100)
            ->height(100)
            ->nonQueued();

        $this->addMediaConversion('preview')
            ->width(300)
            ->height(300)
            ->queued();
    }


    // LearningModule relations (inverse of Course, CourseInstructor, Enrollment)

    /**
     * Courses created by this user.
     */
    public function createdCourses()
    {
        return $this->hasMany(Course::class, 'created_by', 'id');
    }

    /**
     * Courses where this user is assigned as instructor.
     */
    public function instructedCourses()
    {
        return $this->belongsToMany(Course::class, 'course_instructor', 'instructor_id', 'course_id')
            ->using(CourseInstructor::class)
            ->withPivot(['course_instructor_id', 'is_primary', 'assigned_at', 'assigned_by']);
    }

    /**
     * Courses where this user is enrolled as learner.
     */
    public function enrolledCourses()
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
                'final_grade',
            ]);
    }

    /**
     * Course-instructor pivot records where this user is the instructor.
     */
    public function courseInstructorAssignments()
    {
        return $this->hasMany(CourseInstructor::class, 'instructor_id', 'id');
    }

    /**
     * Course-instructor pivot records assigned by this user.
     */
    public function assignedCourseInstructors()
    {
        return $this->hasMany(CourseInstructor::class, 'assigned_by', 'id');
    }

    /**
     * Enrollments where this user is the learner.
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'learner_id', 'id');
    }

    /**
     * Enrollments created by this user (e.g. admin enrolling a learner).
     */
    public function enrollmentsEnrolledBy()
    {
        return $this->hasMany(Enrollment::class, 'enrolled_by', 'id');
    }
}