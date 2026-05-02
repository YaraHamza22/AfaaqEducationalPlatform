<?php

namespace Modules\ReportingModule\Services;

use Illuminate\Support\Facades\DB;

class SnapshotReportingService
{
    public function assessmentProgress(array $filters = [])
    {
        return DB::table('assessment_progress_snapshots')
            ->when($filters['course_id'] ?? null, fn ($q, $v) => $q->where('course_id', $v))
            ->when($filters['student_id'] ?? null, fn ($q, $v) => $q->where('student_id', $v))
            ->orderByDesc('snapshot_date')
            ->paginate((int) ($filters['per_page'] ?? 20));
    }

    public function certificateFunnel(array $filters = [])
    {
        return DB::table('certificate_funnel_snapshots')
            ->when($filters['course_id'] ?? null, fn ($q, $v) => $q->where('course_id', $v))
            ->orderByDesc('snapshot_date')
            ->paginate((int) ($filters['per_page'] ?? 20));
    }

    public function engagementActivity(array $filters = [])
    {
        return DB::table('engagement_activity_snapshots')
            ->when($filters['course_id'] ?? null, fn ($q, $v) => $q->where('course_id', $v))
            ->when($filters['user_id'] ?? null, fn ($q, $v) => $q->where('user_id', $v))
            ->orderByDesc('snapshot_date')
            ->paginate((int) ($filters['per_page'] ?? 20));
    }
}
