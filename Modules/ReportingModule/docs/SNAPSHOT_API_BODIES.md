# Reporting Snapshot API Contracts

## Endpoints
- `POST /api/v1/super-admin/snapshots/materialize`
- `GET /api/v1/super-admin/snapshots/assessment-progress?course_id=12&student_id=9&per_page=20`
- `GET /api/v1/super-admin/snapshots/certificate-funnel?course_id=12&per_page=20`
- `GET /api/v1/super-admin/snapshots/engagement-activity?course_id=12&user_id=9&per_page=20`

## Materialize request body
```json
{
  "snapshot_date": "2026-04-28"
}
```

- `snapshot_date` is optional and must be `Y-m-d`.
- If omitted, the API materializes snapshots for the current date.
- Access is restricted to role: `super-admin`.

## Example response (paginated envelope)
```json
{
  "status": "success",
  "message": "Operation successful",
  "data": [
    {
      "id": 1,
      "course_id": 12,
      "student_id": 9,
      "weighted_percentage": "67.50",
      "attempts_used": 4,
      "attempts_left": 2,
      "snapshot_date": "2026-04-27 00:00:00"
    }
  ],
  "pagination": {
    "total": 1,
    "count": 1,
    "per_page": 20,
    "current_page": 1,
    "total_pages": 1
  }
}
```
