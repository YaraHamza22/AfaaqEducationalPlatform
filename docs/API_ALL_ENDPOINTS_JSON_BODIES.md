# API endpoints — methods and JSON bodies

Auto-generated: `php artisan api:export-json-bodies`

- **GET / HEAD / DELETE**: no JSON body unless noted (some DELETE endpoints accept JSON — rare here).
- **POST / PUT / PATCH**: example skeleton derived from `FormRequest::rules()` when the controller method type-hints one; otherwise `{}` or `_provider payload_`.
- Validation may require extra fields; multipart uploads (`cover`, files) are **not** pure JSON.

## `GET` `/api/profile`

**Middleware:** `api`, `auth:api`

**Body:** none.

## `DELETE` `/api/v1/answers/{answer}`

**Middleware:** `api`, `auth:api`, `permission:delete-answer`

**Body:** usually none.

## `GET` `/api/v1/answers/{answer}`

**Middleware:** `api`, `auth:api`, `permission:show-answer`

**Body:** none.

## `PUT` `/api/v1/answers/{answer}`

**Middleware:** `api`, `auth:api`, `permission:update-answer`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `PATCH` `/api/v1/answers/{answer}`

**Middleware:** `api`, `auth:api`, `permission:update-answer`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `GET` `/api/v1/answers`

**Middleware:** `api`, `auth:api`, `permission:list-answers`

**Body:** none.

## `POST` `/api/v1/answers`

**Middleware:** `api`, `auth:api`, `permission:create-answer`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `DELETE` `/api/v1/assesmentmodules/{assesmentmodule}`

**Middleware:** `api`, `auth:api`

**Body:** usually none.

## `GET` `/api/v1/assesmentmodules/{assesmentmodule}`

**Middleware:** `api`, `auth:api`

**Body:** none.

## `PUT` `/api/v1/assesmentmodules/{assesmentmodule}`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(could not resolve controller — webhook or closure; send provider-specific JSON if applicable)*

```json
{}
```

## `PATCH` `/api/v1/assesmentmodules/{assesmentmodule}`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(could not resolve controller — webhook or closure; send provider-specific JSON if applicable)*

```json
{}
```

## `GET` `/api/v1/assesmentmodules`

**Middleware:** `api`, `auth:api`

**Body:** none.

## `POST` `/api/v1/assesmentmodules`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(could not resolve controller — webhook or closure; send provider-specific JSON if applicable)*

```json
{}
```

## `POST` `/api/v1/attempts/{attempt}/grade`

**Middleware:** `api`, `auth:api`, `permission:grade-attempt`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `POST` `/api/v1/attempts/{attempt}/start`

**Middleware:** `api`, `auth:api`, `permission:submit-attempt`

**FormRequest:** `Modules\AssesmentModule\Http\Requests\AttemptRequest\StartAttemptRequest`

```json
{
    "quiz_id": 0,
    "student_id": 0
}
```

## `POST` `/api/v1/attempts/{attempt}/submit`

**Middleware:** `api`, `auth:api`, `permission:submit-attempt`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `DELETE` `/api/v1/attempts/{attempt}`

**Middleware:** `api`, `auth:api`, `permission:delete-attempt`

**Body:** usually none.

## `GET` `/api/v1/attempts/{attempt}`

**Middleware:** `api`, `auth:api`, `permission:show-attempt`

**Body:** none.

## `PUT` `/api/v1/attempts/{attempt}`

**Middleware:** `api`, `auth:api`, `permission:update-attempt`

**Body:** `{}` *(rules resolution failed: Class "Modules\AssesmentModule\Http\Requests\AttemptRequest\AttemptStatus" not found)*

```json
{}
```

## `PATCH` `/api/v1/attempts/{attempt}`

**Middleware:** `api`, `auth:api`, `permission:update-attempt`

**Body:** `{}` *(rules resolution failed: Class "Modules\AssesmentModule\Http\Requests\AttemptRequest\AttemptStatus" not found)*

```json
{}
```

## `GET` `/api/v1/attempts`

**Middleware:** `api`, `auth:api`, `permission:list-attempts`

**Body:** none.

## `POST` `/api/v1/attempts`

**Middleware:** `api`, `auth:api`, `permission:create-attempt`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `POST` `/api/v1/auth/login`

**Middleware:** `api`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `POST` `/api/v1/auth/logout`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `GET` `/api/v1/auth/profile`

**Middleware:** `api`, `auth:api`

**Body:** none.

## `POST` `/api/v1/auth/refresh`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `POST` `/api/v1/auth/register`

**Middleware:** `api`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `POST` `/api/v1/chat-messages/{chatMessage}/read`

**Middleware:** `api`, `auth:api`, `permission:create-chat_message`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `DELETE` `/api/v1/chat-messages/{chatMessage}`

**Middleware:** `api`, `auth:api`, `permission:delete-chat_message`

**Body:** usually none.

## `POST` `/api/v1/chat-threads/{chatThread}/archive`

**Middleware:** `api`, `auth:api`, `permission:update-chat_message`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `GET` `/api/v1/chat-threads/{chatThread}/messages`

**Middleware:** `api`, `auth:api`, `permission:list-chat_messages`

**Body:** none.

## `POST` `/api/v1/chat-threads/{chatThread}/messages`

**Middleware:** `api`, `auth:api`, `permission:create-chat_message`

**Body:** `{}` *(rules resolution failed: The body field is required.)*

```json
{}
```

## `DELETE` `/api/v1/chat-threads/{chatThread}/participants/{userId}`

**Middleware:** `api`, `auth:api`, `permission:delete-chat_message`

**Body:** usually none.

## `GET` `/api/v1/chat-threads/{chatThread}/participants`

**Middleware:** `api`, `auth:api`, `permission:list-chat_messages`

**Body:** none.

## `POST` `/api/v1/chat-threads/{chatThread}/participants`

**Middleware:** `api`, `auth:api`, `permission:create-chat_message`

**Body:** `{}` *(rules resolution failed: The user id field is required.)*

```json
{}
```

## `GET` `/api/v1/chat-threads/{chat_thread}`

**Middleware:** `api`, `auth:api`, `permission:list-chat_messages`

**Body:** none.

## `GET` `/api/v1/chat-threads`

**Middleware:** `api`, `auth:api`, `permission:list-chat_messages`

**Body:** none.

## `POST` `/api/v1/chat-threads`

**Middleware:** `api`, `auth:api`, `permission:create-chat_message`

**Body:** `{}` *(rules resolution failed: The participant ids field is required.)*

```json
{}
```

## `GET` `/api/v1/chat-unread-count`

**Middleware:** `api`, `auth:api`, `permission:list-chat_messages`

**Body:** none.

## `POST` `/api/v1/complete-profile`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(could not resolve controller — webhook or closure; send provider-specific JSON if applicable)*

