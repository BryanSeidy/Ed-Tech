<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseStructureApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_courses_returns_published_courses(): void
    {
        $instructor = User::factory()->create();
        Course::create([
            'title' => 'Course A',
            'description' => 'Description A',
            'instructor_id' => $instructor->id,
            'is_published' => true,
        ]);

        $response = $this->getJson('/api/courses?published=1');

        $response->assertOk()->assertJsonPath('data.0.title', 'Course A');
    }

    public function test_course_detail_includes_modules_and_lessons(): void
    {
        $instructor = User::factory()->create();
        $course = Course::create([
            'title' => 'Course A',
            'description' => 'Description',
            'instructor_id' => $instructor->id,
            'is_published' => true,
        ]);

        $module = Module::create(['course_id' => $course->id, 'title' => 'M1', 'position' => 1]);
        Lesson::create(['module_id' => $module->id, 'title' => 'L1', 'position' => 1]);

        $response = $this->getJson("/api/courses/{$course->id}");

        $response->assertOk()
            ->assertJsonPath('title', 'Course A')
            ->assertJsonPath('modules.0.title', 'M1')
            ->assertJsonPath('modules.0.lessons.0.title', 'L1');
    }
}
