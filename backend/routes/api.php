<?php

use App\Http\Controllers\Course\CourseController;
use App\Http\Controllers\Course\EnrollmentController;
use App\Http\Controllers\Course\LessonController;
use App\Http\Controllers\Course\ModuleController;
use App\Http\Controllers\Quiz\AnswersController;
use App\Http\Controllers\Quiz\AttemptController;
use App\Http\Controllers\Quiz\QuizController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Course routes
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('courses', CourseController::class);

    // Additional course routes
    Route::post('courses/{course}/enroll', [CourseController::class, 'enroll']);
    Route::delete('courses/{course}/enroll', [CourseController::class, 'unenroll']);
    Route::get('courses/my/enrolled', [CourseController::class, 'myCourses']);
    Route::get('courses/my/created', [CourseController::class, 'myCreatedCourses']);
    Route::patch('courses/{course}/publish', [CourseController::class, 'publish']);
    Route::patch('courses/{course}/unpublish', [CourseController::class, 'unpublish']);
    Route::get('courses/{course}/statistics', [CourseController::class, 'statistics']);
});

// Lesson routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('modules/{module}/lessons', [LessonController::class, 'index']);
    Route::post('modules/{module}/lessons', [LessonController::class, 'store']);
    Route::get('lessons/{lesson}', [LessonController::class, 'show']);
    Route::put('lessons/{lesson}', [LessonController::class, 'update']);
    Route::delete('lessons/{lesson}', [LessonController::class, 'destroy']);

    // Progress routes
    Route::post('lessons/{lesson}/complete', [LessonController::class, 'markCompleted']);
    Route::delete('lessons/{lesson}/complete', [LessonController::class, 'markIncomplete']);
    Route::get('lessons/{lesson}/progress', [LessonController::class, 'getProgress']);

    // Navigation routes
    Route::get('lessons/{lesson}/next', [LessonController::class, 'nextLesson']);
    Route::get('lessons/{lesson}/previous', [LessonController::class, 'previousLesson']);

    // Reorder lessons
    Route::patch('modules/{module}/lessons/reorder', [LessonController::class, 'reorder']);
});

// Enrollment routes
Route::middleware('auth:sanctum')->group(function () {
    // Course enrollments (instructor view)
    Route::get('courses/{course}/enrollments', [EnrollmentController::class, 'index']);
    Route::get('courses/{course}/enrollments/statistics', [EnrollmentController::class, 'statistics']);
    Route::get('courses/{course}/enrollments/export', [EnrollmentController::class, 'export']);
    Route::post('courses/{course}/enrollments/bulk', [EnrollmentController::class, 'bulkEnroll']);
    Route::delete('courses/{course}/enrollments/bulk', [EnrollmentController::class, 'bulkUnenroll']);

    // Individual enrollments
    Route::get('enrollments/{enrollment}', [EnrollmentController::class, 'show']);
    Route::delete('enrollments/{enrollment}', [EnrollmentController::class, 'destroy']);

    // User enrollment actions
    Route::post('courses/{course}/enroll', [EnrollmentController::class, 'store']);
    Route::get('courses/{course}/enrollment/check', [EnrollmentController::class, 'checkEnrollment']);
    Route::get('enrollments/my', [EnrollmentController::class, 'myEnrollments']);
});

// Module routes
Route::middleware('auth:sanctum')->group(function () {
    // Course modules
    Route::get('courses/{course}/modules', [ModuleController::class, 'index']);
    Route::post('courses/{course}/modules', [ModuleController::class, 'store']);
    Route::patch('courses/{course}/modules/reorder', [ModuleController::class, 'reorder']);

    // Individual modules
    Route::get('modules/{module}', [ModuleController::class, 'show']);
    Route::put('modules/{module}', [ModuleController::class, 'update']);
    Route::delete('modules/{module}', [ModuleController::class, 'destroy']);

    // Module statistics and progress
    Route::get('modules/{module}/statistics', [ModuleController::class, 'statistics']);
    Route::get('modules/{module}/progress', [ModuleController::class, 'progress']);

    // Navigation routes
    Route::get('modules/{module}/next', [ModuleController::class, 'nextModule']);
    Route::get('modules/{module}/previous', [ModuleController::class, 'previousModule']);
});

