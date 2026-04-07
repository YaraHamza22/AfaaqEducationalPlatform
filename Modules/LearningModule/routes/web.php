<?php

use Illuminate\Support\Facades\Route;
use Modules\LearningModule\Http\Controllers\LearningModuleController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('learningmodules', LearningModuleController::class)->names('learningmodule');
});