```json
{}
```

## `POST` `/api/v1/course-categories/{courseCategory}/activate`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `POST` `/api/v1/course-categories/{courseCategory}/deactivate`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `DELETE` `/api/v1/course-categories/{courseCategory}`

**Middleware:** `api`, `auth:api`, `permission:delete-category`

**Body:** usually none.

## `GET` `/api/v1/course-categories/{courseCategory}`

**Middleware:** `api`, `auth:api`, `permission:show-category`

**Body:** none.

## `PUT` `/api/v1/course-categories/{courseCategory}`

**Middleware:** `api`, `auth:api`, `permission:update-category`

**FormRequest:** `Modules\LearningModule\Http\Requests\CourseCategory\UpdateCourseCategoryRequest`

```json
{
    "name": {
        "en": "",
        "ar": ""
    },
    "slug": "",
    "description": {
        "en": "",
        "ar": ""
    },
    "is_active": false,
    "target_audience": ""
}
```

## `PATCH` `/api/v1/course-categories/{courseCategory}`

**Middleware:** `api`, `auth:api`, `permission:update-category`

**FormRequest:** `Modules\LearningModule\Http\Requests\CourseCategory\UpdateCourseCategoryRequest`

```json
{
    "name": {
        "en": "",
        "ar": ""
    },
    "slug": "",
    "description": {
        "en": "",
        "ar": ""
    },
    "is_active": false,
    "target_audience": ""
}
```

## `GET` `/api/v1/course-categories`

**Middleware:** `api`, `auth:api`, `permission:list-categories`

**Body:** none.

## `POST` `/api/v1/course-categories`

**Middleware:** `api`, `auth:api`, `permission:create-category`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `GET` `/api/v1/courses-enrollable`

**Middleware:** `api`, `auth:api`

**Body:** none.

## `GET` `/api/v1/courses/enrollable/list`

**Middleware:** `api`, `auth:api`, `role:student,api`

**Body:** none.

## `GET` `/api/v1/courses/{courseId}/assessment-progress`

**Middleware:** `api`, `auth:api`

**Body:** none.

## `GET` `/api/v1/courses/{courseId}/certificate`

**Middleware:** `api`, `auth:api`

**Body:** none.

## `POST` `/api/v1/courses/{course}/assign-instructor`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `POST` `/api/v1/courses/{course}/change-status`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `GET` `/api/v1/courses/{course}/duration`

**Middleware:** `api`, `auth:api`

**Body:** none.

## `POST` `/api/v1/courses/{course}/enroll`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(could not resolve controller — webhook or closure; send provider-specific JSON if applicable)*

```json
{}
```

## `GET` `/api/v1/courses/{course}/instructors`

**Middleware:** `api`, `auth:api`

**Body:** none.

## `POST` `/api/v1/courses/{course}/publish`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `GET` `/api/v1/courses/{course}/publishability`

**Middleware:** `api`, `auth:api`

**Body:** none.

## `POST` `/api/v1/courses/{course}/remove-instructor`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `POST` `/api/v1/courses/{course}/set-primary-instructor`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `GET` `/api/v1/courses/{course}/units/count`

**Middleware:** `api`, `auth:api`

**Body:** none.

## `POST` `/api/v1/courses/{course}/units/reorder`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `GET` `/api/v1/courses/{course}/units`

**Middleware:** `api`, `auth:api`

**Body:** none.

## `POST` `/api/v1/courses/{course}/unpublish`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `POST` `/api/v1/courses/{course}/unset-primary-instructor`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `DELETE` `/api/v1/courses/{course}`

**Middleware:** `api`, `auth:api`, `permission:delete-course`

**Body:** usually none.

## `GET` `/api/v1/courses/{course}`

**Middleware:** `api`, `auth:api`, `permission:show-course`

**Body:** none.

## `PUT` `/api/v1/courses/{course}`

**Middleware:** `api`, `auth:api`, `permission:update-course`

**FormRequest:** `Modules\LearningModule\Http\Requests\Course\UpdateCourseRequest`

```json
{
    "title": {
        "en": "",
        "ar": ""
    },
    "slug": "",
    "description": {
        "en": "",
        "ar": ""
    },
    "objectives": {
        "en": "",
        "ar": ""
    },
    "prerequisites": {
        "en": "",
        "ar": ""
    },
    "course_category_id": 0,
    "actual_duration_hours": 0,
    "language": "",
    "status": "",
    "min_score_to_pass": 0,
    "is_offline_available": false,
    "course_delivery_type": "",
    "difficulty_level": "",
    "cover": "",
    "intro_video": ""
}
```

## `PATCH` `/api/v1/courses/{course}`

**Middleware:** `api`, `auth:api`, `permission:update-course`

**FormRequest:** `Modules\LearningModule\Http\Requests\Course\UpdateCourseRequest`

```json
{
    "title": {
        "en": "",
        "ar": ""
    },
    "slug": "",
    "description": {
        "en": "",
        "ar": ""
    },
    "objectives": {
        "en": "",
        "ar": ""
    },
    "prerequisites": {
        "en": "",
        "ar": ""
    },
    "course_category_id": 0,
    "actual_duration_hours": 0,
    "language": "",
    "status": "",
    "min_score_to_pass": 0,
    "is_offline_available": false,
    "course_delivery_type": "",
    "difficulty_level": "",
    "cover": "",
    "intro_video": ""
}
```

## `GET` `/api/v1/courses`

**Middleware:** `api`, `auth:api`, `permission:list-courses`

**Body:** none.

## `POST` `/api/v1/courses`

**Middleware:** `api`, `auth:api`, `permission:create-course`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `GET` `/api/v1/dashboard`

**Middleware:** `api`, `auth:api`, `role:student,api`

**Body:** none.

## `GET` `/api/v1/enrollments/{enrollment}/progress`

**Middleware:** `api`, `auth:api`, `role:student,api`

**Body:** none.

## `GET` `/api/v1/enrollments/{enrollment}`

**Middleware:** `api`, `auth:api`, `role:student,api`

**Body:** none.

## `GET` `/api/v1/enrollments`

**Middleware:** `api`, `auth:api`, `role:student,api`

**Body:** none.

## `POST` `/api/v1/enrollments`

**Middleware:** `api`, `auth:api`, `role:student,api`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `DELETE` `/api/v1/external-integrations/{external_integration}`

**Middleware:** `api`, `auth:api`

**Body:** usually none.

## `PUT` `/api/v1/external-integrations/{external_integration}`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(rules resolution failed: The provider field is required.)*

```json
{}
```

## `PATCH` `/api/v1/external-integrations/{external_integration}`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(rules resolution failed: The provider field is required.)*

```json
{}
```

