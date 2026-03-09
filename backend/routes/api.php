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

// Certificate routes
Route::middleware('auth:sanctum')->group(function () {
    // Certificate management (admin/instructor)
    Route::get('certificates', [App\Http\Controllers\CertificateController::class, 'index']);
    Route::post('certificates', [App\Http\Controllers\CertificateController::class, 'store']);
    Route::get('certificates/{certificate}', [App\Http\Controllers\CertificateController::class, 'show']);
    Route::put('certificates/{certificate}', [App\Http\Controllers\CertificateController::class, 'update']);
    Route::delete('certificates/{certificate}', [App\Http\Controllers\CertificateController::class, 'destroy']);

    // Certificate generation and verification
    Route::post('certificates/generate', [App\Http\Controllers\CertificateController::class, 'generate']);
    Route::get('certificates/{certificate}/download', [App\Http\Controllers\CertificateController::class, 'download']);
    Route::post('certificates/verify', [App\Http\Controllers\CertificateController::class, 'verify']);

    // User certificate access
    Route::get('my-certificates', [App\Http\Controllers\CertificateController::class, 'myCertificates']);
    Route::get('courses/{course}/certificate/check-eligibility', [App\Http\Controllers\CertificateController::class, 'checkEligibility']);
});

// Progress routes
Route::middleware('auth:sanctum')->group(function () {
    // Progress management
    Route::get('progress', [App\Http\Controllers\ProgressController::class, 'index']);
    Route::post('progress', [App\Http\Controllers\ProgressController::class, 'store']);
    Route::get('progress/{progress}', [App\Http\Controllers\ProgressController::class, 'show']);
    Route::put('progress/{progress}', [App\Http\Controllers\ProgressController::class, 'update']);
    Route::delete('progress/{progress}', [App\Http\Controllers\ProgressController::class, 'destroy']);

    // Progress actions
    Route::post('progress/mark-completed', [App\Http\Controllers\ProgressController::class, 'markCompleted']);
    Route::post('progress/mark-incomplete', [App\Http\Controllers\ProgressController::class, 'markIncomplete']);
    Route::post('progress/bulk-update', [App\Http\Controllers\ProgressController::class, 'bulkUpdate']);

    // Progress statistics and reports
    Route::get('progress/user/{user}', [App\Http\Controllers\ProgressController::class, 'getUserProgress']);
    Route::get('courses/{course}/progress', [App\Http\Controllers\ProgressController::class, 'getCourseProgress']);
    Route::post('courses/{course}/progress/reset', [App\Http\Controllers\ProgressController::class, 'resetCourseProgress']);

    // User progress access
    Route::get('my-progress', [App\Http\Controllers\ProgressController::class, 'myProgress']);
});

// User routes
Route::middleware('auth:sanctum')->group(function () {
    // User management (admin)
    Route::get('users', [App\Http\Controllers\User\UserController::class, 'index']);
    Route::post('users', [App\Http\Controllers\User\UserController::class, 'store']);
    Route::get('users/{user}', [App\Http\Controllers\User\UserController::class, 'show']);
    Route::put('users/{user}', [App\Http\Controllers\User\UserController::class, 'update']);
    Route::delete('users/{user}', [App\Http\Controllers\User\UserController::class, 'destroy']);

    // User profile management
    Route::get('profile', [App\Http\Controllers\User\UserController::class, 'profile']);
    Route::put('profile', [App\Http\Controllers\User\UserController::class, 'updateProfile']);
    Route::post('profile/change-password', [App\Http\Controllers\User\UserController::class, 'changePassword']);

    // User data access
    Route::get('users/{user}/courses', [App\Http\Controllers\User\UserController::class, 'getUserCourses']);
    Route::get('users/{user}/enrollments', [App\Http\Controllers\User\UserController::class, 'getUserEnrollments']);
    Route::get('users/{user}/progress', [App\Http\Controllers\User\UserController::class, 'getUserProgress']);
    Route::get('users/{user}/certificates', [App\Http\Controllers\User\UserController::class, 'getUserCertificates']);
    Route::get('users/{user}/statistics', [App\Http\Controllers\User\UserController::class, 'getUserStatistics']);

    // Authenticated user access
    Route::get('dashboard', [App\Http\Controllers\User\UserController::class, 'dashboard']);
    Route::get('my-courses', [App\Http\Controllers\User\UserController::class, 'myCourses']);
    Route::get('my-enrollments', [App\Http\Controllers\User\UserController::class, 'myEnrollments']);
    Route::get('my-certificates', [App\Http\Controllers\User\UserController::class, 'myCertificates']);
    Route::get('my-statistics', [App\Http\Controllers\User\UserController::class, 'myStatistics']);
});
