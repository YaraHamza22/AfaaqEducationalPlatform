<?php

use Illuminate\Support\Facades\Route;
use Modules\AssessmentModule\Http\Controllers\AssessmentModuleController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('assessmentmodules', AssessmentModuleController::class)->names('assessmentmodule');
});
