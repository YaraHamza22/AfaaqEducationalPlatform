<?php

use Illuminate\Support\Facades\Route;
use Modules\LearningModule\Http\Controllers\CourseController;
use Modules\LearningModule\Http\Controllers\EnrollmentController;
use Modules\UserMangementModule\Http\Controllers\Api\V1\AuthController;
use Modules\UserMangementModule\Http\Controllers\Api\V1\StudentController;

// Backward-compatible profile endpoint for clients using /api/profile.
Route::get('/profile', [AuthController::class, 'profile'])->middleware('auth:api');

Route::group([
    'middleware' => 'api',
    'prefix' => 'v1/auth',
], function ($router) {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::get('/profile', [AuthController::class, 'profile'])->middleware('auth:api');
});

// Super-admin auth aliases for clients using /api/v1/super-admin/auth/*.
Route::group([
    'middleware' => 'api',
    'prefix' => 'v1/super-admin/auth',
], function ($router) {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::get('/profile', [AuthController::class, 'profile'])->middleware('auth:api');
});

// Outer prefix must be `v1` only: `V1/superAdmin.php` already uses `super-admin`.
Route::group(['prefix' => 'v1'], function () {

    require __DIR__ . '/V1/instructor.php';
    require __DIR__ . '/V1/student.php';
    require __DIR__ . '/V1/superAdmin.php';

    // authenticated user routes
    Route::group(['middleware' => ['auth:api']], function () {
        // course discovery
        Route::get('/courses', [CourseController::class, 'index']);
        Route::get('/courses/{course}', [CourseController::class, 'show']);

        //course enrollment
        Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'enroll']);
        Route::post('/complete-profile', [StudentController::class, 'fillProfileInfo']);
    });
});
