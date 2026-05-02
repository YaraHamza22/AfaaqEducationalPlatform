<?php

namespace Modules\ReportingModule\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class SnapshotMaterializationService
{
    public function materialize(?string $snapshotDate = null): array
    {
        $snapshotAt = $snapshotDate
            ? CarbonImmutable::parse($snapshotDate)->startOfDay()
            : now()->toImmutable()->startOfDay();
        $snapshotUntil = $snapshotAt->endOfDay();

        return DB::transaction(function () use ($snapshotAt, $snapshotUntil) {
            $deletedAssessment = DB::table('assessment_progress_snapshots')
                ->where('snapshot_date', $snapshotAt)
                ->delete();
            $deletedCertificate = DB::table('certificate_funnel_snapshots')
                ->where('snapshot_date', $snapshotAt)
                ->delete();
            $deletedEngagement = DB::table('engagement_activity_snapshots')
                ->where('snapshot_date', $snapshotAt)
                ->delete();

            $assessmentRows = $this->buildAssessmentProgressRows($snapshotAt);
            $certificateRows = $this->buildCertificateFunnelRows($snapshotAt);
            $engagementRows = $this->buildEngagementActivityRows($snapshotAt, $snapshotUntil);

            if ($assessmentRows !== []) {
                DB::table('assessment_progress_snapshots')->insert($assessmentRows);
            }

            if ($certificateRows !== []) {
                DB::table('certificate_funnel_snapshots')->insert($certificateRows);
            }

            if ($engagementRows !== []) {
                DB::table('engagement_activity_snapshots')->insert($engagementRows);
            }

            return [
                'snapshot_date' => $snapshotAt->toDateString(),
                'assessment_progress_rows' => count($assessmentRows),
                'certificate_funnel_rows' => count($certificateRows),
                'engagement_activity_rows' => count($engagementRows),
                'deleted_previous_rows' => [
                    'assessment_progress' => $deletedAssessment,
                    'certificate_funnel' => $deletedCertificate,
                    'engagement_activity' => $deletedEngagement,
                ],
            ];
        });
    }

