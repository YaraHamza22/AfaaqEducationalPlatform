<?php

namespace Modules\ReportingModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\ReportingModule\Http\Requests\ActivityLog\ActivityLogIndexRequest;
use Modules\ReportingModule\Http\Resources\ActivityLogResource;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(ActivityLogIndexRequest $request): JsonResponse
    {
        $filters = $request->validated();

        $query = Activity::query()
            ->with('causer')
            ->orderByDesc('id');

        if (! empty($filters['log_name'])) {
            $query->where('log_name', $filters['log_name']);
        }

        if (! empty($filters['event'])) {
            $query->where('event', $filters['event']);
        }

        if (! empty($filters['subject_type'])) {
            $query->where('subject_type', 'like', '%'.$filters['subject_type'].'%');
        }

        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (isset($filters['causer_id'])) {
            $query->where('causer_id', $filters['causer_id']);
        }

        if (! empty($filters['from'])) {
            $query->where('created_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->where('created_at', '<=', $filters['to'].' 23:59:59');
        }

        if (! empty($filters['description'])) {
            $query->where('description', 'like', '%'.$filters['description'].'%');
        }

        $perPage = (int) ($filters['per_page'] ?? 20);

        $paginator = $query->paginate($perPage);
        $paginator->setCollection(
            $paginator->getCollection()->map(
                fn (Activity $row) => (new ActivityLogResource($row))->resolve()
            )
        );

        return self::paginated($paginator, 'Activity log retrieved successfully.');
    }

    public function show(int $activity_log): JsonResponse
    {
        $activity = Activity::query()
            ->with('causer')
            ->findOrFail($activity_log);

        return self::success(
            new ActivityLogResource($activity),
            'Activity log entry retrieved successfully.'
        );
    }
}
