<?php

use Illuminate\Support\Facades\Route;
use Modules\CommunicationModule\Http\Controllers\CommunicationModuleController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('communicationmodules', CommunicationModuleController::class)->names('communicationmodule');
});
