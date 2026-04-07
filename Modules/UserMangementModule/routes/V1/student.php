<?php

use Illuminate\Support\Facades\Route;
use Modules\LearningModule\Http\Controllers\CourseController;
use Modules\LearningModule\Http\Controllers\EnrollmentController;
use Modules\LearningModule\Http\Controllers\LessonController;
use Modules\LearningModule\Http\Controllers\UnitController;
use Modules\ReportingModule\Http\Controllers\StudentDashboardController;

/**
 |----------------------------------------------------
 | Student Dashboard Routes
 | ---------------------------------------------------
 * Routes for learners to access their enrolled content.
 * Security:
 * 1. JWT Auth
 * 2. Student Role
 * 3. CourseAccessScope: Filters all queries to the 'enrollment' table.
 * @prefix api/v1
 * @auth   Required (JWT)
 * @access Student Only
 * @scope  CourseAccessScope (filters courses by student to insure students can only access their enrolled courses)
 */
Route::group(['middleware' => ['auth:api', 'role:student']], function () {
    /**
    |--------------------------------------------------------------------------
    | Student Dashboard (Reporting Module)
    |--------------------------------------------------------------------------
     */
    /**
     * @name   Student Dashboard
     * @path   GET /api/v1/dashboard
     * @desc   Retrieve dashboard data for the authenticated student (progress, enrolled courses, etc.).
     * @controller StudentDashboardController@dashboard
     */
    Route::get('/dashboard', [StudentDashboardController::class, 'dashboard']);

    /**
    |--------------------------------------------------------------------------
    | Course Discovery & Enrollment (Learning Module)
    |--------------------------------------------------------------------------
     */
    /**
     * @name   List Enrollable Courses
     * @path   GET /api/v1/courses/enrollable/list
     * @desc   List courses available for the student to enroll in.
     * @controller CourseController@enrollable
     */
    Route::get('/courses/enrollable/list', [CourseController::class, 'enrollable']);

    /**
     * @name   Enroll in Course
     * @path   POST /api/v1/enrollments
     * @desc   Enroll the authenticated student in a course. Body: course_id, enrollment_type (optional).
     * @body   {course_id: int, enrollment_type?: string}
     * @controller EnrollmentController@store
     */
    Route::post('/enrollments', [EnrollmentController::class, 'store']);

    /**
     * @name   My Enrollments
     * @path   GET /api/v1/enrollments
     * @desc   List enrollments for the authenticated student (filtered by learner_id).
     * @controller EnrollmentController@index
     */
    Route::get('/enrollments', [EnrollmentController::class, 'index']);

    /**
     * @name   View My Enrollment
     * @path   GET /api/v1/enrollments/{enrollment}
     * @desc   View details of one of the student's enrollments.
     * @controller EnrollmentController@show
     */
    Route::get('/enrollments/{enrollment}', [EnrollmentController::class, 'show']);

    /**
     * @name   My Enrollment Progress
     * @path   GET /api/v1/enrollments/{enrollment}/progress
     * @desc   Get progress details for an enrollment (units/lessons completed, percentage).
     * @controller EnrollmentController@getProgress
     */
    Route::get('/enrollments/{enrollment}/progress', [EnrollmentController::class, 'getProgress']);

    /**
     * @name   My Enrolled Courses
     * @path   GET /api/v1/my-learning
     * @desc   List all courses the student has enrolled in.
     * @controller CourseController@index
     */
    Route::get('/my-learning', [CourseController::class, 'index']);

    /**
     * @name   View Enrolled Course
     * @path   GET /api/v1/my-learning/{course}
     * @desc   View details of a specific enrolled course.
     * @param  {course: slug}
     * @controller CourseController@show
     */
    Route::get('/my-learning/{course}', [CourseController::class, 'show']);

    /**
    |--------------------------------------------------------------------------
    | Units & Lessons (Learning Module)
    |--------------------------------------------------------------------------
     */

    /**
     * @name   List Course Units
     * @path   GET /api/v1/my-learning/{course}/units
     * @desc   List units for an enrolled course.
     * @param  {course: slug}
     * @controller UnitController@index
     */
    Route::get('/my-learning/{course}/units', [UnitController::class, 'index']);

    /**
     * @name   View Unit
     * @path   GET /api/v1/my-learning/{course}/units/{unit}
     * @desc   View a unit within an enrolled course.
     * @param  {course: slug, unit: slug}
     * @controller UnitController@show
     */
    Route::get('/my-learning/{course}/units/{unit}', [UnitController::class, 'show']);

    /**
     * @name   Get Unit Duration
     * @path   GET /api/v1/my-learning/{course}/units/{unit}/duration
     * @desc   Get duration of a unit.
     * @param  {course: slug, unit: slug}
     * @controller UnitController@getDuration
     */
    Route::get('/my-learning/{course}/units/{unit}/duration', [UnitController::class, 'getDuration']);

    /**
     * @name   List Unit Lessons
     * @path   GET /api/v1/my-learning/{course}/units/{unit}/lessons
     * @desc   List lessons in a unit within an enrolled course.
     * @param  {course: slug, unit: slug}
     * @controller LessonController@index
     */
    Route::get('/my-learning/{course}/units/{unit}/lessons', [LessonController::class, 'index']);

    /**
     * @name   View Lesson
     * @path   GET /api/v1/my-learning/{course}/units/{unit}/lessons/{lesson}
     * @desc   View a lesson within an enrolled course unit.
     * @param  {course: slug, unit: slug, lesson: slug}
     * @controller LessonController@show
     */
    Route::get('/my-learning/{course}/units/{unit}/lessons/{lesson}', [LessonController::class, 'show']);

    /**
     * @name   Get Lesson Duration
     * @path   GET /api/v1/my-learning/{course}/units/{unit}/lessons/{lesson}/duration
     * @desc   Get duration of a lesson.
     * @param  {course: slug, unit: slug, lesson: slug}
     * @controller LessonController@getDuration
     */
    Route::get('/my-learning/{course}/units/{unit}/lessons/{lesson}/duration', [LessonController::class, 'getDuration']);

    /**
    |--------------------------------------------------------------------------
    | Units & Lessons (flat, read-only)
    |--------------------------------------------------------------------------
     */
    /**
     * @name   Units by Course
     * @path   GET /api/v1/units/course/{course}
     * @desc   List units for a course (enrolled courses only via scope).
     * @param  {course: slug}
     * @controller UnitController@byCourse
     */
    Route::get('/units/course/{course}', [UnitController::class, 'byCourse']);

    /**
     * @name   View Unit by Id
     * @path   GET /api/v1/units/{unit}
     * @desc   View a unit (must belong to student's enrolled course).
     * @param  {unit: slug}
     * @controller UnitController@show
     */
    Route::get('/units/{unit}', [UnitController::class, 'show']);

    /**
     * @name   Unit Duration
     * @path   GET /api/v1/units/{unit}/duration
     * @desc   Get duration of a unit.
     * @controller UnitController@getDuration
     */
    Route::get('/units/{unit}/duration', [UnitController::class, 'getDuration']);

    /**
     * @name   Lessons by Unit
     * @path   GET /api/v1/lessons/unit/{unit}
     * @desc   List lessons in a unit (unit must belong to enrolled course).
     * @param  {unit: slug}
     * @controller LessonController@byUnit
     */
    Route::get('/lessons/unit/{unit}', [LessonController::class, 'byUnit']);

    /**
     * @name   View Lesson by Id
     * @path   GET /api/v1/lessons/{lesson}
     * @desc   View a lesson (must belong to student's enrolled course).
     * @param  {lesson: slug}
     * @controller LessonController@show
     */
    Route::get('/lessons/{lesson}', [LessonController::class, 'show']);

    /**
     * @name   Lesson Duration
     * @path   GET /api/v1/lessons/{lesson}/duration
     * @desc   Get duration of a lesson.
     * @controller LessonController@getDuration
     */
    Route::get('/lessons/{lesson}/duration', [LessonController::class, 'getDuration']);
});
