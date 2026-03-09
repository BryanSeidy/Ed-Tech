<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrollment_returns_conflict_when_user_is_already_enrolled(): void
    {
        $user = User::factory()->create();
        $instructor = User::factory()->create();
        $course = Course::create([
            'title' => 'Course A',
            'description' => 'Description',
            'instructor_id' => $instructor->id,
            'is_published' => true,
        ]);

        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        $response = $this->actingAs($user)->postJson("/api/courses/{$course->id}/enroll");

        $response->assertStatus(409)->assertJson([
            'message' => "L'utilisateur est déjà inscrit à ce cours.",
        ]);
    }
}
