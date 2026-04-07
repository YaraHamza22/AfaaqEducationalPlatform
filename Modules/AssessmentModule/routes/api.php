<?php
use Illuminate\Support\Facades\Route;
use Modules\AssesmentModule\Http\Requests;
use Modules\AssessmentModule\Http\Controllers\AssessmentModuleController;
use Modules\AssessmentModule\Http\Controllers\Api\V1\QuizController;

Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    Route::apiResource('assessmentmodules', AssessmentModuleController::class)->names('assessmentmodule');
});
Route::prefix('v1')->group(function () {

    /*Quizzes*/
    Route::apiResource('quizzes', QuizController::class);
    Route::post('quizzes/{quiz}/publish',   [QuizController::class, 'publish']);
    Route::post('quizzes/{quiz}/unpublish', [QuizController::class, 'unpublish']);
});