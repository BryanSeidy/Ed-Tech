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

    public function test_list_modules_endpoint_returns_paginated_payload(): void
    {
        $instructor = User::factory()->create();
        $course = Course::create([
            'title' => 'Course A',
            'description' => 'Description',
            'instructor_id' => $instructor->id,
            'is_published' => true,
        ]);

        Module::create(['course_id' => $course->id, 'title' => 'M1', 'position' => 1]);
        Module::create(['course_id' => $course->id, 'title' => 'M2', 'position' => 2]);

        $response = $this->getJson("/api/courses/{$course->id}/modules?per_page=1&sort=position&direction=asc");

        $response->assertOk()->assertJsonStructure([
            'data',
            'meta' => ['current_page', 'per_page', 'total', 'last_page'],
        ]);
    }

    public function test_list_lessons_endpoint_returns_paginated_payload(): void
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

        $response = $this->getJson("/api/modules/{$module->id}/lessons?per_page=5");

        $response->assertOk()->assertJsonStructure([
            'data',
            'meta' => ['current_page', 'per_page', 'total', 'last_page'],
        ]);
    }
}
