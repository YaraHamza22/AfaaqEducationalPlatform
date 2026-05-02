<?php

use Illuminate\Support\Facades\Route;
use Modules\ReportingModule\Http\Controllers\AdminDashboardController;

/*
|--------------------------------------------------------------------------
| Report Routes (v1)
|--------------------------------------------------------------------------
|
| Platform-wide reports are available for super-admin only.
| Organizations, managers, donors, and programs were removed.
|
*/

Route::group([
    'middleware' => ['auth:api'],
], function () {
    Route::group([
        'prefix' => 'super-admin/reports',
        'middleware' => ['role:super-admin'],
    ], function () {
        Route::prefix('students')->group(function () {
            Route::get('/performance', [AdminDashboardController::class, 'generateStudentPerformanceReport'])
                ->name('reports.super-admin.students.performance');

            Route::get('/completion-rates', [AdminDashboardController::class, 'getCompletionRates'])
                ->name('reports.super-admin.students.completion-rates');

            Route::get('/learning-time', [AdminDashboardController::class, 'getLearningTimeAnalysis'])
                ->name('reports.super-admin.students.learning-time');
        });

        Route::prefix('courses')->group(function () {
            Route::get('/popularity', [AdminDashboardController::class, 'generateCoursePopularityReport'])
                ->name('reports.super-admin.courses.popularity');

            Route::get('/content-performance/{courseId}', [AdminDashboardController::class, 'getContentPerformance'])
                ->whereNumber('courseId')
                ->name('reports.super-admin.courses.content-performance');

            Route::get('/learning-gaps', [AdminDashboardController::class, 'identifyLearningGaps'])
                ->name('reports.super-admin.courses.learning-gaps');
        });
    });
});
