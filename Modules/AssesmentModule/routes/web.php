<?php

use Illuminate\Support\Facades\Route;
use Modules\AssesmentModule\Http\Controllers\AssesmentModuleController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('assesmentmodules', AssesmentModuleController::class)->names('assesmentmodule');
});
