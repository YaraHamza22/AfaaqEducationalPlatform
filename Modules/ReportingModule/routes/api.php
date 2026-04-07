<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Reporting Module API Routes
|--------------------------------------------------------------------------
|
| Versioned routes for dashboards and reports.
|
*/

Route::group([
    'middleware' => 'api',
    'prefix' => 'v1',
], function () {
    require __DIR__ . '/V1/dashboards.php';
    require __DIR__ . '/V1/reports.php';
});
