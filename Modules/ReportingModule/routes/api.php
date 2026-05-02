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
    require __DIR__ . '/v1/dashboards.php';
    require __DIR__ . '/v1/reports.php';
    require __DIR__ . '/v1/snapshots.php';
    require __DIR__ . '/v1/activity-log.php';
});
