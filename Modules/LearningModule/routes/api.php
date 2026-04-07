<?php

use Illuminate\Support\Facades\Route;
use Modules\LearningModule\Http\Controllers\CourseCategoryController;
use Modules\LearningModule\Http\Controllers\CourseController;
use Modules\LearningModule\Http\Controllers\EnrollmentController;
use Modules\LearningModule\Http\Controllers\LessonController;
use Modules\LearningModule\Http\Controllers\UnitController;

Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    Route::apiResource('course-categories', CourseCategoryController::class)->parameters([
        'course-categories' => 'courseCategory',
    ]);
    Route::post('course-categories/{courseCategory}/activate', [CourseCategoryController::class, 'activate']);
    Route::post('course-categories/{courseCategory}/deactivate', [CourseCategoryController::class, 'deactivate']);

    Route::apiResource('courses', CourseController::class);
    Route::post('courses/{course}/publish', [CourseController::class, 'publish']);
    Route::post('courses/{course}/unpublish', [CourseController::class, 'unpublish']);
    Route::post('courses/{course}/change-status', [CourseController::class, 'changeStatus']);
    Route::get('courses/{course}/duration', [CourseController::class, 'getDuration']);
    Route::get('courses/{course}/publishability', [CourseController::class, 'checkPublishability']);
    Route::get('courses-enrollable', [CourseController::class, 'enrollable']);
    Route::get('instructors/{instructorId}/courses', [CourseController::class, 'byInstructor']);
    Route::get('courses/{course}/instructors', [CourseController::class, 'getInstructors']);
    Route::post('courses/{course}/assign-instructor', [CourseController::class, 'assignInstructor']);
    Route::post('courses/{course}/remove-instructor', [CourseController::class, 'removeInstructor']);
    Route::post('courses/{course}/set-primary-instructor', [CourseController::class, 'setPrimaryInstructor']);
    Route::post('courses/{course}/unset-primary-instructor', [CourseController::class, 'unsetPrimaryInstructor']);

    Route::apiResource('units', UnitController::class);
    Route::get('courses/{course}/units', [UnitController::class, 'byCourse']);
    Route::post('courses/{course}/units/reorder', [UnitController::class, 'reorder']);
    Route::post('units/{unit}/move', [UnitController::class, 'moveToPosition']);
    Route::get('units/{unit}/duration', [UnitController::class, 'getDuration']);
    Route::get('units/{unit}/can-delete', [UnitController::class, 'canBeDeleted']);
    Route::get('courses/{course}/units/count', [UnitController::class, 'getUnitCount']);

    Route::apiResource('lessons', LessonController::class);
    Route::get('units/{unit}/lessons', [LessonController::class, 'byUnit']);
    Route::post('units/{unit}/lessons/reorder', [LessonController::class, 'reorder']);
    Route::post('lessons/{lesson}/move', [LessonController::class, 'moveToPosition']);
    Route::get('lessons/{lesson}/duration', [LessonController::class, 'getDuration']);
    Route::get('units/{unit}/lessons/count', [LessonController::class, 'getLessonCount']);

    Route::apiResource('enrollments', EnrollmentController::class);
    Route::patch('enrollments/{enrollment}/status', [EnrollmentController::class, 'updateStatus']);
    Route::get('enrollments/{enrollment}/progress', [EnrollmentController::class, 'getProgress']);
});
