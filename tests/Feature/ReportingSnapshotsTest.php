<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\AssesmentModule\Enums\AssesmentType;
use Modules\AssesmentModule\Enums\AttemptStatus;
use Modules\AssesmentModule\Enums\QuizStatus;
use Modules\AssesmentModule\Enums\QuizType;
use Modules\AssesmentModule\Models\Attempt;
use Modules\AssesmentModule\Models\Quiz;
use Modules\LearningModule\Models\Course;
use Modules\LearningModule\Models\CourseCategory;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportingSnapshotsTest extends TestCase
{
    use RefreshDatabase;

    public function test_materialize_snapshot_endpoint_requires_super_admin_role(): void
    {
        $student = $this->createUser();

        $this->actingAs($student, 'api')
            ->postJson('/api/v1/super-admin/snapshots/materialize', [
                'snapshot_date' => '2026-04-28',
            ])
            ->assertForbidden();
    }

    public function test_snapshot_materialization_command_builds_assessment_progress_rows(): void
    {
        $instructor = $this->createUser();
        $student = $this->createUser();
        $course = $this->createCourse($instructor);

        $quiz = Quiz::query()->create([
            'instructor_id' => $instructor->id,
            'quizable_type' => QuizType::COURSE->value,
            'quizable_id' => $course->course_id,
            'type' => AssesmentType::Quiz->value,
            'status' => QuizStatus::PUBLISHED->value,
            'title' => ['en' => 'Snapshot Quiz'],
            'description' => ['en' => 'Snapshot Quiz'],
            'max_score' => 100,
            'passing_score' => 60,
            'auto_grade_enabled' => true,
            'duration_minutes' => 20,
        ]);

        Attempt::query()->create([
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
            'attempt_number' => 1,
            'status' => AttemptStatus::GRADED->value,
            'score' => 70,
            'is_passed' => true,
        ]);

        $this->artisan('reporting:snapshots:materialize', ['--date' => '2026-04-28'])
            ->assertSuccessful();

        $this->assertDatabaseHas('assessment_progress_snapshots', [
            'course_id' => $course->course_id,
            'student_id' => $student->id,
            'weighted_percentage' => 70.00,
        ]);
    }

    public function test_super_admin_can_materialize_snapshots_via_api(): void
    {
        $superAdmin = $this->createUser();
        $this->assignApiRole($superAdmin, 'super-admin');

        $this->actingAs($superAdmin, 'api')
            ->postJson('/api/v1/super-admin/snapshots/materialize', [
                'snapshot_date' => '2026-04-28',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.snapshot_date', '2026-04-28');
    }

    private function assignApiRole(User $user, string $roleName): void
    {
        $role = Role::firstOrCreate([
            'name' => $roleName,
            'guard_name' => 'api',
        ]);

        $user->assignRole($role);
    }

    private function createCourse(User $creator): Course
    {
        $category = CourseCategory::query()->create([
            'name' => ['en' => 'General'],
            'slug' => 'general-reporting-'.$creator->id,
            'description' => ['en' => 'General'],
            'is_active' => true,
        ]);

        return Course::query()->create([
            'created_by' => $creator->id,
            'course_category_id' => $category->course_category_id,
            'title' => ['en' => 'Reporting Course '.$creator->id],
            'slug' => 'reporting-course-'.$creator->id,
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

    private function createUser(): User
    {
        $email = 'reporting-'.Str::lower(Str::random(8)).'@example.com';

        DB::table('users')->insert([
            'name' => 'Reporting Tester',
            'email' => $email,
            'password' => bcrypt('password'),
            'phone' => '0000000000',
            'date_of_birth' => '2000-01-01',
            'gender' => 'male',
            'address' => 'Test Address',
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::query()->where('email', $email)->firstOrFail();
    }
}
