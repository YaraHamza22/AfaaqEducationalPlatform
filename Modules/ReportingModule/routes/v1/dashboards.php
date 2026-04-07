<?php

use Illuminate\Support\Facades\Route;
use Modules\ReportingModule\Http\Controllers\AdminDashboardController;
use Modules\ReportingModule\Http\Controllers\InstructorDashboardController;
use Modules\ReportingModule\Http\Controllers\StudentDashboardController;

/*
|--------------------------------------------------------------------------
| Dashboard Routes (v1)
|--------------------------------------------------------------------------
*/

Route::group([
    'middleware' => ['auth:api'],
], function () {
    Route::group([
        'prefix' => 'admin',
        'middleware' => ['role:admin'],
    ], function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])
            ->name('dashboards.admin.dashboard');
    });

    Route::group([
        'prefix' => 'super-admin',
        'middleware' => ['role:super-admin'],
    ], function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])
            ->name('dashboards.super-admin.dashboard');
    });

    Route::group([
        'prefix' => 'student',
        'middleware' => ['role:student'],
    ], function () {
        Route::get('/dashboard/{studentId}', [StudentDashboardController::class, 'dashboard'])
            ->whereNumber('studentId')
            ->name('dashboards.student.dashboard');
    });

    Route::group([
        'prefix' => 'instructor',
        'middleware' => ['role:instructor'],
    ], function () {
        Route::get('/dashboard/{instructorId}', [InstructorDashboardController::class, 'dashboard'])
            ->whereNumber('instructorId')
            ->name('dashboards.instructor.dashboard');
    });
});
