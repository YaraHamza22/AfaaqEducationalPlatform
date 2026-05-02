<?php

use Illuminate\Support\Facades\Route;
use Modules\AssesmentModule\Http\Controllers\AssesmentModuleController;
use Modules\AssesmentModule\Http\Controllers\Api\V1\AssessmentProgressController;
use Modules\AssesmentModule\Http\Controllers\Api\V1\AnswerController;
use Modules\AssesmentModule\Http\Controllers\Api\V1\AttemptController;
use Modules\AssesmentModule\Http\Controllers\Api\V1\CertificateController;
use Modules\AssesmentModule\Http\Controllers\Api\V1\QuizController;
use Modules\AssesmentModule\Http\Controllers\Api\V1\QuestionController;
use Modules\AssesmentModule\Http\Controllers\Api\V1\QuestionOptionController;

Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    Route::apiResource('assesmentmodules', AssesmentModuleController::class)->names('assesmentmodule');

    /*Quizzes*/
    Route::apiResource('quizzes', QuizController::class);
    Route::post('quizzes/{quiz}/publish',   [QuizController::class, 'publish']);
    Route::post('quizzes/{quiz}/unpublish', [QuizController::class, 'unpublish']);
    Route::post('quizzes/{quiz}/archive', [QuizController::class,'archive']);

    Route::apiResource('questions', QuestionController::class);

    /*
     Question Options
    */
    Route::apiResource('question-options', QuestionOptionController::class);

    /*
     Attempts
    */
    Route::apiResource('attempts', AttemptController::class);
    Route::post('attempts/{attempt}/start', [AttemptController::class, 'start']);
    Route::post('attempts/{attempt}/submit', [AttemptController::class, 'submit']);
    Route::post('attempts/{attempt}/grade', [AttemptController::class, 'grade']);
    Route::get('courses/{courseId}/assessment-progress', [AssessmentProgressController::class, 'courseProgress']);
    Route::get('courses/{courseId}/certificate', [CertificateController::class, 'download']);

    /*
     Answers
    */
    Route::apiResource('answers', AnswerController::class);

});