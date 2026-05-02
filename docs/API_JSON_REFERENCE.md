# API JSON reference (AfaaqEducationalPlatform)

Base URL prefix for module APIs: **`/api/v1`**.  
Authentication: **`Authorization: Bearer {jwt}`** where routes use `auth:api`.

Unless noted, send **`Content-Type: application/json`**.

---

## 1. Standard response shapes

### Success (most controllers)

```json
{
  "status": "success",
  "message": "Operation successful",
  "data": {}
}
```

### Success paginated (`Controller::paginated`)

```json
{
  "status": "success",
  "message": "Operation successful",
  "data": [],
  "pagination": {
    "total": 100,
    "count": 20,
    "per_page": 20,
    "current_page": 1,
    "total_pages": 5
  }
}
```

### Validation error (`ApiFormRequest`)

```json
{
  "message": "The submitted data is invalid. Please review the validation errors and try again.",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

### Generic API exception (`bootstrap/app.php` handler)

```json
{
  "status": "error",
  "message": "Unauthenticated",
  "errors": {}
}
```

(`errors` appears only for validation-style payloads when applicable.)

---

## 2. Spatie Activity Log (super-admin)

Reads from the **`activity_log`** table (models using `App\Traits\LogsActivity`, e.g. `Course`, `Enrollment`, `Lesson`, …).

| Method | Path | Body |
|--------|------|------|
| GET | `/api/v1/super-admin/activity-log` | — (query params below) |
| GET | `/api/v1/super-admin/activity-log/{activity_log}` | — |

**Query params (index)**

| Param | Type | Description |
|-------|------|-------------|
| `page` | int | Page number |
| `per_page` | int | 1–100, default 20 |
| `log_name` | string | Exact match (e.g. `course`, `enrollment`) |
| `event` | string | e.g. `created`, `updated`, `deleted` |
| `subject_type` | string | Partial match on morph class (e.g. `Course`) |
| `subject_id` | int | Subject primary key |
| `causer_id` | int | User who caused the activity |
| `from` | date | `created_at` ≥ start |
| `to` | date | `created_at` ≤ end of day |
| `description` | string | `LIKE %…%` on description |

**Example request**

```http
GET /api/v1/super-admin/activity-log?per_page=10&event=updated&subject_type=Course
Authorization: Bearer …
```

**Example item in `data`**

```json
{
  "id": 42,
  "log_name": "course",
  "description": "Course was updated",
  "event": "updated",
  "subject_type": "Modules\\LearningModule\\Models\\Course",
  "subject_id": 7,
  "causer_type": "App\\Models\\User",
  "causer_id": 3,
  "properties": {
    "attributes": {},
    "old": {}
  },
  "batch_uuid": null,
  "created_at": "2026-05-02T10:00:00+00:00",
  "updated_at": "2026-05-02T10:00:00+00:00",
  "causer": {
    "id": 3,
    "name": "Admin",
    "email": "admin@example.com"
  }
}
```

---

## 3. Authentication

### POST `/api/v1/auth/register`

```json
{
  "name": "Student Name",
  "email": "student@example.com",
  "password": "Str0ng!Pass",
  "password_confirmation": "Str0ng!Pass",
  "phone": "+966501234567",
  "date_of_birth": "2000-01-15",
  "gender": "male",
  "address": "Riyadh",
  "education_level": "bachelor",
  "country": "SA",
  "bio": null,
  "specialization": null,
  "joined_at": null
}
```

### POST `/api/v1/auth/login`

```json
{
  "email": "student@example.com",
  "password": "secret"
}
```

### POST `/api/v1/auth/logout`

Body: empty JSON `{}` or empty body (authenticated).

### POST `/api/v1/auth/refresh`

Body: typically empty; requires valid refresh flow / token per JWT setup.

---

## 4. Reporting snapshots (super-admin)

Paths are under **`/api/v1/super-admin/snapshots`** (super-admin JWT required).

### POST `/api/v1/super-admin/snapshots/materialize`

```json
{
  "snapshot_date": "2026-05-01"
}
```

(`snapshot_date` optional; format `Y-m-d`.)

---

## 5. Courses & enrollment (examples)

### POST super-admin course (JSON fields; files separate)

Many endpoints use **multipart** when uploading `cover` / `intro_video`. JSON-only example for translatable fields:

```json
{
  "title": { "en": "Introduction to Arabic", "ar": "مقدمة في العربية" },
  "slug": "intro-arabic",
  "description": { "en": "…", "ar": "…" },
  "objectives": { "en": "…" },
  "prerequisites": { "en": "…" },
  "course_category_id": 1,
  "actual_duration_hours": 40,
  "language": "ar",
  "status": "draft",
  "min_score_to_pass": 60,
  "is_offline_available": true,
  "course_delivery_type": "self_paced",
  "difficulty_level": "beginner"
}
```

### POST `/api/v1/enrollments` (admin/super-admin flows — see route groups)

Typical body (abbrev.; see `StoreEnrollmentRequest` for full rules):

```json
{
  "learner_id": 12,
  "course_id": 5,
  "enrollment_type": "self",
  "enrolled_by": null
}
```

---

## 6. Assessment (examples)

### POST `/api/v1/quizzes`

See `QuizType`, `AssesmentType`, `QuizStatus` enums in code. Illustrative body:

```json
{
  "instructor_id": 3,
  "quizable_id": 10,
  "quizable_type": "lesson",
  "type": "formative",
  "title": { "en": "Quiz 1" },
  "description": { "en": "Optional" },
  "max_score": 100,
  "passing_score": 50,
  "status": "draft",
  "auto_grade_enabled": true,
  "available_from": null,
  "due_date": null
}
```

### POST `/api/v1/attempts`

```json
{
  "quiz_id": 1,
  "student_id": 12
}
```

---

## 7. Communication (examples)

### POST `/api/v1/notifications`

```json
{
  "user_ids": [12, 15],
  "title": "New assignment",
  "body": "Please complete unit 3 quiz.",
  "type": "course",
  "data": { "course_id": 5 }
}
```

### POST `/api/v1/virtual-sessions`

See `StoreVirtualSessionRequest` — includes `provider` (`zoom` | `google_classroom`), times, `integration_id`, and for Classroom **`metadata.google_course_id`**.

### POST `/api/v1/chat-threads` / messages / forum

See respectively:

- `Modules/CommunicationModule/app/Http/Requests/Chat/StoreChatThreadRequest.php`
- `Modules/CommunicationModule/app/Http/Requests/Chat/StoreChatMessageRequest.php`
- `Modules/CommunicationModule/app/Http/Requests/Forum/StoreForumThreadRequest.php`
- `Modules/CommunicationModule/app/Http/Requests/Forum/StoreForumPostRequest.php`

---

## 8. Where to find rules for every POST/PUT body

Form requests under each module define the canonical validation (and therefore JSON keys):

| Module | Path pattern |
|--------|----------------|
| UserMangementModule | `Modules/UserMangementModule/app/Http/Requests/**/*.php` |
| LearningModule | `Modules/LearningModule/app/Http/Requests/**/*.php` |
| AssesmentModule | `Modules/AssesmentModule/app/Http/Requests/**/*.php` |
| CommunicationModule | `Modules/CommunicationModule/app/Http/Requests/**/*.php` |
| ReportingModule | `Modules/ReportingModule/app/Http/Requests/**/*.php` |

Flat route list (paths + middleware): **`all_api_routes.md`** at repo root. Regenerate with:

```bash
php artisan route:list --json > storage/app/routes.json
```

---

## 9. Note on “complete” coverage

The platform exposes **hundreds** of routes; this file documents **response conventions**, the **new activity-log API**, and **high-traffic JSON bodies**. For any endpoint not listed here, open the matching `*Request.php` or controller for the exact payload.
