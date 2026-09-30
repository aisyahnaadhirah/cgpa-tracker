<?php

namespace Tests\Feature;

use App\Events\CourseCreated;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CourseApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_duplicate_course_code_in_same_semester_returns_json_422(): void
    {
        // create dummy user testing
        $user = User::factory()->create();

        $semester = $user->semesters()->create([
            'name' => 'Semester 1',
            'academic_year' => '2026/2027',
            'semester_number' => 1,
        ]);

        $semester->courses()->create([
            'code' => 'CSF33201',
            'name' => 'Existing Course',
            'credit_hours' => 3,
            'grade_point' => 4,
        ]);

        $response = $this->actingAs($user, 'web')->postJson('/api/courses', [
            'semester_id' => $semester->id,
            'code' => 'CSF33201',
            'name' => 'Duplicate Course',
            'credit_hours' => 3,
            'grade_point' => 4,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('code');
        $this->assertDatabaseCount('courses', 1);
    }

    public function test_patch_updates_only_the_submitted_field(): void
    {
        $user = User::factory()->create();

        $semester = $user->semesters()->create([
            'name' => 'Semester 1',
            'academic_year' => '2026/2027',
            'semester_number' => 1,
        ]);

        $course = $semester->courses()->create([
            'code' => 'CSF10102',
            'name' => 'Math',
            'credit_hours' => 3,
            'grade_point' => 4,
        ]);

        $response = $this->actingAs($user, 'web')->patchJson(
            '/api/courses/'.$course->id,
            ['grade_point' => 3.33]
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.grade_point', 3.33);

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'code' => 'CSF10102',
            'name' => 'Math',
            'credit_hours' => 3,
            'grade_point' => 3.33,
        ]);
    }

    public function test_user_cannot_update_another_users_course(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $otherSemester = $otherUser->semesters()->create([
            'name' => 'Semester 1',
            'academic_year' => '2026/2027',
            'semester_number' => 1,
        ]);

        $course = $otherSemester->courses()->create([
            'code' => 'CSF10102',
            'name' => 'Math',
            'credit_hours' => 3,
            'grade_point' => 4,
        ]);

        $response = $this->actingAs($user, 'web')->patchJson(
            '/api/courses/'.$course->id,
            ['grade_point' => 3.33]
        );

        $response->assertNotFound();

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'grade_point' => 4,
        ]);
    }

    public function test_user_can_delete_their_own_course(): void
    {
        $user = User::factory()->create();

        $semester = $user->semesters()->create([
            'name' => 'Semester 1',
            'academic_year' => '2026/2027',
            'semester_number' => 1,
        ]);

        $course = $semester->courses()->create([
            'code' => 'CSF10102',
            'name' => 'Math',
            'credit_hours' => 3,
            'grade_point' => 4,
        ]);

        $response = $this->actingAs($user, 'web')
            ->deleteJson('/api/courses/'.$course->id);

        $response->assertNoContent();

        $this->assertDatabaseMissing('courses', [
            'id' => $course->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_course(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $otherSemester = $otherUser->semesters()->create([
            'name' => 'Semester 1',
            'academic_year' => '2026/2027',
            'semester_number' => 1,
        ]);

        $course = $otherSemester->courses()->create([
            'code' => 'CSF10102',
            'name' => 'Math',
            'credit_hours' => 3,
            'grade_point' => 4,
        ]);

        $response = $this->actingAs($user, 'web')
            ->deleteJson('/api/courses/'.$course->id);

        $response->assertNotFound();

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'grade_point' => 4,
        ]);
    }

    public function test_user_can_create_a_course(): void
    {
        $user = User::factory()->create();

        $semester = $user->semesters()->create([
            'name' => 'Semester 1',
            'academic_year' => '2026/2027',
            'semester_number' => 1,
        ]);

        Event::fake([CourseCreated::class]);

        $response = $this->actingAs($user, 'web')->postJson('/api/courses', [
            'semester_id' => $semester->id,
            'code' => 'CSF10102',
            'name' => 'Math',
            'credit_hours' => 3,
            'grade_point' => 4,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.code', 'CSF10102');

        $this->assertDatabaseHas('courses', [
            'semester_id' => $semester->id,
            'code' => 'CSF10102',
            'name' => 'Math',
        ]);

        Event::assertDispatched(CourseCreated::class);
    }

    // untuk check list course yang keluar hanya untuk user yang login
    public function test_course_list_contains_only_the_authenticated_users_courses(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $semester = $user->semesters()->create([
            'name' => 'Semester 1',
            'academic_year' => '2026/2027',
            'semester_number' => 1,
        ]);

        $otherSemester = $otherUser->semesters()->create([
            'name' => 'Semester 1',
            'academic_year' => '2026/2027',
            'semester_number' => 1,
        ]);

        $semester->courses()->create([
            'code' => 'CSF10102',
            'name' => 'My Course',
            'credit_hours' => 3,
            'grade_point' => 4,
        ]);

        $otherSemester->courses()->create([
            'code' => 'CSD33002',
            'name' => 'Other Course',
            'credit_hours' => 3,
            'grade_point' => 3,
        ]);

        $response = $this->actingAs($user, 'web')->getJson('/api/courses');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'CSF10102');
    }

    // test response yang keluar tu ikut JSON Resource
    public function test_user_can_view_their_course_with_resource_fields_only(): void
    {
        $user = User::factory()->create();

        $semester = $user->semesters()->create([
            'name' => 'Semester 1',
            'academic_year' => '2026/2027',
            'semester_number' => 1,
        ]);

        $course = $semester->courses()->create([
            'code' => 'CSF10102',
            'name' => 'Math',
            'credit_hours' => 3,
            'grade_point' => 4,
        ]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/courses/'.$course->id);

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $course->id)
            ->assertJsonPath('data.semester_id', $semester->id)
            ->assertJsonPath('data.code', 'CSF10102')
            ->assertJsonMissingPath('data.semester');
    }

    // test kalau user update, dia takleh tukar code tu jadi code yang sama dengan yang sedia ada
    public function test_patch_rejects_a_course_code_already_used_in_the_semester(): void
    {
        $user = User::factory()->create();

        $semester = $user->semesters()->create([
            'name' => 'Semester 1',
            'academic_year' => '2026/2027',
            'semester_number' => 1,
        ]);

        $course = $semester->courses()->create([
            'code' => 'CSF10102',
            'name' => 'Math',
            'credit_hours' => 3,
            'grade_point' => 4,
        ]);

        $semester->courses()->create([
            'code' => 'CSD33002',
            'name' => 'Programming',
            'credit_hours' => 3,
            'grade_point' => 3,
        ]);

        $response = $this->actingAs($user, 'web')->patchJson(
            '/api/courses/'.$course->id,
            ['code' => 'CSD33002']
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('code');

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'code' => 'CSF10102',
        ]);
    }
}
