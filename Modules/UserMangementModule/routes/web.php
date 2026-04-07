<?php

use Illuminate\Support\Facades\Route;
use Modules\UserMangementModule\Http\Controllers\UserMangementModuleController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('usermangementmodules', UserMangementModuleController::class)->names('usermanagementmodule');
});
