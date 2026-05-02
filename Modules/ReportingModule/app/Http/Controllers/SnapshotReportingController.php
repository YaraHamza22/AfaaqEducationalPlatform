<?php

namespace Modules\ReportingModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ReportingModule\Http\Requests\Snapshot\MaterializeSnapshotsRequest;
use Modules\ReportingModule\Services\SnapshotMaterializationService;
use Modules\ReportingModule\Services\SnapshotReportingService;
use Throwable;

class SnapshotReportingController extends Controller
{
    public function __construct(
        private SnapshotReportingService $service,
        private SnapshotMaterializationService $materializationService
    ) {}

    public function assessmentProgress(Request $request)
    {
        $rows = $this->service->assessmentProgress($request->only(['course_id', 'student_id', 'per_page']));
        return self::paginated($rows, 'Assessment progress snapshots fetched successfully.');
    }

    public function certificateFunnel(Request $request)
    {
        $rows = $this->service->certificateFunnel($request->only(['course_id', 'per_page']));
        return self::paginated($rows, 'Certificate funnel snapshots fetched successfully.');
    }

    public function engagementActivity(Request $request)
    {
        $rows = $this->service->engagementActivity($request->only(['course_id', 'user_id', 'per_page']));
        return self::paginated($rows, 'Engagement activity snapshots fetched successfully.');
    }

    public function materialize(MaterializeSnapshotsRequest $request)
    {
        try {
            $result = $this->materializationService->materialize($request->validated('snapshot_date'));

            return self::success($result, 'Snapshots materialized successfully.');
        } catch (Throwable $e) {
            return self::error('Failed to materialize snapshots.', 422, $e->getMessage());
        }
    }
}
