<?php

use Illuminate\Support\Facades\Route;
use Modules\LearningModule\Http\Controllers\CourseController;
use Modules\LearningModule\Http\Controllers\CourseCategoryController;
use Modules\LearningModule\Http\Controllers\EnrollmentController;
use Modules\LearningModule\Http\Controllers\LessonController;
use Modules\LearningModule\Http\Controllers\UnitController;
use Modules\UserMangementModule\Http\Controllers\Api\V1\AuditorController;
use Modules\UserMangementModule\Http\Controllers\Api\V1\InstructorController;
use Modules\UserMangementModule\Http\Controllers\Api\V1\PermissionController;
use Modules\UserMangementModule\Http\Controllers\Api\V1\RoleController;
use Modules\UserMangementModule\Http\Controllers\Api\V1\StudentController;
use Modules\UserMangementModule\Http\Controllers\Api\V1\UserController;
use Modules\ReportingModule\Http\Controllers\AdminDashboardController;
use Spatie\Permission\Models\Permission;

/**
 | -----------------------------------------------------------------------------------------------
 |Super Admin Dashboard Routes
 | -----------------------------------------------------------------------------------------------
 * Handles users management, course management, quiz management
 * super admin have full controll, full access over system resources
 * @auth   Required (JWT)
 * @prefix api/v1/super-admin
 * @access Super Admin Only
 */