## `POST` `/api/v1/external-integrations/{provider}/exchange-code`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `GET` `/api/v1/external-integrations/{provider}/oauth-url`

**Middleware:** `api`, `auth:api`

**Body:** none.

## `GET` `/api/v1/external-integrations`

**Middleware:** `api`, `auth:api`

**Body:** none.

## `POST` `/api/v1/external-integrations`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(rules resolution failed: The provider field is required.)*

```json
{}
```

## `POST` `/api/v1/forum-post-reports/{forumPostReport}/review`

**Middleware:** `api`, `auth:api`, `permission:update-post_forum`

**Body:** `{}` *(rules resolution failed: The status field is required.)*

```json
{}
```

## `POST` `/api/v1/forum-posts/{forumPost}/react`

**Middleware:** `api`, `auth:api`, `permission:create-post_forum`

**Body:** `{}` *(rules resolution failed: The reaction field is required.)*

```json
{}
```

## `POST` `/api/v1/forum-posts/{forumPost}/report`

**Middleware:** `api`, `auth:api`, `permission:create-post_forum`

**Body:** `{}` *(rules resolution failed: The reason field is required.)*

```json
{}
```

## `DELETE` `/api/v1/forum-posts/{forumPost}`

**Middleware:** `api`, `auth:api`, `permission:delete-post_forum`

**Body:** usually none.

## `PUT` `/api/v1/forum-posts/{forumPost}`

**Middleware:** `api`, `auth:api`, `permission:update-post_forum`

**Body:** `{}` *(rules resolution failed: The body field is required.)*

```json
{}
```

## `POST` `/api/v1/forum-threads/{forumThread}/lock`

**Middleware:** `api`, `auth:api`, `permission:update-thread_forum`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `POST` `/api/v1/forum-threads/{forumThread}/pin`

**Middleware:** `api`, `auth:api`, `permission:update-thread_forum`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `GET` `/api/v1/forum-threads/{forumThread}/posts`

**Middleware:** `api`, `auth:api`, `permission:list-posts_forum`

**Body:** none.

## `POST` `/api/v1/forum-threads/{forumThread}/posts`

**Middleware:** `api`, `auth:api`, `permission:create-post_forum`

**Body:** `{}` *(rules resolution failed: The body field is required.)*

```json
{}
```

## `DELETE` `/api/v1/forum-threads/{forum_thread}`

**Middleware:** `api`, `auth:api`, `permission:delete-thread_forum`

**Body:** usually none.

## `GET` `/api/v1/forum-threads/{forum_thread}`

**Middleware:** `api`, `auth:api`, `permission:list-threads_forum`

**Body:** none.

## `PUT` `/api/v1/forum-threads/{forum_thread}`

**Middleware:** `api`, `auth:api`, `permission:update-thread_forum`

**FormRequest:** `Modules\CommunicationModule\Http\Requests\Forum\UpdateForumThreadRequest`

```json
{
    "title": "",
    "body": "",
    "is_pinned": false,
    "is_locked": false
}
```

## `PATCH` `/api/v1/forum-threads/{forum_thread}`

**Middleware:** `api`, `auth:api`, `permission:update-thread_forum`

**FormRequest:** `Modules\CommunicationModule\Http\Requests\Forum\UpdateForumThreadRequest`

```json
{
    "title": "",
    "body": "",
    "is_pinned": false,
    "is_locked": false
}
```

## `GET` `/api/v1/forum-threads`

**Middleware:** `api`, `auth:api`, `permission:list-threads_forum`

**Body:** none.

## `POST` `/api/v1/forum-threads`

**Middleware:** `api`, `auth:api`, `permission:create-thread_forum`

**Body:** `{}` *(rules resolution failed: The course id field is required. (and 1 more error))*

```json
{}
```

## `GET` `/api/v1/instructor/dashboard/{instructorId}`

**Middleware:** `api`, `auth:api`, `role:instructor`

**Body:** none.

## `GET` `/api/v1/instructors/{instructorId}/courses`

**Middleware:** `api`, `auth:api`

**Body:** none.

## `GET` `/api/v1/lessons/unit/{unit}`

**Middleware:** `api`, `auth:api`, `role:student,api`

**Body:** none.

## `GET` `/api/v1/lessons/{lesson}/duration`

**Middleware:** `api`, `auth:api`, `role:student,api`

**Body:** none.

## `DELETE` `/api/v1/lessons/{lesson}`

**Middleware:** `api`, `auth:api`, `permission:delete-lesson`

**Body:** usually none.

## `GET` `/api/v1/lessons/{lesson}`

**Middleware:** `api`, `auth:api`, `role:student,api`, `permission:show-lesson`

**Body:** none.

## `PUT` `/api/v1/lessons/{lesson}`

**Middleware:** `api`, `auth:api`, `permission:update-lesson`

**FormRequest:** `Modules\LearningModule\Http\Requests\Lesson\UpdateLessonRequest`

```json
{
    "unit_id": 0,
    "title": {
        "en": "",
        "ar": ""
    },
    "description": {
        "en": "",
        "ar": ""
    },
    "lesson_order": 0,
    "lesson_type": "",
    "is_required": false,
    "actual_duration_minutes": 0,
    "video": "",
    "attachments": []
}
```

## `PATCH` `/api/v1/lessons/{lesson}`

**Middleware:** `api`, `auth:api`, `permission:update-lesson`

**FormRequest:** `Modules\LearningModule\Http\Requests\Lesson\UpdateLessonRequest`

```json
{
    "unit_id": 0,
    "title": {
        "en": "",
        "ar": ""
    },
    "description": {
        "en": "",
        "ar": ""
    },
    "lesson_order": 0,
    "lesson_type": "",
    "is_required": false,
    "actual_duration_minutes": 0,
    "video": "",
    "attachments": []
}
```

## `GET` `/api/v1/lessons`

**Middleware:** `api`, `auth:api`, `permission:list-lessons`

**Body:** none.

## `POST` `/api/v1/lessons`

**Middleware:** `api`, `auth:api`, `permission:create-lesson`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `GET` `/api/v1/my-courses/{course}/duration`

**Middleware:** `api`, `auth:api`, `role:instructor,api`

**Body:** none.

## `GET` `/api/v1/my-courses/{course}/publishability`

**Middleware:** `api`, `auth:api`, `role:instructor,api`

**Body:** none.

## `GET` `/api/v1/my-courses/{course}/units/count`

**Middleware:** `api`, `auth:api`, `role:instructor,api`

**Body:** none.

## `POST` `/api/v1/my-courses/{course}/units/reorder`

**Middleware:** `api`, `auth:api`, `role:instructor,api`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `GET` `/api/v1/my-courses/{course}/units/{unit}/duration`

**Middleware:** `api`, `auth:api`, `role:instructor,api`

**Body:** none.

