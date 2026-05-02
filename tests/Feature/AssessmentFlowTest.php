<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\AssesmentModule\Models\Attempt;
use Modules\AssesmentModule\Models\Quiz;
use Modules\AssesmentModule\Enums\AttemptStatus;
use Modules\AssesmentModule\Enums\AssesmentType;
use Modules\AssesmentModule\Enums\QuizStatus;
use Modules\AssesmentModule\Enums\QuizType;
use Modules\LearningModule\Models\Course;
use Modules\LearningModule\Models\CourseCategory;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AssessmentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_quiz_attempt_answer_submit_flow_is_processed_end_to_end(): void
    {
        $student = $this->createApiUserWithPermissions([
            'create-quiz',
            'create-question',
            'create-option',
            'create-attempt',
            'submit-attempt',
            'show-attempt',
        ]);

        $quizResponse = $this->actingAs($student, 'api')->postJson('/api/v1/quizzes', [
            'instructor_id' => $student->id,
            'quizable_id' => 1,
            'quizable_type' => QuizType::COURSE->value,
            'type' => AssesmentType::Quiz->value,
            'title' => ['en' => 'Flow Quiz'],
            'description' => ['en' => 'Quiz for flow test'],
            'max_score' => 10,
            'passing_score' => 6,
            'status' => QuizStatus::DRAFT->value,
            'auto_grade_enabled' => true,
            'duration_minutes' => 15,
        ])->assertCreated();

        $quizId = $quizResponse->json('data.id');

        $questionResponse = $this->actingAs($student, 'api')->postJson('/api/v1/questions', [
            'quiz_id' => $quizId,
            'type' => QuestionType::MULTIPLE_CHOICE->value,
            'question_text' => ['en' => '2 + 2 = ?'],
            'point' => 5,
            'order_index' => 1,
            'is_required' => true,
        ])->assertCreated();

        $questionId = $questionResponse->json('data.id');

        $optionResponse = $this->actingAs($student, 'api')->postJson('/api/v1/question-options', [
            'question_id' => $questionId,
            'option_text' => ['en' => '4'],
            'is_correct' => true,
        ])->assertCreated();

        $optionId = $optionResponse->json('data.id');

        $attemptResponse = $this->actingAs($student, 'api')->postJson('/api/v1/attempts', [
            'quiz_id' => $quizId,
            'student_id' => $student->id,
        ])->assertCreated();

        $attemptId = $attemptResponse->json('data.id');

        $this->actingAs($student, 'api')
            ->postJson("/api/v1/attempts/{$attemptId}/start", [])
            ->assertCreated();

        $submitResponse = $this->actingAs($student, 'api')->postJson("/api/v1/attempts/{$attemptId}/submit", [
            'answers' => [
                [
                    'question_id' => $questionId,
                    'selected_option_id' => $optionId,
                ],
            ],
        ])->assertOk();

        $this->assertSame(AttemptStatus::GRADED->value, $submitResponse->json('data.data.status'));
        $this->assertTrue($submitResponse->json('data.data.is_passed'));
    }

    public function test_submitted_attempt_can_be_graded_for_manual_answers(): void
    {
        $instructor = $this->createApiUserWithPermissions([
            'create-quiz',
            'create-question',
            'create-attempt',
            'submit-attempt',
            'grade-attempt',
            'show-attempt',
        ]);

        $quizId = $this->actingAs($instructor, 'api')->postJson('/api/v1/quizzes', [
            'instructor_id' => $instructor->id,
            'quizable_id' => 2,
            'quizable_type' => QuizType::COURSE->value,
            'type' => AssesmentType::Quiz->value,
            'title' => ['en' => 'Manual Quiz'],
            'description' => ['en' => 'Manual grading quiz'],
            'max_score' => 10,
            'passing_score' => 6,
            'status' => QuizStatus::DRAFT->value,
            'auto_grade_enabled' => false,
            'duration_minutes' => 20,
        ])->assertCreated()->json('data.id');

        $questionId = $this->actingAs($instructor, 'api')->postJson('/api/v1/questions', [
            'quiz_id' => $quizId,
            'type' => QuestionType::SHORT_ANSWER->value,
            'question_text' => ['en' => 'Explain OOP principles'],
            'point' => 10,
            'order_index' => 1,
            'is_required' => true,
        ])->assertCreated()->json('data.id');

        $attemptId = $this->actingAs($instructor, 'api')->postJson('/api/v1/attempts', [
            'quiz_id' => $quizId,
            'student_id' => $instructor->id,
        ])->assertCreated()->json('data.id');

        $this->actingAs($instructor, 'api')
            ->postJson("/api/v1/attempts/{$attemptId}/start", [])
            ->assertCreated();

        $submitResponse = $this->actingAs($instructor, 'api')->postJson("/api/v1/attempts/{$attemptId}/submit", [
            'answers' => [
                [
                    'question_id' => $questionId,
                    'answer_text' => ['en' => 'Encapsulation and inheritance'],
                ],
            ],
        ])->assertOk();

        $this->assertSame(AttemptStatus::SUBMITTED->value, $submitResponse->json('data.data.status'));

        $gradeResponse = $this->actingAs($instructor, 'api')->postJson("/api/v1/attempts/{$attemptId}/grade", [
            'answers' => [
                [
                    'question_id' => $questionId,
                    'earned_score' => 8,
                    'is_correct' => true,
                ],
            ],
        ])->assertOk();

        $this->assertSame(AttemptStatus::GRADED->value, $gradeResponse->json('data.data.status'));
    }

    private function createApiUserWithPermissions(array $permissions): User
    {
        $user = User::factory()->create();

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'api',
            ]);
        }

        $role = Role::firstOrCreate([
            'name' => 'assessment-tester-'.md5(implode(',', $permissions)),
            'guard_name' => 'api',
        ]);

        $role->syncPermissions($permissions);
        $user->assignRole($role);

        return $user;
    }

    public function test_student_cannot_create_fourth_attempt_after_three_failed_attempts(): void
    {
        $student = User::factory()->create();
        $quiz = $this->createCourseQuiz($student);

        for ($index = 1; $index <= 3; $index++) {
            Attempt::query()->create([
                'quiz_id' => $quiz->id,
                'student_id' => $student->id,
                'attempt_number' => $index,
                'status' => AttemptStatus::GRADED->value,
                'score' => 20,
                'is_passed' => false,
            ]);
        }

        $service = app(\Modules\AssesmentModule\Services\V1\AttemptService::class);
        $response = $service->store([
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
        ]);

        $this->assertFalse($response['success']);
        $this->assertSame(422, $response['code']);
    }

    public function test_student_cannot_create_new_attempt_after_passing_quiz(): void
    {
        $student = User::factory()->create();
        $quiz = $this->createCourseQuiz($student);

        Attempt::query()->create([
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
            'attempt_number' => 1,
            'status' => AttemptStatus::GRADED->value,
            'score' => 85,
            'is_passed' => true,
        ]);

        $service = app(\Modules\AssesmentModule\Services\V1\AttemptService::class);
        $response = $service->store([
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
        ]);

        $this->assertFalse($response['success']);
        $this->assertSame('You already passed this quiz.', $response['message']);
        $this->assertSame(422, $response['code']);
    }

    public function test_course_progress_endpoint_issues_certificate_only_when_weighted_score_reaches_sixty(): void
    {
        $student = User::factory()->create();
        $course = $this->createCourse($student);

        $quizOne = Quiz::query()->create([
            'instructor_id' => $student->id,
            'quizable_type' => QuizType::COURSE->value,
            'quizable_id' => $course->course_id,
            'type' => AssesmentType::Quiz->value,
            'status' => QuizStatus::PUBLISHED->value,
            'title' => ['en' => 'Quiz 1'],
            'description' => ['en' => 'Quiz 1'],
            'max_score' => 100,
            'passing_score' => 50,
            'auto_grade_enabled' => true,
            'duration_minutes' => 30,
        ]);

        $quizTwo = Quiz::query()->create([
            'instructor_id' => $student->id,
            'quizable_type' => QuizType::COURSE->value,
            'quizable_id' => $course->course_id,
            'type' => AssesmentType::Quiz->value,
            'status' => QuizStatus::PUBLISHED->value,
            'title' => ['en' => 'Quiz 2'],
            'description' => ['en' => 'Quiz 2'],
            'max_score' => 100,
            'passing_score' => 50,
            'auto_grade_enabled' => true,
            'duration_minutes' => 30,
        ]);

        Attempt::query()->create([
            'quiz_id' => $quizOne->id,
            'student_id' => $student->id,
            'attempt_number' => 1,
            'status' => AttemptStatus::GRADED->value,
            'score' => 59,
            'is_passed' => true,
        ]);

        Attempt::query()->create([
            'quiz_id' => $quizTwo->id,
            'student_id' => $student->id,
            'attempt_number' => 1,
            'status' => AttemptStatus::GRADED->value,
            'score' => 60,
            'is_passed' => true,
        ]);

        $response = $this->actingAs($student, 'api')
            ->getJson("/api/v1/courses/{$course->course_id}/assessment-progress");

        $response->assertOk();
        $response->assertJsonPath('data.progress.weighted_percentage', 59.5);
        $response->assertJsonPath('data.certificate.eligible', false);
        $response->assertJsonPath('data.certificate.issued', false);

        Attempt::query()
            ->where('quiz_id', $quizOne->id)
            ->where('student_id', $student->id)
            ->update(['score' => 70, 'is_passed' => true]);

        $secondResponse = $this->actingAs($student, 'api')
            ->getJson("/api/v1/courses/{$course->course_id}/assessment-progress");

        $secondResponse->assertOk();
        $secondResponse->assertJsonPath('data.progress.weighted_percentage', 65.0);
        $secondResponse->assertJsonPath('data.certificate.eligible', true);
        $secondResponse->assertJsonPath('data.certificate.issued', true);
    }

    private function createCourseQuiz(User $instructor): Quiz
    {
        $course = $this->createCourse($instructor);

        return Quiz::query()->create([
            'instructor_id' => $instructor->id,
            'quizable_type' => QuizType::COURSE->value,
            'quizable_id' => $course->course_id,
            'type' => AssesmentType::Quiz->value,
            'status' => QuizStatus::PUBLISHED->value,
            'title' => ['en' => 'Attempts Quiz'],
            'description' => ['en' => 'Attempts Quiz'],
            'max_score' => 100,
            'passing_score' => 60,
            'auto_grade_enabled' => true,
            'duration_minutes' => 20,
        ]);
    }

    private function createCourse(User $creator): Course
    {
        $category = CourseCategory::query()->create([
            'name' => ['en' => 'General'],
            'slug' => 'general-'.$creator->id,
            'description' => ['en' => 'General'],
            'is_active' => true,
        ]);

        return Course::query()->create([
            'created_by' => $creator->id,
            'course_category_id' => $category->course_category_id,
            'title' => ['en' => 'Course '.$creator->id],
            'slug' => 'course-'.$creator->id,
            'description' => ['en' => 'Description'],
            'objectives' => ['en' => 'Objectives'],
            'prerequisites' => ['en' => 'None'],
            'actual_duration_hours' => 2,
            'language' => 'en',
            'status' => 'published',
            'min_score_to_pass' => 60.00,
            'is_offline_available' => false,
            'course_delivery_type' => 'self_paced',
            'difficulty_level' => 'beginner',
            'average_rating' => 0,
            'total_ratings' => 0,
        ]);
    }
}
