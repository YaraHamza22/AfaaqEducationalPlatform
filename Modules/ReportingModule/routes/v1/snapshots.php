<?php

use Illuminate\Support\Facades\Route;
use Modules\ReportingModule\Http\Controllers\SnapshotReportingController;

Route::middleware(['auth:api', 'role:super-admin,api'])
    ->prefix('super-admin/snapshots')
    ->group(function () {
        Route::post('materialize', [SnapshotReportingController::class, 'materialize']);
        Route::get('assessment-progress', [SnapshotReportingController::class, 'assessmentProgress']);
        Route::get('certificate-funnel', [SnapshotReportingController::class, 'certificateFunnel']);
        Route::get('engagement-activity', [SnapshotReportingController::class, 'engagementActivity']);
    });