## `GET` `/api/v1/my-courses/{course}/units/{unit}/lessons/count`

**Middleware:** `api`, `auth:api`, `role:instructor,api`

**Body:** none.

## `POST` `/api/v1/my-courses/{course}/units/{unit}/lessons/reorder`

**Middleware:** `api`, `auth:api`, `role:instructor,api`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `GET` `/api/v1/my-courses/{course}/units/{unit}/lessons/{lesson}/duration`

**Middleware:** `api`, `auth:api`, `role:instructor,api`

**Body:** none.

## `PUT` `/api/v1/my-courses/{course}/units/{unit}/lessons/{lesson}/position`

**Middleware:** `api`, `auth:api`, `role:instructor,api`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `DELETE` `/api/v1/my-courses/{course}/units/{unit}/lessons/{lesson}`

**Middleware:** `api`, `auth:api`, `role:instructor,api`, `permission:delete-lesson`

**Body:** usually none.

## `GET` `/api/v1/my-courses/{course}/units/{unit}/lessons/{lesson}`

**Middleware:** `api`, `auth:api`, `role:instructor,api`, `permission:show-lesson`

**Body:** none.

## `PUT` `/api/v1/my-courses/{course}/units/{unit}/lessons/{lesson}`

**Middleware:** `api`, `auth:api`, `role:instructor,api`, `permission:update-lesson`

**FormRequest:** `Modules\LearningModule\Http\Requests\Lesson\UpdateLessonRequest`

```json
{
    "unit_id": 0,
    "title": {
        "en": "",
        "ar": ""
    },
    "description": {
        "en": "",
        "ar": ""
    },
    "lesson_order": 0,
    "lesson_type": "",
    "is_required": false,
    "actual_duration_minutes": 0,
    "video": "",
    "attachments": []
}
```

## `GET` `/api/v1/my-courses/{course}/units/{unit}/lessons`

**Middleware:** `api`, `auth:api`, `role:instructor,api`, `permission:list-lessons`

**Body:** none.

## `POST` `/api/v1/my-courses/{course}/units/{unit}/lessons`

**Middleware:** `api`, `auth:api`, `role:instructor,api`, `permission:create-lesson`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `PUT` `/api/v1/my-courses/{course}/units/{unit}/position`

**Middleware:** `api`, `auth:api`, `role:instructor,api`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `DELETE` `/api/v1/my-courses/{course}/units/{unit}`

**Middleware:** `api`, `auth:api`, `role:instructor,api`, `permission:delete-unit`

**Body:** usually none.

## `GET` `/api/v1/my-courses/{course}/units/{unit}`

**Middleware:** `api`, `auth:api`, `role:instructor,api`, `permission:show-unit`

**Body:** none.

## `PUT` `/api/v1/my-courses/{course}/units/{unit}`

**Middleware:** `api`, `auth:api`, `role:instructor,api`, `permission:update-unit`

**FormRequest:** `Modules\LearningModule\Http\Requests\Unit\UpdateUnitRequest`

```json
{
    "course_id": 0,
    "title": {
        "en": "",
        "ar": ""
    },
    "description": {
        "en": "",
        "ar": ""
    },
    "unit_order": 0,
    "actual_duration_minutes": 0
}
```

## `GET` `/api/v1/my-courses/{course}/units`

**Middleware:** `api`, `auth:api`, `role:instructor,api`, `permission:list-units`

**Body:** none.

## `POST` `/api/v1/my-courses/{course}/units`

**Middleware:** `api`, `auth:api`, `role:instructor,api`, `permission:create-unit`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `GET` `/api/v1/my-courses/{course}`

**Middleware:** `api`, `auth:api`, `role:instructor,api`, `permission:show-course`

**Body:** none.

## `GET` `/api/v1/my-courses`

**Middleware:** `api`, `auth:api`, `role:instructor,api`, `permission:list-courses`

**Body:** none.

## `GET` `/api/v1/my-learning/{course}/units/{unit}/duration`

**Middleware:** `api`, `auth:api`, `role:student,api`

**Body:** none.

## `GET` `/api/v1/my-learning/{course}/units/{unit}/lessons/{lesson}/duration`

**Middleware:** `api`, `auth:api`, `role:student,api`

**Body:** none.

## `GET` `/api/v1/my-learning/{course}/units/{unit}/lessons/{lesson}`

**Middleware:** `api`, `auth:api`, `role:student,api`, `permission:show-lesson`

**Body:** none.

## `GET` `/api/v1/my-learning/{course}/units/{unit}/lessons`

**Middleware:** `api`, `auth:api`, `role:student,api`, `permission:list-lessons`

**Body:** none.

## `GET` `/api/v1/my-learning/{course}/units/{unit}`

**Middleware:** `api`, `auth:api`, `role:student,api`, `permission:show-unit`

**Body:** none.

## `GET` `/api/v1/my-learning/{course}/units`

**Middleware:** `api`, `auth:api`, `role:student,api`, `permission:list-units`

**Body:** none.

## `GET` `/api/v1/my-learning/{course}`

**Middleware:** `api`, `auth:api`, `role:student,api`, `permission:show-course`

**Body:** none.

## `GET` `/api/v1/my-learning`

**Middleware:** `api`, `auth:api`, `role:student,api`, `permission:list-courses`

**Body:** none.

## `POST` `/api/v1/notifications/digest/trigger`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `POST` `/api/v1/notifications/read-all`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `POST` `/api/v1/notifications/{notificationId}/read`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `GET` `/api/v1/notifications`

**Middleware:** `api`, `auth:api`

**Body:** none.

## `POST` `/api/v1/notifications`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(rules resolution failed: The user ids field is required. (and 2 more errors))*

```json
{}
```

## `GET` `/api/v1/offline-packages/download/{token}`

**Middleware:** `api`, `auth:api`

**Body:** none.

## `POST` `/api/v1/offline-packages/tokens/{offlineDownloadToken}/revoke`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `POST` `/api/v1/offline-packages/{offlinePackage}/tokens`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(rules resolution failed: The user id field is required. (and 1 more error))*

```json
{}
```

## `GET` `/api/v1/offline-packages/{offline_package}`

**Middleware:** `api`, `auth:api`

**Body:** none.

## `GET` `/api/v1/offline-packages`

**Middleware:** `api`, `auth:api`

**Body:** none.

## `POST` `/api/v1/offline-packages`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(rules resolution failed: The course id field is required. (and 2 more errors))*

```json
{}
```

## `POST` `/api/v1/offline-sync-logs`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(rules resolution failed: The device id field is required. (and 1 more error))*

```json
{}
```

## `DELETE` `/api/v1/question-options/{question_option}`

**Middleware:** `api`, `auth:api`, `permission:delete-option`

**Body:** usually none.