Route::group([
    'prefix' => 'super-admin',
    'middleware' => ['auth:api', 'role:super-admin,api']
], function () {
  

    /**
     * @name   Super Admin Dashboard
     * @path   GET /api/v1/super-admin/dashboard
     * @desc   Retrieve statistics and reports on courses, students
     * @controller AdminDashboardController@index
     */
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);

    /**
    |--------------------------------------------------------------------------
    |  Course Categories Management (Learning Module)
    |--------------------------------------------------------------------------
     */

    /**
     * @name   List All Course Categorys
     * @path   GET /api/v1/super-admin/course-categories
     * @desc   Retrieve a paginated list of all course categorys across the platform.
     * @controller CourseCategoryController@index
     */
    Route::get('/course-categories', [CourseCategoryController::class, 'index']);

    /**
     * @name   View Course Category Details
     * @path   GET /api/v1/super-admin/course-categories/{courseCategory}
     * @desc   Retrieve details of a single course category.
     * @param  {courseType: slug}
     * @controller CourseCategoryController@show
     */
    Route::get('/course-categories/{courseCategory}', [CourseCategoryController::class, 'show']);

    /**
     * @name   Create Course Category
     * @path   POST /api/v1/super-admin/course-categories
     * @desc   Validate and store a new course category in the system.
     * @body   {name: string, ...}
     * @controller CourseCategoryController@store
     */
    Route::post('/course-categories', [CourseCategoryController::class, 'store']);

    /**
     * @name   Update Course Category
     * @path   PUT /api/v1/super-admin/course-categories/{courseCategory}
     * @desc   Update an existing course category.
     * @param  {courseType: slug}
     * @controller CourseCategoryController@update
     */
    Route::put('/course-categories/{courseCategory}', [CourseCategoryController::class, 'update']);

    /**
     * @name   Delete Course Category
     * @path   DELETE /api/v1/super-admin/course-categories/{courseCategory}
     * @desc   Soft Deletes a course category from the database.
     * @param  {courseType: slug}
     * @controller CourseCategoryController@destroy
     */
    Route::delete('/course-categories/{courseCategory}', [CourseCategoryController::class, 'destroy']);

    /**
     * @name   Activate Course Category
     * @path   POST /api/v1/super-admin/course-categories/{courseCategory}/activate
     * @desc   Mark a course category as active.
     * @param  {courseType: slug}
     * @controller CourseCategoryController@activate
     */
    Route::post('/course-categories/{courseCategory}/activate', [CourseCategoryController::class, 'activate']);

    /**
     * @name   Deactivate Course Category
     * @path   POST /api/v1/super-admin/course-categories/{courseCategory}/deactivate
     * @desc   Mark a course category as inactive.
     * @param  {courseType: slug}
     * @controller CourseCategoryController@deactivate
     */
    Route::post('/course-categories/{courseCategory}/deactivate', [CourseCategoryController::class, 'deactivate']);

    /**
    |--------------------------------------------------------------------------
    |  Enrollments Management (Learning Module)
    |--------------------------------------------------------------------------
     */

    /**
     * @name   List All Enrollments
     * @path   GET /api/v1/super-admin/enrollments
     * @desc   Retrieve a paginated list of all enrollments across the platform.
     * @controller EnrollmentController@index
     */
    Route::get('/enrollments', [EnrollmentController::class, 'index']);

    /**
     * @name   View Enrollment Details
     * @path   GET /api/v1/super-admin/enrollments/{enrollment}
     * @desc   Retrieve details of a single enrollment.
     * @param  {enrollment: id}
     * @controller EnrollmentController@show
     */
    Route::get('/enrollments/{enrollment}', [EnrollmentController::class, 'show']);

    /**
     * @name   Create Enrollment
     * @path   POST /api/v1/super-admin/enrollments
     * @desc   Create a new enrollment (enroll a user in a course).
     * @body   {user_id: int, course_id: int, ...}
     * @controller EnrollmentController@store
     */
    Route::post('/enrollments', [EnrollmentController::class, 'store']);

    /**
     * @name   Update Enrollment
     * @path   PUT /api/v1/super-admin/enrollments/{enrollment}
     * @desc   Update enrollment attributes.
     * @param  {enrollment: id}
     * @controller EnrollmentController@update
     */
    Route::put('/enrollments/{enrollment}', [EnrollmentController::class, 'update']);

    /**
     * @name   Update Enrollment Status
     * @path   PUT /api/v1/super-admin/enrollments/{enrollment}/status
     * @desc   Update the status of an enrollment.
     * @param  {enrollment: id}
     * @controller EnrollmentController@updateStatus
     */
    Route::put('/enrollments/{enrollment}/status', [EnrollmentController::class, 'updateStatus']);

    /**
     * @name   Get Enrollment Progress
     * @path   GET /api/v1/super-admin/enrollments/{enrollment}/progress
     * @desc   Retrieve progress data for an enrollment.
     * @param  {enrollment: id}
     * @controller EnrollmentController@getProgress
     */
    Route::get('/enrollments/{enrollment}/progress', [EnrollmentController::class, 'getProgress']);

    /**
    |--------------------------------------------------------------------------
    |  Courses Management (Learning Module)
    |--------------------------------------------------------------------------
     */

    /**
     * @name   List All Courses
     * @path   GET /api/v1/super-admin/courses
     * @desc   Retrieve a paginated list of all courses across the platform
     * @controller CourseController@index
     */
    Route::get('/courses', [CourseController::class, 'index']);

    /**
     * @name   Enrollable Courses List
     * @path   GET /api/v1/super-admin/courses/enrollable/list
     * @desc   List courses available for enrollment.
     * @controller CourseController@enrollable
     */
    Route::get('/courses/enrollable/list', [CourseController::class, 'enrollable']);

    /**
     * @name   Courses by Instructor
     * @path   GET /api/v1/super-admin/courses/instructor/{instructorId}
     * @desc   List courses taught by a specific instructor.
     * @param  {instructorId: id}
     * @controller CourseController@byInstructor
     */
    Route::get('/courses/instructor/{instructorId}', [CourseController::class, 'byInstructor']);

    /**
     * @name   View Course Details
     * @path   GET /api/v1/super-admin/courses/{course}
     * @desc   Retrieve detailed information about a course
     * @param   {course: slug}
     * @controller   CourseController@show
     */
    Route::get('/courses/{course}', [CourseController::class, 'show']);

    /**
     * @name   Create New Course
     * @path   POST /api/v1/super-admin/courses
     * @desc   create a new course. requires association with a Program ID.
     * @body   {title: string, course_category_id: int, difficulty_level: string}
     * @controller CourseController@store
     */
    Route::post('/courses', [CourseController::class, 'store']);

    /**
     * @name   Update Course
     * @path   PUT /api/v1/super-admin/courses/{course}
     * @desc   Update an existing attributes of a specific course
     * @param  {course: slug}
     * @controller  CourseController@update
     */
    Route::put('/courses/{course}', [CourseController::class, 'update']);

    /**
     * @name   Delete Course
     * @path   DELETE /api/v1/super-admin/courses/{course}
     * @desc   Soft Deletes a course.
     * @param {course: slug}
     * @controller CourseController@destroy
     */
    Route::delete('/courses/{course}', [CourseController::class, 'destroy']);

    /**
     * @name   Publish Course
     * @path   POST /api/v1/super-admin/courses/{course}/publish
     * @desc   Publish a course.
     * @param  {course: slug}
     * @controller CourseController@publish
     */
    Route::post('/courses/{course}/publish', [CourseController::class, 'publish']);

    /**
     * @name   Unpublish Course
     * @path   POST /api/v1/super-admin/courses/{course}/unpublish
     * @desc   Unpublish a course.
     * @param  {course: slug}
     * @controller CourseController@unpublish
     */
    Route::post('/courses/{course}/unpublish', [CourseController::class, 'unpublish']);

    /**
     * @name   Change Course Status
     * @path   PUT /api/v1/super-admin/courses/{course}/status
     * @desc   Change course status.
     * @param  {course: slug}
     * @controller CourseController@changeStatus
     */
    Route::put('/courses/{course}/status', [CourseController::class, 'changeStatus']);

    /**
     * @name   Check Course Publishability
     * @path   GET /api/v1/super-admin/courses/{course}/publishability
     * @desc   Check if course can be published.
     * @param  {course: slug}
     * @controller CourseController@checkPublishability
     */
    Route::get('/courses/{course}/publishability', [CourseController::class, 'checkPublishability']);

    /**
     * @name   Get Course Duration
     * @path   GET /api/v1/super-admin/courses/{course}/duration
     * @desc   Get total duration of a course.
     * @param  {course: slug}
     * @controller CourseController@getDuration
     */
    Route::get('/courses/{course}/duration', [CourseController::class, 'getDuration']);

    /**
     * @name   Assign Instructor to Course
     * @path   POST /api/v1/super-admin/courses/{course}/instructors/assign
     * @desc   Assign an instructor to a course.
     * @param  {course: slug}
     * @controller CourseController@assignInstructor
     */
    Route::post('/courses/{course}/instructors/assign', [CourseController::class, 'assignInstructor']);

    /**
     * @name   Remove Instructor from Course
     * @path   DELETE /api/v1/super-admin/courses/{course}/instructors/remove
     * @desc   Remove an instructor from a course.
     * @param  {course: slug}
     * @controller CourseController@removeInstructor
     */
        Route::post('courses/{course}/remove-instructor', [CourseController::class, 'removeInstructor']);

    /**
     * @name   Set Primary Instructor
     * @path   PUT /api/v1/super-admin/courses/{course}/instructors/primary
     * @desc   Set primary instructor for a course.
     * @param  {course: slug}
     * @controller CourseController@setPrimaryInstructor
     */
    Route::put('/courses/{course}/instructors/primary', [CourseController::class, 'setPrimaryInstructor']);

    /**
     * @name   Unset Primary Instructor
     * @path   DELETE /api/v1/super-admin/courses/{course}/instructors/primary
     * @desc   Unset primary instructor for a course.
     * @param  {course: slug}
     * @controller CourseController@unsetPrimaryInstructor
     */
    Route::delete('/courses/{course}/instructors/primary', [CourseController::class, 'unsetPrimaryInstructor']);

    /**
     * @name   Get Course Instructors
     * @path   GET /api/v1/super-admin/courses/{course}/instructors
     * @desc   List instructors assigned to a course.
     * @param  {course: slug}
     * @controller CourseController@getInstructors
     */
    Route::get('/courses/{course}/instructors', [CourseController::class, 'getInstructors']);

    /**
    |--------------------------------------------------------------------------
    |  Units Management (Learning Module)
    |--------------------------------------------------------------------------
     */

    /**
     * @name   List All Units
     * @path   GET /api/v1/super-admin/units
     * @desc   Retrieve a paginated list of all units.
     * @controller UnitController@index
     */
    Route::get('/units', [UnitController::class, 'index']);

    /**
     * @name   Units by Course
     * @path   GET /api/v1/super-admin/units/course/{course}
     * @desc   List units belonging to a course.
     * @param  {course: slug}
     * @controller UnitController@byCourse
     */
    Route::get('/units/course/{course}', [UnitController::class, 'byCourse']);

    /**
     * @name   Reorder Units in Course
     * @path   POST /api/v1/super-admin/courses/{course}/units/reorder
     * @desc   Reorder units within a course.
     * @param  {course: slug}
     * @controller UnitController@reorder
     */
    Route::post('courses/{course}/units/reorder', [UnitController::class, 'reorder']);

    /**
     * @name   Get Unit Count for Course
     * @path   GET /api/v1/super-admin/units/course/{course}/count
     * @desc   Get number of units in a course.
     * @param  {course: slug}
     * @controller UnitController@getUnitCount
     */
    Route::get('/units/course/{course}/count', [UnitController::class, 'getUnitCount']);

    /**
     * @name   View Unit Details
     * @path   GET /api/v1/super-admin/units/{unit}
     * @desc   Retrieve details for a single unit.
     * @param  {unit: slug}
     * @controller UnitController@show
     */
    Route::get('/units/{unit}', [UnitController::class, 'show']);

    /**
     * @name   Create Unit
     * @path   POST /api/v1/super-admin/units
     * @desc   Create a new unit.
     * @body   {title: string, course_id: int, ...}
     * @controller UnitController@store
     */
    Route::post('/units', [UnitController::class, 'store']);

    /**
     * @name   Update Unit
     * @path   PUT /api/v1/super-admin/units/{unit}
     * @desc   Update unit attributes.
     * @param  {unit: slug}
     * @controller UnitController@update
     */
    Route::put('/units/{unit}', [UnitController::class, 'update']);

    /**
     * @name   Delete Unit
     * @path   DELETE /api/v1/super-admin/units/{unit}
     * @desc   Soft delete a unit.
     * @param  {unit: slug}
     * @controller UnitController@destroy
     */
    Route::delete('/units/{unit}', [UnitController::class, 'destroy']);

    /**
     * @name   Get Unit Duration
     * @path   GET /api/v1/super-admin/units/{unit}/duration
     * @desc   Get duration of a unit.
     * @param  {unit: slug}
     * @controller UnitController@getDuration
     */
    Route::get('/units/{unit}/duration', [UnitController::class, 'getDuration']);

    /**
     * @name   Check Unit Can Be Deleted
     * @path   GET /api/v1/super-admin/units/{unit}/can-delete
     * @desc   Check if unit can be safely deleted.
     * @param  {unit: slug}
     * @controller UnitController@canBeDeleted
     */
    Route::get('/units/{unit}/can-delete', [UnitController::class, 'canBeDeleted']);

    /**
     * @name   Move Unit to Position
     * @path   PUT /api/v1/super-admin/units/{unit}/position
     * @desc   Change unit order position.
     * @param  {unit: slug}
     * @controller UnitController@moveToPosition
     */
    Route::put('/units/{unit}/position', [UnitController::class, 'moveToPosition']);

    /**
     * @name   List Units by Course (nested)
     * @path   GET /api/v1/super-admin/courses/{course}/units
     * @desc   Fetch all educational units associated with a specific course.
     * @param  {course: slug}
     * @controller UnitController@index
     */
    Route::get('/courses/{course}/units', [UnitController::class, 'index']);

    /**
     * @name   View Unit Details (nested)
     * @path   GET /api/v1/super-admin/courses/{course}/units/{unit}
     * @desc   Retrieve details for a single unit within its course context.
     * @param {course: slug, unit: slug}
     * @controller UnitController@show
     */
    Route::get('/courses/{course}/units/{unit}', [UnitController::class, 'show']);

    /**
     * @name   Create Course Unit (nested)
     * @path   POST /api/v1/super-admin/courses/{course}/units
     * @desc   Add a new unit to a specific course.
     * @body   {title: string,...}
     * @controller UnitController@store
     */
    Route::post('/courses/{course}/units', [UnitController::class, 'store']);

    /**
     * @name   Update Course Unit (nested)
     * @path   PUT /api/v1/super-admin/courses/{course}/units/{unit}
     * @desc   Modify the title, content, or sequence of a specific unit.
     * @controller UnitController@update
     */
    Route::put('/courses/{course}/units/{unit}', [UnitController::class, 'update']);

    /**
     * @name   Delete Course Unit (nested)
     * @path   DELETE /api/v1/super-admin/courses/{course}/units/{unit}
     * @desc   Soft Deletes a course unit.
     * @controller UnitController@destroy
     */
    Route::delete('/courses/{course}/units/{unit}', [UnitController::class, 'destroy']);

    /**
    |--------------------------------------------------------------------------
    |  Lessons Management (Learning Module)
    |--------------------------------------------------------------------------
     */

    /**
     * @name   List All Lessons
     * @path   GET /api/v1/super-admin/lessons
     * @desc   Retrieve a paginated list of all lessons.
     * @controller LessonController@index
     */
    Route::get('/lessons', [LessonController::class, 'index']);

    /**
     * @name   Lessons by Unit
     * @path   GET /api/v1/super-admin/lessons/unit/{unit}
     * @desc   List lessons belonging to a unit.
     * @param  {unit: slug}
     * @controller LessonController@byUnit
     */
    Route::get('/lessons/unit/{unit}', [LessonController::class, 'byUnit']);

    /**
     * @name   Reorder Lessons in Unit
     * @path   POST /api/v1/super-admin/lessons/unit/{unit}/reorder
     * @desc   Reorder lessons within a unit.
     * @param  {unit: slug}
     * @controller LessonController@reorder
     */
    Route::post('/lessons/unit/{unit}/reorder', [LessonController::class, 'reorder']);

    /**
     * @name   Get Lesson Count for Unit
     * @path   GET /api/v1/super-admin/lessons/unit/{unit}/count
     * @desc   Get number of lessons in a unit.
     * @param  {unit: slug}
     * @controller LessonController@getLessonCount
     */
    Route::get('/lessons/unit/{unit}/count', [LessonController::class, 'getLessonCount']);

    /**
     * @name   View Lesson Details
     * @path   GET /api/v1/super-admin/lessons/{lesson}
     * @desc   Retrieve details for a single lesson.
     * @param  {lesson: slug}
     * @controller LessonController@show
     */
    Route::get('/lessons/{lesson}', [LessonController::class, 'show']);

    /**
     * @name   Create Lesson
     * @path   POST /api/v1/super-admin/lessons
     * @desc   Create a new lesson.
     * @body   {title: string, unit_id: int, ...}
     * @controller LessonController@store
     */
    Route::post('/lessons', [LessonController::class, 'store']);

    /**
     * @name   Update Lesson
     * @path   PUT /api/v1/super-admin/lessons/{lesson}
     * @desc   Update lesson attributes.
     * @param  {lesson: slug}
     * @controller LessonController@update
     */
    Route::put('/lessons/{lesson}', [LessonController::class, 'update']);

    /**
     * @name   Delete Lesson
     * @path   DELETE /api/v1/super-admin/lessons/{lesson}
     * @desc   Soft delete a lesson.
     * @param  {lesson: slug}
     * @controller LessonController@destroy
     */
    Route::delete('/lessons/{lesson}', [LessonController::class, 'destroy']);

    /**
     * @name   Get Lesson Duration
     * @path   GET /api/v1/super-admin/lessons/{lesson}/duration
     * @desc   Get duration of a lesson.
     * @param  {lesson: slug}
     * @controller LessonController@getDuration
     */
    Route::get('/lessons/{lesson}/duration', [LessonController::class, 'getDuration']);

    /**
     * @name   Move Lesson to Position
     * @path   PUT /api/v1/super-admin/lessons/{lesson}/position
     * @desc   Change lesson order position.
     * @param  {lesson: slug}
     * @controller LessonController@moveToPosition
     */
    Route::put('/lessons/{lesson}/position', [LessonController::class, 'moveToPosition']);

    /**
     * @name   List Lessons by Unit (nested)
     * @path   GET /api/v1/super-admin/courses/{course}/units/{unit}/lessons
     * @desc   Retrieve all lessons belonging to a specific course unit.
     * @controller LessonController@index
     */
    Route::get('/courses/{course}/units/{unit}/lessons', [LessonController::class, 'index']);

    /**
     * @name   View Lesson Details (nested)
     * @path   GET /api/v1/super-admin/courses/{course}/units/{unit}/lessons/{lesson}
     * @desc   Fetch full content for a specific lesson.
     * @controller LessonController@show
     */
    Route::get('/courses/{course}/units/{unit}/lessons/{lesson}', [LessonController::class, 'show']);

    /**
     * @name   Create Lesson for a course unit (nested)
     * @path   POST /api/v1/super-admin/courses/{course}/units/{unit}/lessons
     * @desc   Create new lesson belongs to a course unit.
     * @body   {title: string,....}
     * @controller LessonController@store
     */
    Route::post('/courses/{course}/units/{unit}/lessons', [LessonController::class, 'store']);

    /**
     * @name   Update Lesson (nested)
     * @path   PUT /api/v1/super-admin/courses/{course}/units/{unit}/lessons/{lesson}
     * @desc   Edit lesson content
     * @controller LessonController@update
     */
    Route::put('/courses/{course}/units/{unit}/lessons/{lesson}', [LessonController::class, 'update']);

    /**
     * @name   Delete Lesson (nested)
     * @path   DELETE /api/v1/super-admin/courses/{course}/units/{unit}/lessons/{lesson}
     * @desc   Soft Deletes a lesson from the unit.
     * @controller LessonController@destroy
     */
    Route::delete('/courses/{course}/units/{unit}/lessons/{lesson}', [LessonController::class, 'destroy']);

    /**
    |--------------------------------------------------------------------------
    | User Management
    |--------------------------------------------------------------------------
     */

    /**
     * @name   List All Users
     * @path   GET /api/v1/super-admin/users
     * @desc   Retrieve a paginated list of all users (Admins, Students, Instructors, Auditors).
     * @controller UserController@index
     */
    Route::get('/users', [UserController::class, 'index']);

    /**
     * @name   View User Details
     * @path   GET /api/v1/super-admin/users/{user}
     * @desc   Retrieve a specific user's account details and his given role on the platform with role permissions.
     * @param  {user: id}
     * @controller  UserController@show
     */
    Route::get('/users/{user}', [UserController::class, 'show']);

    /**
     * @name   Create User Identity
     * @path   POST /api/v1/super-admin/users
     * @desc   Manually register a new user into the platform.
     * @body   {name: string, email: string, password: string,.....}
     * @controller UserController@store
     */
    Route::post('/users', [UserController::class, 'store']);

    /**
     * @name   Update User Information
     * @path   PUT /api/v1/super-admin/users/{user}
     * @desc   Update account details such as name, email,.....
     * @param  {user: id}
     * @controller UserController@update
     */
    Route::put('/users/{user}', [UserController::class, 'update']);

    /**
     * @name   Delete User Account
     * @path   DELETE /api/v1/super-admin/users/{user}
     * @desc   Soft Deletes a user from the platform.
     * @controller UserController@destroy
    */
     Route::delete('/users/{user}', [UserController::class, 'destroy']);

    /** 
    |--------------------------------------------------------------------------
    | Profiles Management
    |--------------------------------------------------------------------------
    */

   /**
     * @name   List Organization Instructors
     * @path   GET /api/v1/super-admin/instructors
     * @desc   List all users assigned as instructors in this organization.
     * @controller InstructorController@index
     * 
    */
   Route::get('/instructors',[InstructorController::class,'index']);
   /**
     * @name   Assign Instructor
     * @path   POST /api/v1/super-admin/instructors
     * @desc   Invite a user to teach within this organization.
     * @controller InstructorController@store
    */
   Route::post('/instructors',[InstructorController::class,'store']);

   /**
     * @name   View Instructor Details
     * @path   GET /api/v1/super-admin/instructors/{instructor}
     * @desc   Retrieve Instructor's account informations and profile details
     * @param  {instructor: id}
     * @controller  InstructorController@show
    */
   Route::get('/instructors/{instructor}',[InstructorController::class,'show']);
   //6.2. students authorized routes
   /**
     * @name   List Enrolled Students
     * @path   GET /api/v1/super-admin/students
     * @desc   Retrieve a list of all students enrolled in this platform courses.
     * @controller StudentController@index
    */

   /**
     * @name   Update Instructor Information
     * @path   PUT /api/v1/super-admin/instructors/{instructor}
     * @desc   Update Instructor's account informations and profile details
     * @param  {instructor: id}
     * @controller  InstructorController@update
    */
   Route::put('/instructors/{instructor}',[InstructorController::class,'update']);

    /**
      * @name   Delete Instructor Account
      * @path   DELETE /api/v1/super-admin/instructors/{instructor}
      * @desc   Soft Deletes an instructor account from the organization.
      * @param  {instructor: id}
      * @controller  InstructorController@destroy
     */
    Route::delete('/instructors/{instructor}',[InstructorController::class,'destroy']);

    /**
      * @name   View Student Details
      * @path   GET /api/v1/super-admin/students/{student}
      * @desc   Retrieve Student's account informations and profile details
      * @param  {student: id}
      * @controller  StudentController@show
     */
   Route::get('/students',[StudentController::class,'index']);

   /**
     * @name   View Student Details
     * @path   GET /api/v1/super-admin/students/{student}
     * @desc   Retrieve Student's account informations and profile details
     * @param  {student: id}
     * @controller  StudentController@show
    */
   Route::get('/students/{student}',[StudentController::class,'show']);

   /**
     * @name   Register Student in the organization 
     * @path   POST /api/v1/super-admin/students
     * @desc   Manually register a new student into the organization
     * @controller StudentController@store
    */
    Route::post('/students', [StudentController::class, 'store']);

    /**
     * @name   Update Student Information
     * @path   PUT /api/v1/super-admin/students/{student}
     * @desc   Update Student's account informations and profile details            
     * @param  {student: id}
     * @controller  StudentController@update
     */
    Route::put('/students/{student}', [StudentController::class, 'update']);

    /**
     * @name   Delete Student Account
     * @path   DELETE /api/v1/super-admin/students/{student}
     * @desc   Soft Deletes a student account from the organization.
     * @param  {student: id}
     * @controller  StudentController@destroy
    */
    Route::delete('/students/{student}', [StudentController::class, 'destroy']);


   /**
     * @name   View Auditor List
     * @path   GET /api/v1/super-admin/auditors
     * @desc   List all auditors assigned to this organization.
     * @controller AuditorController@index
    */
   Route::get('/auditors',[AuditorController::class,'index']);

   /**
     * @name   Assign Auditor
     * @path   POST /api/v1/super-admin/auditors
     * @desc   Invite an auditor to this organization.
     * @controller AuditorController@store
    */
   Route::post('/auditors',[AuditorController::class,'store']);

   /**
     * @name   View Auditor Details
     * @path   GET /api/v1/super-admin/auditors/{auditor}
     * @desc   Retrieve Auditor's account informations and profile details
     * @param  {auditor: id}
     * @controller  AuditorController@show
    */
   Route::get('/auditors/{auditor}',[AuditorController::class,'show']);  


   /**
     * @name   Update Auditor Information
     * @path   PUT /api/v1/super-admin/auditors/{auditor}
     * @desc   Update Auditor's account informations and profile details            
     * @param  {auditor: id}
     * @controller  AuditorController@update
    */
   Route::put('/auditors/{auditor}',[AuditorController::class,'update']);

   /**
     * @name   Delete Auditor Account
     * @path   DELETE /api/v1/super-admin/auditors/{auditor}
     * @desc   Soft Deletes an auditor account from the organization.
     * @param  {auditor: id}
     * @controller  AuditorController@destroy
    */
    Route::delete('/auditors/{auditor}',[AuditorController::class,'destroy']);
   

    Route::apiResource('roles', RoleController::class);
    Route::get('permissions', [PermissionController::class, 'index']);
    
});