// Attempt routes
Route::middleware('auth:sanctum')->group(function () {
    // Quiz attempts (instructor view)
    Route::get('quizzes/{quiz}/attempts', [AttemptController::class, 'index']);
    Route::get('quizzes/{quiz}/attempts/statistics', [AttemptController::class, 'statistics']);

    // Individual attempts
    Route::get('attempts/{attempt}', [AttemptController::class, 'show']);
    Route::put('attempts/{attempt}', [AttemptController::class, 'update']);
    Route::delete('attempts/{attempt}', [AttemptController::class, 'destroy']);
    Route::get('attempts/{attempt}/results', [AttemptController::class, 'results']);

    // User attempt actions
    Route::post('quizzes/{quiz}/attempts', [AttemptController::class, 'store']);
    Route::get('quizzes/{quiz}/my-attempts', [AttemptController::class, 'userAttempts']);
    Route::get('attempts/my', [AttemptController::class, 'myAttempts']);
});

// Quiz routes
Route::middleware('auth:sanctum')->group(function () {
    // Quiz management
    Route::get('quizzes', [QuizController::class, 'index']);
    Route::post('quizzes', [QuizController::class, 'store']);
    Route::get('quizzes/{quiz}', [QuizController::class, 'show']);
    Route::put('quizzes/{quiz}', [QuizController::class, 'update']);
    Route::delete('quizzes/{quiz}', [QuizController::class, 'destroy']);

    // Quiz publishing
    Route::post('quizzes/{quiz}/publish', [QuizController::class, 'publish']);
    Route::post('quizzes/{quiz}/unpublish', [QuizController::class, 'unpublish']);

    // Quiz questions and statistics
    Route::get('quizzes/{quiz}/questions', [QuizController::class, 'getQuestions']);
    Route::get('quizzes/{quiz}/statistics', [QuizController::class, 'statistics']);
    Route::post('quizzes/{quiz}/reorder-questions', [QuizController::class, 'reorderQuestions']);

    // Quiz attempts
    Route::post('quizzes/{quiz}/start-attempt', [QuizController::class, 'startAttempt']);
    Route::post('quizzes/{quiz}/submit', [QuizController::class, 'submitAnswers']);
    Route::get('quizzes/{quiz}/results/{attempt}', [QuizController::class, 'getResults']);
    Route::get('quizzes/{quiz}/my-attempts', [QuizController::class, 'myAttempts']);
    Route::get('quizzes/{quiz}/attempts', [QuizController::class, 'getAllAttempts']);

    // User quiz access
    Route::get('my-quizzes', [QuizController::class, 'myQuizzes']);
    Route::get('enrolled-quizzes', [QuizController::class, 'enrolledQuizzes']);

    // Quiz duplication
    Route::post('quizzes/{quiz}/duplicate', [QuizController::class, 'duplicate']);
});

// Answer routes
Route::middleware('auth:sanctum')->group(function () {
    // Question answers
    Route::get('questions/{question}/answers', [AnswersController::class, 'index']);
    Route::post('questions/{question}/answers', [AnswersController::class, 'store']);
    Route::post('questions/{question}/answers/bulk', [AnswersController::class, 'bulkStore']);
    Route::patch('questions/{question}/answers/reorder', [AnswersController::class, 'reorder']);

    // Individual answers
    Route::get('answers/{answer}', [AnswersController::class, 'show']);
    Route::put('answers/{answer}', [AnswersController::class, 'update']);
    Route::delete('answers/{answer}', [AnswersController::class, 'destroy']);

    // Answer management
    Route::patch('answers/{answer}/correct', [AnswersController::class, 'setCorrect']);
    Route::delete('answers/{answer}/correct', [AnswersController::class, 'unsetCorrect']);
    Route::get('questions/{question}/correct-answer', [AnswersController::class, 'correctAnswer']);
});