## `GET` `/api/v1/question-options/{question_option}`

**Middleware:** `api`, `auth:api`, `permission:show-option`

**Body:** none.

## `PUT` `/api/v1/question-options/{question_option}`

**Middleware:** `api`, `auth:api`, `permission:update-option`

**FormRequest:** `Modules\AssesmentModule\Http\Requests\QuestionOptionRequest\UpdateQuestionOptionRequest`

```json
{
    "option_text": [],
    "is_correct": false
}
```

## `PATCH` `/api/v1/question-options/{question_option}`

**Middleware:** `api`, `auth:api`, `permission:update-option`

**FormRequest:** `Modules\AssesmentModule\Http\Requests\QuestionOptionRequest\UpdateQuestionOptionRequest`

```json
{
    "option_text": [],
    "is_correct": false
}
```

## `GET` `/api/v1/question-options`

**Middleware:** `api`, `auth:api`, `permission:list-options`

**Body:** none.

## `POST` `/api/v1/question-options`

**Middleware:** `api`, `auth:api`, `permission:create-option`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `DELETE` `/api/v1/questions/{question}`

**Middleware:** `api`, `auth:api`, `permission:delete-question`

**Body:** usually none.

## `GET` `/api/v1/questions/{question}`

**Middleware:** `api`, `auth:api`, `permission:show-question`

**Body:** none.

## `PUT` `/api/v1/questions/{question}`

**Middleware:** `api`, `auth:api`, `permission:update-question`

**Body:** `{}` *(rules resolution failed: Attempt to read property "quiz_id" on null)*

```json
{}
```

## `PATCH` `/api/v1/questions/{question}`

**Middleware:** `api`, `auth:api`, `permission:update-question`

**Body:** `{}` *(rules resolution failed: Attempt to read property "quiz_id" on null)*

```json
{}
```

## `GET` `/api/v1/questions`

**Middleware:** `api`, `auth:api`, `permission:list-questions`

**Body:** none.

## `POST` `/api/v1/questions`

**Middleware:** `api`, `auth:api`, `permission:create-question`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `POST` `/api/v1/quizzes/{quiz}/archive`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `POST` `/api/v1/quizzes/{quiz}/publish`

**Middleware:** `api`, `auth:api`, `permission:publish-quiz`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `POST` `/api/v1/quizzes/{quiz}/unpublish`

**Middleware:** `api`, `auth:api`, `permission:unpublish-quiz`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `DELETE` `/api/v1/quizzes/{quiz}`

**Middleware:** `api`, `auth:api`, `permission:delete-quiz`

**Body:** usually none.

## `GET` `/api/v1/quizzes/{quiz}`

**Middleware:** `api`, `auth:api`, `permission:show-quiz`

**Body:** none.

## `PUT` `/api/v1/quizzes/{quiz}`

**Middleware:** `api`, `auth:api`, `permission:update-quiz`

**FormRequest:** `Modules\AssesmentModule\Http\Requests\QuizRequest\UpdateQuizRequest`

```json
{
    "instructor_id": "",
    "quizable_id": 0,
    "quizable_type": "",
    "type": "",
    "title": [],
    "description": [],
    "max_score": 0,
    "passing_score": 0,
    "status": "",
    "auto_grade_enabled": false,
    "available_from": "2026-01-01",
    "due_date": "2026-01-01",
    "duration_minutes": 0
}
```

## `PATCH` `/api/v1/quizzes/{quiz}`

**Middleware:** `api`, `auth:api`, `permission:update-quiz`

**FormRequest:** `Modules\AssesmentModule\Http\Requests\QuizRequest\UpdateQuizRequest`

```json
{
    "instructor_id": "",
    "quizable_id": 0,
    "quizable_type": "",
    "type": "",
    "title": [],
    "description": [],
    "max_score": 0,
    "passing_score": 0,
    "status": "",
    "auto_grade_enabled": false,
    "available_from": "2026-01-01",
    "due_date": "2026-01-01",
    "duration_minutes": 0
}
```

## `GET` `/api/v1/quizzes`

**Middleware:** `api`, `auth:api`, `permission:list-quiz`

**Body:** none.

## `POST` `/api/v1/quizzes`

**Middleware:** `api`, `auth:api`, `permission:create-quiz`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `GET` `/api/v1/student/dashboard/{studentId}`

**Middleware:** `api`, `auth:api`, `role:student`

**Body:** none.

## `GET` `/api/v1/super-admin/activity-log/{activity_log}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** none.

## `GET` `/api/v1/super-admin/activity-log`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** none.

## `DELETE` `/api/v1/super-admin/auditors/{auditor}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:delete-auditor`

**Body:** usually none.

## `GET` `/api/v1/super-admin/auditors/{auditor}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:show-auditor`

**Body:** none.

## `PUT` `/api/v1/super-admin/auditors/{auditor}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:update-auditor`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `GET` `/api/v1/super-admin/auditors`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:list-auditors`

**Body:** none.

## `POST` `/api/v1/super-admin/auditors`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:create-auditor`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `POST` `/api/v1/super-admin/auth/login`

**Middleware:** `api`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `POST` `/api/v1/super-admin/auth/logout`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `GET` `/api/v1/super-admin/auth/profile`

**Middleware:** `api`, `auth:api`

**Body:** none.

## `POST` `/api/v1/super-admin/auth/refresh`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `POST` `/api/v1/super-admin/auth/register`

**Middleware:** `api`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `POST` `/api/v1/super-admin/course-categories/{courseCategory}/activate`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `POST` `/api/v1/super-admin/course-categories/{courseCategory}/deactivate`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `DELETE` `/api/v1/super-admin/course-categories/{courseCategory}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:delete-category`

**Body:** usually none.

## `GET` `/api/v1/super-admin/course-categories/{courseCategory}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:show-category`

**Body:** none.

## `PUT` `/api/v1/super-admin/course-categories/{courseCategory}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:update-category`

**FormRequest:** `Modules\LearningModule\Http\Requests\CourseCategory\UpdateCourseCategoryRequest`

```json
{
    "name": {
        "en": "",
        "ar": ""
    },
    "slug": "",
    "description": {
        "en": "",
        "ar": ""
    },
    "is_active": false,
    "target_audience": ""
}
```

## `GET` `/api/v1/super-admin/course-categories`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:list-categories`

**Body:** none.

## `POST` `/api/v1/super-admin/course-categories`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:create-category`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `GET` `/api/v1/super-admin/courses/enrollable/list`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** none.

## `GET` `/api/v1/super-admin/courses/instructor/{instructorId}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** none.

## `GET` `/api/v1/super-admin/courses/{course}/duration`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** none.

## `POST` `/api/v1/super-admin/courses/{course}/instructors/assign`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `DELETE` `/api/v1/super-admin/courses/{course}/instructors/primary`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** usually none.