    private function buildAssessmentProgressRows(CarbonImmutable $snapshotAt): array
    {
        $now = now();
        $rows = DB::table('attempts as a')
            ->join('quizzes as q', 'q.id', '=', 'a.quiz_id')
            ->leftJoin('units as unit', function ($join) {
                $join->on('unit.unit_id', '=', 'q.quizable_id')
                    ->where('q.quizable_type', '=', 'unit');
            })
            ->leftJoin('lessons as lesson', function ($join) {
                $join->on('lesson.lesson_id', '=', 'q.quizable_id')
                    ->where('q.quizable_type', '=', 'lesson');
            })
            ->leftJoin('units as lesson_unit', 'lesson_unit.unit_id', '=', 'lesson.unit_id')
            ->where(function ($query) {
                $query->where('q.quizable_type', 'course')
                    ->orWhere('q.quizable_type', 'unit')
                    ->orWhere('q.quizable_type', 'lesson');
            })
            ->selectRaw("
                CASE
                    WHEN q.quizable_type = 'course' THEN q.quizable_id
                    WHEN q.quizable_type = 'unit' THEN unit.course_id
                    WHEN q.quizable_type = 'lesson' THEN lesson_unit.course_id
                    ELSE NULL
                END AS course_id
            ")
            ->addSelect('a.student_id')
            ->selectRaw('ROUND((SUM(CASE WHEN a.status = ? THEN COALESCE(a.score, 0) ELSE 0 END) / NULLIF(SUM(q.max_score), 0)) * 100, 2) as weighted_percentage', ['graded'])
            ->selectRaw('COUNT(a.id) as attempts_used')
            ->selectRaw('SUM(GREATEST(0, 3 - a.attempt_number)) as attempts_left')
            ->groupBy('course_id', 'a.student_id')
            ->havingRaw('course_id IS NOT NULL')
            ->get();

        return $rows->map(fn ($row) => [
            'course_id' => (int) $row->course_id,
            'student_id' => (int) $row->student_id,
            'weighted_percentage' => (float) ($row->weighted_percentage ?? 0),
            'attempts_used' => (int) ($row->attempts_used ?? 0),
            'attempts_left' => max(0, (int) ($row->attempts_left ?? 0)),
            'snapshot_date' => $snapshotAt,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();
    }

    private function buildCertificateFunnelRows(CarbonImmutable $snapshotAt): array
    {
        $now = now();

        $eligibleRows = DB::table('assessment_progress_snapshots')
            ->select('course_id')
            ->selectRaw('COUNT(DISTINCT student_id) as eligible_students')
            ->where('snapshot_date', $snapshotAt)
            ->where('weighted_percentage', '>=', 60)
            ->groupBy('course_id')
            ->get()
            ->keyBy('course_id');

        $issuedRows = DB::table('course_certificates')
            ->select('course_id')
            ->selectRaw('COUNT(DISTINCT student_id) as issued_students')
            ->whereNotNull('issued_at')
            ->groupBy('course_id')
            ->get()
            ->keyBy('course_id');

        $courseIds = $eligibleRows->keys()->merge($issuedRows->keys())->unique()->values();

        return $courseIds->map(function ($courseId) use ($eligibleRows, $issuedRows, $snapshotAt, $now) {
            return [
                'course_id' => (int) $courseId,
                'eligible_students' => (int) ($eligibleRows->get($courseId)->eligible_students ?? 0),
                'issued_students' => (int) ($issuedRows->get($courseId)->issued_students ?? 0),
                'snapshot_date' => $snapshotAt,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->all();
    }

    private function buildEngagementActivityRows(CarbonImmutable $snapshotAt, CarbonImmutable $snapshotUntil): array
    {
        $now = now();
        $rows = DB::table('users as u')
            ->leftJoin('chat_messages as cm', function ($join) use ($snapshotUntil) {
                $join->on('cm.sender_id', '=', 'u.id')
                    ->where('cm.created_at', '<=', $snapshotUntil);
            })
            ->leftJoin('chat_threads as ct', 'ct.id', '=', 'cm.chat_thread_id')
            ->leftJoin('forum_posts as fp', function ($join) use ($snapshotUntil) {
                $join->on('fp.author_id', '=', 'u.id')
                    ->whereNull('fp.deleted_at')
                    ->where('fp.created_at', '<=', $snapshotUntil);
            })
            ->leftJoin('forum_threads as ft', 'ft.id', '=', 'fp.forum_thread_id')
            ->leftJoin('session_attendances as sa', function ($join) use ($snapshotUntil) {
                $join->on('sa.user_id', '=', 'u.id')
                    ->where('sa.created_at', '<=', $snapshotUntil);
            })
            ->leftJoin('virtual_sessions as vs', 'vs.id', '=', 'sa.virtual_session_id')
            ->selectRaw('COALESCE(ft.course_id, ct.course_id, vs.course_id) as course_id')
            ->addSelect('u.id as user_id')
            ->selectRaw('COUNT(DISTINCT cm.id) as messages_count')
            ->selectRaw('COUNT(DISTINCT fp.id) as forum_posts_count')
            ->selectRaw('COUNT(DISTINCT sa.id) as virtual_sessions_attended')
            ->groupBy('course_id', 'u.id')
            ->havingRaw('course_id IS NOT NULL')
            ->havingRaw('(COUNT(DISTINCT cm.id) + COUNT(DISTINCT fp.id) + COUNT(DISTINCT sa.id)) > 0')
            ->get();

        return $rows->map(fn ($row) => [
            'course_id' => (int) $row->course_id,
            'user_id' => (int) $row->user_id,
            'messages_count' => (int) ($row->messages_count ?? 0),
            'forum_posts_count' => (int) ($row->forum_posts_count ?? 0),
            'virtual_sessions_attended' => (int) ($row->virtual_sessions_attended ?? 0),
            'snapshot_date' => $snapshotAt,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();
    }
}
