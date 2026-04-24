<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizProgressApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_submit_quiz_attempt_returns_score(): void
    {
        $student = User::factory()->create();
        $instructor = User::factory()->create();

        $course = Course::create([
            'title' => 'Course A',
            'description' => 'Description',
            'instructor_id' => $instructor->id,
            'is_published' => true,
        ]);

        $module = Module::create(['course_id' => $course->id, 'title' => 'M1', 'position' => 1]);
        $lesson = Lesson::create(['module_id' => $module->id, 'title' => 'L1', 'position' => 1]);
        $quiz = Quiz::create(['lesson_id' => $lesson->id, 'title' => 'Quiz 1']);

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Question 1',
            'type' => 'multiple_choice',
            'position' => 1,
        ]);

        $goodAnswer = Answer::create(['question_id' => $question->id, 'answer_text' => 'Oui', 'is_correct' => true]);

        Enrollment::create(['user_id' => $student->id, 'course_id' => $course->id]);

        $attemptStart = $this->actingAs($student)->postJson("/api/quizzes/{$quiz->id}/attempts");

        $attemptStart->assertCreated()->assertJsonPath('attempt.quiz_id', $quiz->id);

        $attemptId = $attemptStart->json('attempt.id');

        $submit = $this->actingAs($student)->putJson("/api/attempts/{$attemptId}", [
            'answers' => [
                ['question_id' => $question->id, 'answer_id' => $goodAnswer->id],
            ],
        ]);

        $submit->assertOk()->assertJsonPath('results.percentage', 100.0);
    }

    public function test_course_progress_returns_completed_lessons_count(): void
    {
        $student = User::factory()->create();
        $instructor = User::factory()->create();

        $course = Course::create([
            'title' => 'Course A',
            'description' => 'Description',
            'instructor_id' => $instructor->id,
            'is_published' => true,
        ]);

        $module = Module::create(['course_id' => $course->id, 'title' => 'M1', 'position' => 1]);
        $lesson = Lesson::create(['module_id' => $module->id, 'title' => 'L1', 'position' => 1]);

        Enrollment::create(['user_id' => $student->id, 'course_id' => $course->id]);

        $this->actingAs($student)->postJson("/api/lessons/{$lesson->id}/progress")
            ->assertOk();

        $this->actingAs($student)->getJson("/api/courses/{$course->id}/progress")
            ->assertOk()
            ->assertJsonPath('completed_lessons', 1)
            ->assertJsonPath('total_lessons', 1);
    }
}