## `PUT` `/api/v1/super-admin/courses/{course}/instructors/primary`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `GET` `/api/v1/super-admin/courses/{course}/instructors`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** none.

## `POST` `/api/v1/super-admin/courses/{course}/publish`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `GET` `/api/v1/super-admin/courses/{course}/publishability`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** none.

## `POST` `/api/v1/super-admin/courses/{course}/remove-instructor`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `PUT` `/api/v1/super-admin/courses/{course}/status`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `POST` `/api/v1/super-admin/courses/{course}/units/reorder`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `DELETE` `/api/v1/super-admin/courses/{course}/units/{unit}/lessons/{lesson}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:delete-lesson`

**Body:** usually none.

## `GET` `/api/v1/super-admin/courses/{course}/units/{unit}/lessons/{lesson}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:show-lesson`

**Body:** none.

## `PUT` `/api/v1/super-admin/courses/{course}/units/{unit}/lessons/{lesson}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:update-lesson`

**FormRequest:** `Modules\LearningModule\Http\Requests\Lesson\UpdateLessonRequest`

```json
{
    "unit_id": 0,
    "title": {
        "en": "",
        "ar": ""
    },
    "description": {
        "en": "",
        "ar": ""
    },
    "lesson_order": 0,
    "lesson_type": "",
    "is_required": false,
    "actual_duration_minutes": 0,
    "video": "",
    "attachments": []
}
```

## `GET` `/api/v1/super-admin/courses/{course}/units/{unit}/lessons`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:list-lessons`

**Body:** none.

## `POST` `/api/v1/super-admin/courses/{course}/units/{unit}/lessons`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:create-lesson`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `DELETE` `/api/v1/super-admin/courses/{course}/units/{unit}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:delete-unit`

**Body:** usually none.

## `GET` `/api/v1/super-admin/courses/{course}/units/{unit}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:show-unit`

**Body:** none.

## `PUT` `/api/v1/super-admin/courses/{course}/units/{unit}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:update-unit`

**FormRequest:** `Modules\LearningModule\Http\Requests\Unit\UpdateUnitRequest`

```json
{
    "course_id": 0,
    "title": {
        "en": "",
        "ar": ""
    },
    "description": {
        "en": "",
        "ar": ""
    },
    "unit_order": 0,
    "actual_duration_minutes": 0
}
```

## `GET` `/api/v1/super-admin/courses/{course}/units`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:list-units`

**Body:** none.

## `POST` `/api/v1/super-admin/courses/{course}/units`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:create-unit`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `POST` `/api/v1/super-admin/courses/{course}/unpublish`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `DELETE` `/api/v1/super-admin/courses/{course}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:delete-course`

**Body:** usually none.

## `GET` `/api/v1/super-admin/courses/{course}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:show-course`

**Body:** none.

## `PUT` `/api/v1/super-admin/courses/{course}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:update-course`

**FormRequest:** `Modules\LearningModule\Http\Requests\Course\UpdateCourseRequest`

```json
{
    "title": {
        "en": "",
        "ar": ""
    },
    "slug": "",
    "description": {
        "en": "",
        "ar": ""
    },
    "objectives": {
        "en": "",
        "ar": ""
    },
    "prerequisites": {
        "en": "",
        "ar": ""
    },
    "course_category_id": 0,
    "actual_duration_hours": 0,
    "language": "",
    "status": "",
    "min_score_to_pass": 0,
    "is_offline_available": false,
    "course_delivery_type": "",
    "difficulty_level": "",
    "cover": "",
    "intro_video": ""
}
```

## `GET` `/api/v1/super-admin/courses`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:list-courses`

**Body:** none.

## `POST` `/api/v1/super-admin/courses`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:create-course`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `GET` `/api/v1/super-admin/dashboard`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** none.

## `GET` `/api/v1/super-admin/enrollments/{enrollment}/progress`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** none.

## `PUT` `/api/v1/super-admin/enrollments/{enrollment}/status`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `GET` `/api/v1/super-admin/enrollments/{enrollment}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** none.

## `PUT` `/api/v1/super-admin/enrollments/{enrollment}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**FormRequest:** `Modules\LearningModule\Http\Requests\Enrollment\UpdateEnrollmentRequest`

```json
{
    "enrollment_type": "",
    "progress_percentage": 0
}
```

## `GET` `/api/v1/super-admin/enrollments`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** none.

## `POST` `/api/v1/super-admin/enrollments`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `DELETE` `/api/v1/super-admin/instructors/{instructor}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:delete-instructor`

**Body:** usually none.

## `GET` `/api/v1/super-admin/instructors/{instructor}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:show-instructor`

**Body:** none.

## `PUT` `/api/v1/super-admin/instructors/{instructor}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:update-instructor`

**FormRequest:** `Modules\UserMangementModule\Http\Requests\Api\V1\Instructor\InstructorUpdateRequest`

```json
{
    "name": "",
    "email": "",
    "password": "",
    "phone": "",
    "date_of_birth": "2026-01-01",
    "gender": "",
    "address": "",
    "specialization": "",
    "bio": "",
    "years_of_experience": 0,
    "avatar": "",
    "cv": ""
}
```

## `GET` `/api/v1/super-admin/instructors`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:list-instructors`

**Body:** none.

## `POST` `/api/v1/super-admin/instructors`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:create-instructor`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `GET` `/api/v1/super-admin/lessons/unit/{unit}/count`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** none.

## `GET` `/api/v1/super-admin/lessons/unit/{unit}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** none.

## `GET` `/api/v1/super-admin/lessons/{lesson}/duration`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** none.

## `DELETE` `/api/v1/super-admin/lessons/{lesson}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:delete-lesson`

**Body:** usually none.

## `GET` `/api/v1/super-admin/lessons/{lesson}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:show-lesson`

**Body:** none.

## `PUT` `/api/v1/super-admin/lessons/{lesson}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:update-lesson`

**FormRequest:** `Modules\LearningModule\Http\Requests\Lesson\UpdateLessonRequest`

```json
{
    "unit_id": 0,
    "title": {
        "en": "",
        "ar": ""
    },
    "description": {
        "en": "",
        "ar": ""
    },
    "lesson_order": 0,
    "lesson_type": "",
    "is_required": false,
    "actual_duration_minutes": 0,
    "video": "",
    "attachments": []
}
```

## `GET` `/api/v1/super-admin/lessons`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:list-lessons`

**Body:** none.

## `POST` `/api/v1/super-admin/lessons`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:create-lesson`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `GET` `/api/v1/super-admin/permissions`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:list-permissions`

**Body:** none.

## `DELETE` `/api/v1/super-admin/question-options/{question_option}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:delete-option`

**Body:** usually none.

## `GET` `/api/v1/super-admin/question-options/{question_option}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:show-option`

**Body:** none.

## `PUT` `/api/v1/super-admin/question-options/{question_option}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:update-option`

**FormRequest:** `Modules\AssesmentModule\Http\Requests\QuestionOptionRequest\UpdateQuestionOptionRequest`

```json
{
    "option_text": [],
    "is_correct": false
}
```

## `PATCH` `/api/v1/super-admin/question-options/{question_option}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:update-option`

**FormRequest:** `Modules\AssesmentModule\Http\Requests\QuestionOptionRequest\UpdateQuestionOptionRequest`

```json
{
    "option_text": [],
    "is_correct": false
}
```

## `GET` `/api/v1/super-admin/question-options`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:list-options`

**Body:** none.

## `POST` `/api/v1/super-admin/question-options`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:create-option`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `DELETE` `/api/v1/super-admin/questions/{question}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:delete-question`

**Body:** usually none.

## `GET` `/api/v1/super-admin/questions/{question}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:show-question`

**Body:** none.

## `PUT` `/api/v1/super-admin/questions/{question}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:update-question`

**Body:** `{}` *(rules resolution failed: Attempt to read property "quiz_id" on null)*

```json
{}
```

## `PATCH` `/api/v1/super-admin/questions/{question}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:update-question`

**Body:** `{}` *(rules resolution failed: Attempt to read property "quiz_id" on null)*

```json
{}
```

## `GET` `/api/v1/super-admin/questions`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:list-questions`

**Body:** none.

## `POST` `/api/v1/super-admin/questions`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:create-question`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `POST` `/api/v1/super-admin/quizzes/{quiz}/archive`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `POST` `/api/v1/super-admin/quizzes/{quiz}/publish`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:publish-quiz`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `POST` `/api/v1/super-admin/quizzes/{quiz}/unpublish`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:unpublish-quiz`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `DELETE` `/api/v1/super-admin/quizzes/{quiz}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:delete-quiz`

**Body:** usually none.

## `GET` `/api/v1/super-admin/quizzes/{quiz}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:show-quiz`

**Body:** none.

## `PUT` `/api/v1/super-admin/quizzes/{quiz}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:update-quiz`

**FormRequest:** `Modules\AssesmentModule\Http\Requests\QuizRequest\UpdateQuizRequest`

```json
{
    "instructor_id": "",
    "quizable_id": 0,
    "quizable_type": "",
    "type": "",
    "title": [],
    "description": [],
    "max_score": 0,
    "passing_score": 0,
    "status": "",
    "auto_grade_enabled": false,
    "available_from": "2026-01-01",
    "due_date": "2026-01-01",
    "duration_minutes": 0
}
```

## `PATCH` `/api/v1/super-admin/quizzes/{quiz}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:update-quiz`

**FormRequest:** `Modules\AssesmentModule\Http\Requests\QuizRequest\UpdateQuizRequest`

```json
{
    "instructor_id": "",
    "quizable_id": 0,
    "quizable_type": "",
    "type": "",
    "title": [],
    "description": [],
    "max_score": 0,
    "passing_score": 0,
    "status": "",
    "auto_grade_enabled": false,
    "available_from": "2026-01-01",
    "due_date": "2026-01-01",
    "duration_minutes": 0
}
```

## `GET` `/api/v1/super-admin/quizzes`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:list-quiz`

**Body:** none.

## `POST` `/api/v1/super-admin/quizzes`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:create-quiz`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `GET` `/api/v1/super-admin/reports/courses/content-performance/{courseId}`

**Middleware:** `api`, `auth:api`, `role:super-admin`

**Body:** none.

## `GET` `/api/v1/super-admin/reports/courses/learning-gaps`

**Middleware:** `api`, `auth:api`, `role:super-admin`

**Body:** none.

## `GET` `/api/v1/super-admin/reports/courses/popularity`

**Middleware:** `api`, `auth:api`, `role:super-admin`

**Body:** none.

## `GET` `/api/v1/super-admin/reports/students/completion-rates`

**Middleware:** `api`, `auth:api`, `role:super-admin`

**Body:** none.

## `GET` `/api/v1/super-admin/reports/students/learning-time`

**Middleware:** `api`, `auth:api`, `role:super-admin`

**Body:** none.

## `GET` `/api/v1/super-admin/reports/students/performance`

**Middleware:** `api`, `auth:api`, `role:super-admin`

**Body:** none.

## `DELETE` `/api/v1/super-admin/roles/{role}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:delete-roles`

**Body:** usually none.

## `GET` `/api/v1/super-admin/roles/{role}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:show-roles`

**Body:** none.

## `PUT` `/api/v1/super-admin/roles/{role}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:update-roles`

**FormRequest:** `Modules\UserMangementModule\Http\Requests\Api\V1\Role\UpdateRoleRequest`

```json
{
    "name": "",
    "guard_name": "",
    "permissions": []
}
```

## `PATCH` `/api/v1/super-admin/roles/{role}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:update-roles`

**FormRequest:** `Modules\UserMangementModule\Http\Requests\Api\V1\Role\UpdateRoleRequest`

```json
{
    "name": "",
    "guard_name": "",
    "permissions": []
}
```

## `GET` `/api/v1/super-admin/roles`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:list-roles`

**Body:** none.

## `POST` `/api/v1/super-admin/roles`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:create-roles`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `GET` `/api/v1/super-admin/snapshots/assessment-progress`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** none.

## `GET` `/api/v1/super-admin/snapshots/certificate-funnel`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** none.

## `GET` `/api/v1/super-admin/snapshots/engagement-activity`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** none.

## `POST` `/api/v1/super-admin/snapshots/materialize`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**FormRequest:** `Modules\ReportingModule\Http\Requests\Snapshot\MaterializeSnapshotsRequest`

```json
{
    "snapshot_date": "2026-01-01"
}
```

## `DELETE` `/api/v1/super-admin/students/{student}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:delete-student`

**Body:** usually none.

## `GET` `/api/v1/super-admin/students/{student}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:show-student`

**Body:** none.

## `PUT` `/api/v1/super-admin/students/{student}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:update-student`

**FormRequest:** `Modules\UserMangementModule\Http\Requests\Api\V1\Student\StudentUpdateRequest`

```json
{
    "name": "",
    "email": "",
    "password": "",
    "phone": "",
    "date_of_birth": "2026-01-01",
    "gender": "",
    "address": "",
    "education_level": "",
    "country": "",
    "bio": "",
    "specialization": "",
    "joined_at": "2026-01-01"
}
```

## `GET` `/api/v1/super-admin/students`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:list-students`

**Body:** none.

## `POST` `/api/v1/super-admin/students`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:create-student`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `GET` `/api/v1/super-admin/units/course/{course}/count`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** none.

## `GET` `/api/v1/super-admin/units/course/{course}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** none.

## `GET` `/api/v1/super-admin/units/{unit}/can-delete`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** none.

## `GET` `/api/v1/super-admin/units/{unit}/duration`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** none.

## `PUT` `/api/v1/super-admin/units/{unit}/move`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `PUT` `/api/v1/super-admin/units/{unit}/position`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `DELETE` `/api/v1/super-admin/units/{unit}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:delete-unit`

**Body:** usually none.

## `GET` `/api/v1/super-admin/units/{unit}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:show-unit`

**Body:** none.

## `PUT` `/api/v1/super-admin/units/{unit}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:update-unit`

**FormRequest:** `Modules\LearningModule\Http\Requests\Unit\UpdateUnitRequest`

```json
{
    "course_id": 0,
    "title": {
        "en": "",
        "ar": ""
    },
    "description": {
        "en": "",
        "ar": ""
    },
    "unit_order": 0,
    "actual_duration_minutes": 0
}
```

## `GET` `/api/v1/super-admin/units`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:list-units`

**Body:** none.

## `POST` `/api/v1/super-admin/units`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:create-unit`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `DELETE` `/api/v1/super-admin/users/{user}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:delete-user`

**Body:** usually none.

## `GET` `/api/v1/super-admin/users/{user}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:show-user`

**Body:** none.

## `PUT` `/api/v1/super-admin/users/{user}`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:update-user`

**FormRequest:** `Modules\UserMangementModule\Http\Requests\Api\V1\User\UserUpdateRequest`

```json
{
    "name": "",
    "email": "",
    "password": "",
    "phone": "",
    "date_of_birth": "2026-01-01",
    "gender": "",
    "address": ""
}
```

## `GET` `/api/v1/super-admin/users`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:list-users`

**Body:** none.

## `POST` `/api/v1/super-admin/users`

**Middleware:** `api`, `auth:api`, `role:super-admin,api`, `permission:create-user`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `GET` `/api/v1/units/course/{course}`

**Middleware:** `api`, `auth:api`, `role:student,api`

**Body:** none.

## `GET` `/api/v1/units/{unit}/duration`

**Middleware:** `api`, `auth:api`, `role:student,api`

**Body:** none.

## `GET` `/api/v1/units/{unit}/lessons/count`

**Middleware:** `api`, `auth:api`

**Body:** none.

## `GET` `/api/v1/units/{unit}/lessons`

**Middleware:** `api`, `auth:api`

**Body:** none.

## `POST` `/api/v1/units/{unit}/move`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `DELETE` `/api/v1/units/{unit}`

**Middleware:** `api`, `auth:api`, `permission:delete-unit`

**Body:** usually none.

## `GET` `/api/v1/units/{unit}`

**Middleware:** `api`, `auth:api`, `role:student,api`, `permission:show-unit`

**Body:** none.

## `PUT` `/api/v1/units/{unit}`

**Middleware:** `api`, `auth:api`, `permission:update-unit`

**FormRequest:** `Modules\LearningModule\Http\Requests\Unit\UpdateUnitRequest`

```json
{
    "course_id": 0,
    "title": {
        "en": "",
        "ar": ""
    },
    "description": {
        "en": "",
        "ar": ""
    },
    "unit_order": 0,
    "actual_duration_minutes": 0
}
```

## `PATCH` `/api/v1/units/{unit}`

**Middleware:** `api`, `auth:api`, `permission:update-unit`

**FormRequest:** `Modules\LearningModule\Http\Requests\Unit\UpdateUnitRequest`

```json
{
    "course_id": 0,
    "title": {
        "en": "",
        "ar": ""
    },
    "description": {
        "en": "",
        "ar": ""
    },
    "unit_order": 0,
    "actual_duration_minutes": 0
}
```

## `GET` `/api/v1/units`

**Middleware:** `api`, `auth:api`, `permission:list-units`

**Body:** none.

## `POST` `/api/v1/units`

**Middleware:** `api`, `auth:api`, `permission:create-unit`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `POST` `/api/v1/virtual-sessions/{virtualSession}/attendance`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(rules resolution failed: The user id field is required.)*

```json
{}
```

## `POST` `/api/v1/virtual-sessions/{virtualSession}/cancel`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `POST` `/api/v1/virtual-sessions/{virtualSession}/publish`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `DELETE` `/api/v1/virtual-sessions/{virtual_session}`

**Middleware:** `api`, `auth:api`

**Body:** usually none.

## `GET` `/api/v1/virtual-sessions/{virtual_session}`

**Middleware:** `api`, `auth:api`

**Body:** none.

## `PUT` `/api/v1/virtual-sessions/{virtual_session}`

**Middleware:** `api`, `auth:api`

**FormRequest:** `Modules\CommunicationModule\Http\Requests\VirtualSession\UpdateVirtualSessionRequest`

```json
{
    "title": "",
    "description": "",
    "starts_at": "2026-01-01",
    "ends_at": "2026-01-01",
    "metadata": [],
    "status": "",
    "join_url": ""
}
```

## `PATCH` `/api/v1/virtual-sessions/{virtual_session}`

**Middleware:** `api`, `auth:api`

**FormRequest:** `Modules\CommunicationModule\Http\Requests\VirtualSession\UpdateVirtualSessionRequest`

```json
{
    "title": "",
    "description": "",
    "starts_at": "2026-01-01",
    "ends_at": "2026-01-01",
    "metadata": [],
    "status": "",
    "join_url": ""
}
```

## `GET` `/api/v1/virtual-sessions`

**Middleware:** `api`, `auth:api`

**Body:** none.

## `POST` `/api/v1/virtual-sessions`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(rules resolution failed: The integration id field is required. (and 3 more errors))*

```json
{}
```

## `POST` `/api/v1/webhooks/integrations/{provider}`

**Middleware:** `api`

**Body:** `{}` *(no FormRequest on this action — check controller or use empty body)*

```json
{}
```

## `GET` `/api/v1/{enrollment}/progress`

**Middleware:** `api`, `auth:api`

**Body:** none.

## `PUT` `/api/v1/{enrollment}/status`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```

## `GET` `/api/v1/{enrollment}`

**Middleware:** `api`, `auth:api`

**Body:** none.

## `PUT` `/api/v1/{enrollment}`

**Middleware:** `api`, `auth:api`

**FormRequest:** `Modules\LearningModule\Http\Requests\Enrollment\UpdateEnrollmentRequest`

```json
{
    "enrollment_type": "",
    "progress_percentage": 0
}
```

## `GET` `/api/v1`

**Middleware:** `api`, `auth:api`

**Body:** none.

## `POST` `/api/v1`

**Middleware:** `api`, `auth:api`

**Body:** `{}` *(rules resolution failed: )*

```json
{}
```
